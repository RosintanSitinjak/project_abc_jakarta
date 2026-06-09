<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactInquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<string, string|null> $data
     */
    public function __construct(private array $data)
    {
    }

    public function build(): self
    {
        $subject = sprintf('Contact Inquiry: %s', $this->data['subject'] ?? '');

        $mail = $this
            ->subject(trim($subject))
            ->view('emails.contact-inquiry')
            ->with(['data' => $this->data]);

        if (!empty($this->data['email'])) {
            $mail->replyTo($this->data['email'], $this->data['name'] ?? null);
        }

        return $mail;
    }
}
