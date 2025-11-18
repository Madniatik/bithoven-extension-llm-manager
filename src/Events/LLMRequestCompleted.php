<?php

namespace Bithoven\LLMManager\Events;

use Bithoven\LLMManager\Models\LLMUsageLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LLMRequestCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public LLMUsageLog $usageLog,
        public array $result
    ) {
    }
}
