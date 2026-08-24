<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckLowStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $product;
    public $totalQuantity;

    public function __construct(Product $product, $totalQuantity)
    {
        $this->product = $product;
        $this->totalQuantity = $totalQuantity;
    }

    public function handle(): void
    {
        if ($this->totalQuantity < $this->product->low_stock_threshold) {
            // Mock notification (could be email/SMS)
            Log::warning("Low Stock Alert: Product [{$this->product->sku}] has fallen below threshold. Current total quantity: {$this->totalQuantity}. Threshold: {$this->product->low_stock_threshold}");
        }
    }
}
