<?php

namespace App\Events;

use App\Models\Status;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StatusCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $status;

    public function __construct(Status $status)
    {
        $this->status = $status;
    }
}
