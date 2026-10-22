<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $customer;

    /**
     * Inisialisasi data user dan customer
     */
    public function __construct(User $user, Customer $customer)
    {
        $this->user = $user;
        $this->customer = $customer;
    }

    /**
     * Subjek Email Resmi
     */
    public function envelope(): Envelope
    {
        $subject = $this->customer->type === 'penginjil'
            ? 'Pendaftaran Akun Penginjil Literatur (PL) - ABC Jakarta'
            : 'Selamat Datang di Adventist Book Center (ABC) Jakarta';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * View Template HTML
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome_registration',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}