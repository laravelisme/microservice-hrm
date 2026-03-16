<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HariLiburUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $hariLiburId,
        public bool $isUmum,
        public array $companyIds = [],
    ) {}
}
