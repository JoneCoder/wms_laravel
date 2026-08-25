<?php

namespace App\Notifications;

use App\Notifications\Channels\SlackWebhookChannel;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Product $product,
        public int $totalQuantity
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', SlackWebhookChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject("Low Stock Alert: {$this->product->name}")
                    ->greeting("Hello,")
                    ->line("The stock level for **{$this->product->name}** (SKU: {$this->product->sku}) is running low.")
                    ->line("Current quantity: **{$this->totalQuantity}**")
                    ->line("Threshold level: **{$this->product->low_stock_threshold}**")
                    ->action('View Inventory', url('/')) // Usually front-end URL
                    ->line('Please restock the item to avoid stockouts.');
    }

    /**
     * Get the custom slack representation of the notification.
     */
    public function toSlackWebhook(object $notifiable): array
    {
        return [
            'text' => "🚨 *Low Stock Alert*\nProduct: *{$this->product->name}* (SKU: {$this->product->sku})\nCurrent Quantity: *{$this->totalQuantity}*\nThreshold: *{$this->product->low_stock_threshold}*",
        ];
    }
}
