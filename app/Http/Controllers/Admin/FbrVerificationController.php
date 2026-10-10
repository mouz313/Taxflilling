<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FbrVerificationService;
use Illuminate\Http\Request;

class FbrVerificationController extends Controller
{
    protected FbrVerificationService $fbrService;

    public function __construct(FbrVerificationService $fbrService)
    {
        $this->fbrService = $fbrService;
    }

    /**
     * Dedicated FBR IRIS 2.0 Live Verification Desk
     */
    public function index()
    {
        return view('admin.fbr.index');
    }

    /**
     * Live AJAX Verification Dispatcher
     */
    public function verify(Request $request)
    {
        $type = $request->input('type');
        $query = trim($request->input('query', ''));

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a CNIC, NTN, or CPR number.',
            ], 422);
        }

        $result = match ($type) {
            'atl' => $this->fbrService->verifyAtl($query),
            'ntn' => $this->fbrService->verifyNtn($query),
            'cpr' => $this->fbrService->verifyCpr($query),
            default => ['success' => false, 'message' => 'Invalid verification type.']
        };

        return response()->json($result);
    }
}
