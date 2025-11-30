<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $target;
    protected $message;

    /**
     * Jumlah percobaan ulang jika gagal (misal bot mati sebentar)
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct($target, $message)
    {
        $this->target = $target;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Tembak ke Service Node.js (Baileys)
            $response = Http::timeout(10)->post('http://localhost:3000/send-message', [
                'number' => $this->target,
                'message' => $this->message,
            ]);

            if ($response->failed()) {
                // Jika gagal, lempar error agar masuk ke failed_jobs atau dicoba lagi
                throw new \Exception("Gagal kirim ke Node.js: " . $response->body());
            }

            Log::info("WA Terkirim ke: " . $this->target);

        } catch (\Exception $e) {
            Log::error("Queue WA Error: " . $e->getMessage());
            // Melempar error agar Laravel tahu job ini gagal dan perlu di-retry
            throw $e;
        }
    }
}
