<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ServiceRequest;
use App\Models\TaxReturn;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    /**
     * Show tracking search page and search results with phone verification.
     */
    public function index(Request $request)
    {
        $query    = strtoupper(trim((string) $request->input('q', $request->input('query', ''))));
        $verify   = preg_replace('/\D/', '', (string) $request->input('verify', ''));
        $searched = $query !== '';
        $type     = null;
        $result   = null;

        if ($searched) {
            $sr = ServiceRequest::where('tracking_number', $query)->first();

            if ($sr) {
                $phone = preg_replace('/\D/', '', (string) $sr->applicant_phone);
                if (strlen($verify) === 4 && hash_equals(substr($phone, -4), $verify)) {
                    $type = 'service_request';
                    $result = $sr;
                }
            }
            // Na milne par aur verification ghalat hone par ek hi message (enumeration se bachne ke liye)
        }

        $myRequests = collect();
        if (auth()->check()) {
            $user = auth()->user();
            $myRequests = ServiceRequest::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('applicant_email', $user->email);
            })->latest()->get();
        }

        return view('track', compact('query', 'searched', 'type', 'result', 'myRequests', 'verify'));
    }

    /**
     * Staff-only AJAX search endpoint. Public requests return empty array.
     */
    public function search(Request $request)
    {
        if (! (auth()->check() && auth()->user()->isCompany())) {
            return response()->json(['results' => []]);
        }

        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $cleanNumbers = preg_replace('/[^0-9]/', '', $q);
        $isStaff = true;

        // 1. Search Service Requests
        $serviceRequests = ServiceRequest::query()
            ->where(function ($query) use ($q, $cleanNumbers) {
                $query->where('tracking_number', 'like', "%{$q}%")
                      ->orWhere('applicant_name', 'like', "%{$q}%")
                      ->orWhere('applicant_email', 'like', "%{$q}%")
                      ->orWhere('service_name', 'like', "%{$q}%");

                if (!empty($cleanNumbers)) {
                    $query->orWhere('applicant_phone', 'like', "%{$cleanNumbers}%")
                          ->orWhere('data_payload', 'like', "%{$cleanNumbers}%");
                }
            })
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($item) use ($isStaff) {
                return [
                    'id' => $item->id,
                    'type' => 'service_request',
                    'tracking_number' => $item->tracking_number,
                    'service_name' => $item->service_name,
                    'applicant_name' => $isStaff ? $item->applicant_name : $this->maskName($item->applicant_name),
                    'applicant_email' => $isStaff ? $item->applicant_email : $this->maskEmail($item->applicant_email),
                    'applicant_phone' => $isStaff ? $item->applicant_phone : $this->maskPhone($item->applicant_phone),
                    'fee_amount' => (float) $item->fee_amount,
                    'status' => $item->status,
                    'status_label' => ucfirst(str_replace('_', ' ', $item->status)),
                    'payment_status' => $item->payment_status,
                    'created_at' => $item->created_at->format('d M Y'),
                    'admin_notes' => $isStaff ? $item->admin_notes : null,
                    'delivered_reference_no' => $item->delivered_reference_no,
                    'has_delivered_file' => !empty($item->delivered_file_path),
                    'download_url' => (auth()->check() && !empty($item->delivered_file_path)) ? route('services.download', $item->id) : null,
                ];
            });

        // 2. Search Tax Returns
        $taxReturns = TaxReturn::query()
            ->with(['client', 'taxYear'])
            ->whereHas('client', function ($cq) use ($q, $cleanNumbers) {
                $cq->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%");
                if (!empty($cleanNumbers)) {
                    $cq->orWhere('phone', 'like', "%{$cleanNumbers}%")
                       ->orWhere('cnic', 'like', "%{$cleanNumbers}%");
                }
            })
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($item) use ($isStaff) {
                return [
                    'id' => $item->id,
                    'type' => 'tax_return',
                    'tracking_number' => 'TR-' . ($item->taxYear?->name ?? '2026') . '-' . $item->id,
                    'service_name' => 'Income Tax Return (' . ($item->taxYear?->name ?? '2026-27') . ')',
                    'applicant_name' => $isStaff ? ($item->client?->name ?? 'Client') : $this->maskName($item->client?->name ?? 'Client'),
                    'applicant_email' => $isStaff ? ($item->client?->email ?? '') : $this->maskEmail($item->client?->email ?? ''),
                    'applicant_phone' => $isStaff ? ($item->client?->phone ?? '') : $this->maskPhone($item->client?->phone ?? ''),
                    'fee_amount' => (float) $item->fee_amount,
                    'status' => $item->status,
                    'status_label' => ucfirst(str_replace('_', ' ', $item->status)),
                    'payment_status' => 'paid',
                    'created_at' => $item->created_at->format('d M Y'),
                    'iris_acknowledgement_no' => $isStaff ? $item->iris_acknowledgement_no : null,
                ];
            });

        $results = $serviceRequests->concat($taxReturns)->values();

        return response()->json(['results' => $results]);
    }

    private function maskName(?string $name): string
    {
        if (empty($name)) return '';
        $parts = explode(' ', trim($name));
        $maskedParts = array_map(function ($part) {
            $len = strlen($part);
            if ($len <= 2) return $part[0] . '*';
            return substr($part, 0, 2) . str_repeat('*', min(4, $len - 2)) . substr($part, -1);
        }, $parts);
        return implode(' ', $maskedParts);
    }

    private function maskEmail(?string $email): string
    {
        if (empty($email)) return '';
        $parts = explode('@', $email);
        if (count($parts) !== 2) return '***@***';
        $user = $parts[0];
        $domain = $parts[1];
        $maskedUser = strlen($user) <= 2 ? $user[0] . '*' : substr($user, 0, 2) . '***' . substr($user, -1);
        return $maskedUser . '@' . $domain;
    }

    private function maskPhone(?string $phone): string
    {
        if (empty($phone)) return '';
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean) <= 4) return '****';
        return substr($clean, 0, 4) . '*****' . substr($clean, -2);
    }
}
