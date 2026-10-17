@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-file-earmark-medical me-2 text-info"></i> Record Prescription</h2>
        <p class="text-white-50 mb-0">Record doctor and patient prescription information for pharmacy dispensation.</p>
    </div>
    <div>
        <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="bi bi-arrow-left me-1"></i> Back to Prescriptions
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card-laravel animate-fade-in">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 text-primary"><i class="bi bi-clipboard-pulse me-2"></i> Prescription Form</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('prescriptions.store') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Patient Name <span class="text-danger">*</span></label>
                            <input type="text" name="patient_name" class="form-control rounded-pill px-3" placeholder="e.g. Mary Jane" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Prescribing Doctor <span class="text-danger">*</span></label>
                            <input type="text" name="doctor_name" class="form-control rounded-pill px-3" placeholder="e.g. Dr. Robert Smith" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Hospital / Clinic Name</label>
                        <input type="text" name="clinic_name" class="form-control rounded-pill px-3" placeholder="e.g. City General Hospital">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Prescription Notes / Dosage Instructions</label>
                        <textarea name="notes" class="form-control rounded px-3" rows="3" placeholder="Dosage, frequency, duration..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('prescriptions.index') }}" class="btn btn-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn-laravel-primary rounded-pill px-4">
                            <i class="bi bi-check-circle me-1"></i> Save Prescription
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
