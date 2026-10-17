<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\TransactionItem;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index()
    {
        $prescriptions = [];
        $dda = [];
        $tenant_id = auth()->user()?->tenant_id ?? session('tenant_id') ?? session('masquerade_tenant_id') ?? 1;

        try {
            $prescQuery = Prescription::with(['transactionItem.transaction.user', 'transactionItem.batch.drug']);
            if ($tenant_id) {
                $prescQuery->whereHas('transactionItem.transaction', function ($q) use ($tenant_id) {
                    $q->where('tenant_id', $tenant_id);
                });
            }

            $rawPrescriptions = $prescQuery->orderBy('created_at', 'desc')->get();

            $prescriptions = $rawPrescriptions->map(function ($p) {
                return [
                    'id' => $p->id,
                    'drug_name' => $p->transactionItem?->batch?->drug?->name ?? 'Medicine',
                    'patient_name' => $p->patient_name,
                    'doctor_name' => $p->doctor_name,
                    'hospital_name' => $p->clinic_name ?? 'Clinic',
                    'quantity' => $p->transactionItem?->quantity ?? 1,
                    'created_at' => $p->created_at,
                ];
            })->toArray();

            $ddaQuery = \App\Models\DDARegister::with(['transactionItem.batch.drug', 'approvedBy']);
            if ($tenant_id) {
                $ddaQuery->whereHas('transactionItem.transaction', function ($q) use ($tenant_id) {
                    $q->where('tenant_id', $tenant_id);
                });
            }

            $dda = $ddaQuery->orderBy('created_at', 'desc')->get()->map(function ($d) {
                return [
                    'id' => $d->id,
                    'drug_name' => $d->transactionItem?->batch?->drug?->name ?? 'Controlled Substance',
                    'patient_name' => $d->patient_name,
                    'id_type' => $d->id_type ?? 'NIDA',
                    'id_number' => $d->id_number ?? '-',
                    'doctor_name' => $d->doctor_name ?? 'Physician',
                    'approved_by' => $d->approvedBy?->name ?? $d->approvedBy?->username ?? 'Pharmacist',
                    'created_at' => $d->created_at,
                ];
            })->toArray();

        } catch (\Exception $e) {
            $prescriptions = [];
            $dda = [];
        }

        return view('prescriptions', compact('prescriptions', 'dda'));
    }


    public function create()
    {
        return view('prescriptions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_item_id' => 'required|exists:transaction_items,id',
            'patient_name' => 'required|string',
            'doctor_name' => 'required|string',
            'clinic_name' => 'nullable|string',
            'image_path' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        Prescription::create($validated);

        return redirect()->route('prescriptions.index')->with('success', 'Prescription recorded successfully');
    }

    public function show(Prescription $prescription)
    {
        $prescription->load(['transactionItem.transaction', 'transactionItem.batch.drug']);
        return view('prescriptions.show', compact('prescription'));
    }
}
