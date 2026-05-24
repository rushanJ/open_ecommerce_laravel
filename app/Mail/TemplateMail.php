<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Queue-ready via Queueable; implement ShouldQueue when running a queue worker. */
class TemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $toEmail,
        public string $mailSubject,
        public string $bodyHtml,
        public ?string $bodyText = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
            to: [
                new Address($this->toEmail),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.template',
            with: [
                'bodyHtml' => $this->bodyHtml,
                'bodyText' => $this->bodyText,
            ],
        );
    }
}
