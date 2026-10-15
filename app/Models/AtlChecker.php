<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtlChecker extends Model
{
    // Do not save to database as per user instruction: "data save nahi krna is ka"
    protected $guarded = [];

    /**
     * Honest ATL status verification (Fail-safe, no fake parity logic or unverified scraping).
     */
    public static function checkOnline(string $cnic): array
    {
        $clean = preg_replace('/\D/', '', $cnic);

        if (strlen($clean) !== 13) {
            return [
                'success' => false,
                'message' => 'Barah-e-karam durust 13-digit CNIC number darj karein (e.g. 42101-1234567-1).',
            ];
        }

        // Is waqt FBR ki ATL se live check nahi hota. Jhoota status kabhi nahi dikhana.
        return [
            'success'       => true,
            'cnic'          => substr($clean, 0, 5) . '-*******-' . substr($clean, -1),
            'verified'      => false,
            'is_active'     => null,
            'status'        => 'UNVERIFIED',
            'source'        => 'Not checked against FBR',
            'checked_at'    => null,
            'message'       => 'Hum is waqt FBR ki Active Taxpayer List se live check nahi karte. Apna status FBR ki official ATL list par check karein, ya hamari team se tasdeeq karwayein.',
            'action_needed' => 'Tasdeeq ke liye humse rabta karein.',
        ];
    }
}
