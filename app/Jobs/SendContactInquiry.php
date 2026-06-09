<?php

namespace App\Jobs;

use App\Mail\ContactInquiryMail;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInputForCreate;
use HubSpot\Factory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendContactInquiry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param array<string, string|null> $data
     */
    public function __construct(private array $data) {}

    public function handle(): void
    {
        Mail::to(env('CONTACT_MAILTO', 'jhonny@lamsolusi.com'))->send(new ContactInquiryMail($this->data));

        $this->syncToHubspot();
    }

    private function syncToHubspot(): void
    {
        $token = env('HUBSPOT_ACCESS_TOKEN');

        if (empty($token)) {
            return;
        }

        try {
            $hubspot = Factory::createWithAccessToken($token);

            $contactInput = new SimplePublicObjectInputForCreate();
            
            // Map the form data to HubSpot properties
            // The key must be the 'Internal name' of the property in HubSpot
            $properties = [
                'email' => $this->data['email'] ?? '',
                'firstname' => $this->data['name'] ?? '',
            ];

            if (!empty($this->data['company'])) {
                $properties['company'] = $this->data['company'];
            }

            if (!empty($this->data['phone'])) {
                $properties['phone'] = $this->data['phone'];
            }
            
            if (!empty($this->data['message']) || !empty($this->data['subject'])) {
                $subject = $this->data['subject'] ?? 'Tanpa Subjek';
                $message = $this->data['message'] ?? '';
                // Menggabungkan subject ke dalam message karena HubSpot Contact tidak punya default properti 'subject'
                $properties['message'] = "[Subjek: {$subject}]\n\n{$message}";
            }

            $contactInput->setProperties($properties);

            // 1. Buat Contact (atau perbarui jika sudah ada berdasarkan email)
            $contactResponse = $hubspot->crm()->contacts()->basicApi()->create($contactInput);

            // 2. Buat Ticket
            try {
                $ticketInput = new \HubSpot\Client\Crm\Tickets\Model\SimplePublicObjectInputForCreate();
                $ticketInput->setProperties([
                    'hs_pipeline' => '0',       // Default Support/Service Pipeline
                    'hs_pipeline_stage' => '1', // Default Stage "New"
                    'subject' => $this->data['subject'] ?? 'Inquiry Baru dari Website',
                    'content' => sprintf(
                        "Dikirim oleh: %s\nEmail: %s\nTelepon: %s\nPerusahaan: %s\n\nDetail Pesan:\n%s",
                        $this->data['name'] ?? '-',
                        $this->data['email'] ?? '-',
                        $this->data['phone'] ?? '-',
                        $this->data['company'] ?? '-',
                        $this->data['message'] ?? '-'
                    ),
                ]);
                $hubspot->crm()->tickets()->basicApi()->create($ticketInput);
            } catch (Throwable $ticketError) {
                // Hanya di-log jika pembuatan ticket gagal (misalnya karena pipeline custom) agar tidak membatalkan proses utama
                Log::warning('HubSpot Ticket Creation failed: ' . $ticketError->getMessage());
            }

        } catch (Throwable $e) {
            Log::error('Failed to sync contact to HubSpot: ' . $e->getMessage());
        }
    }
}
