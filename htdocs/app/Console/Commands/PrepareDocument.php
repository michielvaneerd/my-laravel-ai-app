<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Enums\Lab;

#[Signature('app:prepare-document {--path=} {--title=}')]
#[Description('Stores, chunks and embeds a document')]
class PrepareDocument extends Command
{

    // https://arxiv.org/pdf/2501.17887
    private const MAX_CHUNKS_FOR_EMBEDDING = 5;

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $title = $this->option('title');
        if (empty($title)) {
            $this->error('Missing title');
            return Command::FAILURE;
        }
        $path = $this->option('path');
        if (empty($path)) {
            $this->error('Missing path');
            return Command::FAILURE;
        }
        if (!str_starts_with($path, 'http') && !is_readable($path)) {
            $this->error("Path $path is not readable.");
            return Command::FAILURE;
        }
        $existingDocument = Document::where('path', $path)->first();
        if (!empty($existingDocument)) {
            $this->error("Document with path $path already exists.");
            return Command::FAILURE;
        }

        // Step 1: Get the chunks
        $response = Http::post(config('myapp.docling_serve_url') . '/v1/chunk/hybrid/source', [
            'sources' => [
                [
                    'base64_string' => base64_encode(file_get_contents($path)),
                    'filename' => basename($path),
                    'kind' => 'file'
                ]
            ],
            // 'convert_options' => [
            //     'page_range' => [1, 3],
            //     // 'do_pdf_heading_hierarchy' => true,
            //     // 'pdf_heading_hierarchy_options' => [
            //     //     'enabled' => true
            //     // ]
            // ]
        ]);
        $chunks = $response->json('chunks');

        // Step 2: Get the embeddings of the chunks
        $chunksToEmbed = [];
        $chunksWithEmbeddings = [];
        foreach ($chunks as $chunk) {
            if (count($chunksToEmbed) === self::MAX_CHUNKS_FOR_EMBEDDING) {
                // Get the embeddings
                $response = Embeddings::for(array_map(fn($ch) => $ch['text'], $chunksToEmbed))->generate(Lab::Ollama, 'embeddinggemma:latest');
                foreach ($response->embeddings as $embedding) {
                    $tmp = array_shift($chunksToEmbed);
                    $tmp['embedding'] = $embedding;
                    $chunksWithEmbeddings[] = $tmp;
                }
                if (!empty($chunksToEmbed)) {
                    $this->error('Array $chunksToEmbed is not empty!');
                    return Command::FAILURE;
                }
            }
            $chunksToEmbed[] = $chunk;
        }
        if (!empty($chunksToEmbed)) {
            $response = Embeddings::for(array_map(fn($ch) => $ch['text'], $chunksToEmbed))->generate(Lab::Ollama, 'embeddinggemma:latest');
            foreach ($response->embeddings as $embedding) {
                $chunkToEnrich = array_shift($chunksToEmbed);
                $chunkToEnrich['embedding'] = $embedding;
                $chunksWithEmbeddings[] = $chunkToEnrich;
            }
        }

        DB::transaction(function () use ($chunksWithEmbeddings, $title, $path) {
            $document = Document::create([
                'title' => $title,
                'path' => $path
            ]);
            foreach ($chunksWithEmbeddings as $chunk) {
                $document->chunks()->create([
                    'content' => $chunk['text'],
                    'embedding' => $chunk['embedding'],
                    'token_count' => $chunk['num_tokens']
                ]);
            }
        });
        return Command::SUCCESS;
    }
}
