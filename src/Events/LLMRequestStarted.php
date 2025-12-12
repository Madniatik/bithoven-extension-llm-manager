<?php

namespace Bithoven\LLMManager\Events;

use Bithoven\LLMManager\Models\LLMProviderConfiguration;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LLMRequestStarted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public LLMProviderConfiguration $configuration,
        public string $prompt,
        public array $parameters
    ) {
    }
}
