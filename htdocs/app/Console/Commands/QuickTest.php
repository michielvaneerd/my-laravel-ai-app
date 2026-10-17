<?php

namespace App\Console\Commands;

use App\Ai\Agents\RagAssistant;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Laravel\Ai\Enums\Lab;

#[Signature('app:quick-test {prompt}')]
#[Description('Do a quick test')]
class QuickTest extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $prompt = $this->argument('prompt');
        $response = (new RagAssistant())->prompt($prompt, provider: Lab::Ollama, model: 'gemma4:12b-mlx');
        $this->line((string)$response);
    }
}
