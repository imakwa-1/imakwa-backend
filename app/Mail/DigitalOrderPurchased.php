<?php

namespace App\Mail;

use App\Models\DigitalProductOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DigitalOrderPurchased extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $downloadUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(DigitalProductOrder $order)
    {
        $this->order = $order->load('tier.product');
        $this->downloadUrl = config('app.frontend_url') . '/download/' . $order->download_token;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Imakwa World Cup Download is Ready! 🎉',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.digital-order-purchased',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
