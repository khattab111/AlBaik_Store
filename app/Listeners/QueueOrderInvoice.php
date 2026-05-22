<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\GenerateOrderInvoice;

class QueueOrderInvoice
{
    public function handle(OrderPlaced $event): void
    {
        GenerateOrderInvoice::dispatch($event->order->id);
    }
}
