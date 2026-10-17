<?php

namespace App\Console\Commands;

use App\Ai\Agents\RagAssistant;
use App\Models\Chunk;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Enums\Lab;

#[Signature('app:rag-chat {--type=} {prompt}')]
#[Description('Chat on prepared documents. Type must be `programmed` or `tool`.')]
class RagChat extends Command
{
    private const TYPES = ['programmed', 'tool'];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        if (empty($type) || !in_array($type, self::TYPES)) {
            $this->error('Missing or invalid type.');
            return Command::FAILURE;
        }
        $prompt = $this->argument('prompt');

        // 1) Create embedding from prompt
        $embeddingResponse = Embeddings::for([$prompt])->generate(Lab::Ollama, 'embeddinggemma:latest');

        //die(json_encode(($embeddingResponse->embeddings[0])));

        // 2) Get some chunks
        $chunks = Chunk::query()->whereVectorSimilarTo('embedding', $embeddingResponse->embeddings[0], minSimilarity: 0.1)->limit(4)->pluck('content')->join("\n----------\n");

        $fullPrompt = "<CONTEXT>$chunks</CONTEXT>\n$prompt";

        $response = (new RagAssistant())->prompt($fullPrompt, provider: Lab::Ollama, model: 'gemma4:12b-mlx');
        $this->line((string)$response);
        return Command::SUCCESS;
    }
}
