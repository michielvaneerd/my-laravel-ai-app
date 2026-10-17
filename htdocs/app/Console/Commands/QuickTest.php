<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Enums\Lab;

#[Signature('app:quick-test')]
#[Description('Do a quick test')]
class QuickTest extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Embeddings::for(['Dit is een test!', 'En dit ook!'])->generate(Lab::Ollama, 'embeddinggemma:latest');
        dd($response->embeddings);
    }
}
