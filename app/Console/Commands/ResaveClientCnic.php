<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\QueryException;

class ResaveClientCnic extends Command
{
    protected $signature = 'clients:resave-cnic {--dry-run}';
    protected $description = 'Plaintext CNIC ko encrypt karta hai aur cnic_hash set karta hai';

    public function handle(): int
    {
        $updated = 0;
        $failed = 0;

        Client::withoutGlobalScopes()->chunkById(100, function ($clients) use (&$updated, &$failed) {
            foreach ($clients as $client) {
                $raw = $client->getRawOriginal('cnic');
                if (empty($raw)) {
                    continue;
                }

                try {
                    decrypt($raw);
                    $isEncrypted = true;
                } catch (DecryptException $e) {
                    $isEncrypted = false;
                }

                if ($isEncrypted && ! empty($client->cnic_hash)) {
                    continue;
                }

                if ($this->option('dry-run')) {
                    $updated++;
                    continue;
                }

                try {
                    $plain = $client->cnic;      // getter: decrypted ya plaintext
                    $client->cnic = $plain;      // setter: encrypt + cnic_hash
                    $client->cnic_hash = Client::generateCnicHash($plain);
                    $client->saveQuietly();
                    $updated++;
                } catch (QueryException $e) {
                    // (consultancy_id, cnic_hash) unique: duplicate client mila
                    $failed++;
                    $this->warn("Client #{$client->id}: duplicate ya DB error");
                }
            }
        });

        $this->info("Updated: {$updated}, Failed: {$failed}" . ($this->option('dry-run') ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }
}
