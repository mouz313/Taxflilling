<?php

namespace App\Http\Controllers;

use App\Models\AtlChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AtlCheckerController extends Controller
{
    /**
     * Check FBR Active Taxpayer Status (ATL) via AtlChecker Model.
     * Note: Data is NEVER persisted to database as per user privacy requirement.
     */
    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cnic' => ['required', 'string', 'min:13', 'max:20'],
        ]);

        $result = AtlChecker::checkOnline($validated['cnic']);

        return response()->json($result);
    }
}
