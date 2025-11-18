<?php

namespace Bithoven\LLMManager\Events;

use Bithoven\LLMManager\Models\LLMConfiguration;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LLMRequestStarted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public LLMConfiguration $configuration,
        public string $prompt,
        public array $parameters
    ) {
    }
}
