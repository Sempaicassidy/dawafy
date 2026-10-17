@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-file-earmark-medical me-2 text-info"></i> Prescription Record #{{ $prescription->id }}</h2>
        <p class="text-white-50 mb-0">Patient: {{ $prescription->patient_name }} | Prescriber: Dr. {{ $prescription->doctor_name }}</p>
    </div>
    <div>
        <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="bi bi-arrow-left me-1"></i> Back to Prescriptions
        </a>
    </div>
</div>

<div class="row justify-content-center animate-fade-in">
    <div class="col-md-8">
        <div class="card-laravel mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-clipboard2-pulse me-2"></i> Clinical & Dispensing Information</h6>
                <span class="badge bg-primary bg-opacity-10 text-primary">{{ date('d M Y, H:i', strtotime($prescription->created_at)) }}</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Patient Name</label>
                        <div class="p-2 bg-light rounded fw-bold">{{ $prescription->patient_name }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Doctor / Prescriber</label>
                        <div class="p-2 bg-light rounded fw-bold">Dr. {{ $prescription->doctor_name }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Hospital / Clinic</label>
                        <div class="p-2 bg-light rounded">{{ $prescription->clinic_name ?? 'Walk-in / Clinic' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Dispensed Drug</label>
                        <div class="p-2 bg-light rounded fw-bold text-primary">
                            {{ $prescription->transactionItem?->batch?->drug?->name ?? 'Dispensed Medicine' }}
                            (Qty: {{ $prescription->transactionItem?->quantity ?? 1 }})
                        </div>
                    </div>
                </div>

                @if($prescription->notes)
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">Pharmacist / Clinical Notes</label>
                        <div class="p-3 bg-light rounded border">
                            {{ $prescription->notes }}
                        </div>
                    </div>
                @endif

                @if($prescription->image_path)
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Prescription Image / Document</label>
                        <div class="text-center p-3 border rounded bg-light">
                            <img src="{{ asset($prescription->image_path) }}" alt="Prescription Document" class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
