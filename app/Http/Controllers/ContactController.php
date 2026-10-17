<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Patient;
use App\Models\User;
use App\Models\InsuranceProvider;
use App\Models\InsuranceClaim;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        // 1. Suppliers / Wholesalers
        $suppliers = Supplier::where('tenant_id', $tenant_id)
            ->orWhereNull('tenant_id')
            ->orderBy('name')
            ->get();

        // 2. Customers / Patients (including chronic disease & debt)
        $patientsQuery = Patient::where('tenant_id', $tenant_id)
            ->orWhereNull('tenant_id');

        $filter = $request->query('filter');
        if ($filter === 'chronic') {
            $patientsQuery->whereNotNull('chronic_condition')->where('chronic_condition', '!=', '');
        } elseif ($filter === 'debt') {
            $patientsQuery->where('outstanding_debt', '>', 0);
        }

        $patients = $patientsQuery->orderBy('name')->get();

        // 3. Staff Contacts
        $staff = User::with('role')
            ->where('tenant_id', $tenant_id)
            ->orderBy('full_name')
            ->get()
            ->map(function ($u) {
                $isOnline = !empty($u->session_id) && $u->last_activity && $u->last_activity > now()->subMinutes(30);
                return [
                    'id' => $u->id,
                    'username' => $u->username,
                    'full_name' => $u->full_name,
                    'phone' => $u->phone,
                    'role' => ($u->role?->name === 'Admin') ? 'Manager' : ($u->role?->name ?? 'Staff'),
                    'is_online' => $isOnline,
                    'last_activity' => $u->last_activity,
                ];
            });

        // 4. Insurance Providers
        $insuranceProviders = InsuranceProvider::where('tenant_id', $tenant_id)
            ->orWhereNull('tenant_id')
            ->get()
            ->map(function ($prov) {
                $claims = InsuranceClaim::where('insurance_provider_id', $prov->id)->get();
                $pendingClaims = $claims->where('status', 'Pending');
                return [
                    'id' => $prov->id,
                    'name' => $prov->name,
                    'contact_person' => $prov->contact_person,
                    'phone' => $prov->phone,
                    'email' => $prov->email,
                    'address' => $prov->address,
                    'total_claims' => $claims->count(),
                    'pending_claims_count' => $pendingClaims->count(),
                    'pending_amount' => $pendingClaims->sum('amount'),
                ];
            });

        // Metrics for directory header
        $stats = [
            'total_suppliers' => $suppliers->count(),
            'total_patients' => Patient::where('tenant_id', $tenant_id)->orWhereNull('tenant_id')->count(),
            'chronic_patients_count' => Patient::where(function($q) use ($tenant_id) {
                $q->where('tenant_id', $tenant_id)->orWhereNull('tenant_id');
            })->whereNotNull('chronic_condition')->where('chronic_condition', '!=', '')->count(),
            'debtor_patients_count' => Patient::where(function($q) use ($tenant_id) {
                $q->where('tenant_id', $tenant_id)->orWhereNull('tenant_id');
            })->where('outstanding_debt', '>', 0)->count(),
            'total_staff' => $staff->count(),
            'total_insurance' => $insuranceProviders->count(),
        ];

        return view('contacts.index', compact(
            'suppliers',
            'patients',
            'staff',
            'insuranceProviders',
            'stats',
            'filter'
        ));
    }
}
