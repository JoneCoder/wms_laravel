<?php

namespace App\Jobs;

use App\Models\Product;
use App\Notifications\LowStockNotification;
use App\Notifications\Channels\SlackWebhookChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

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
            Log::warning("Low Stock Alert: Product [{$this->product->sku}] has fallen below threshold. Current total quantity: {$this->totalQuantity}. Threshold: {$this->product->low_stock_threshold}");

            $adminEmail = config('mail.from.address') ?? 'admin@example.com';
            $slackWebhook = config('services.slack.webhook_url');

            $notificationRoute = Notification::route('mail', $adminEmail);
            
            if ($slackWebhook) {
                $notificationRoute->route(SlackWebhookChannel::class, $slackWebhook);
            }

            $notificationRoute->notify(new LowStockNotification($this->product, $this->totalQuantity));
        }
    }
}
