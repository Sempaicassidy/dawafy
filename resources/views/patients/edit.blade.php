@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-person-gear me-2 text-info"></i> Edit Patient Record</h2>
        <p class="text-white-50 mb-0">Update patient personal information and contact details.</p>
    </div>
    <div>
        <a href="{{ route('patients.index') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="bi bi-arrow-left me-1"></i> Back to Patients
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card-laravel animate-fade-in">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 text-primary"><i class="bi bi-card-text me-2"></i> Edit Information for {{ $patient->name }}</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('patients.update', $patient->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-pill px-3" value="{{ old('name', $patient->name) }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Phone Number</label>
                            <input type="text" name="phone" class="form-control rounded-pill px-3" value="{{ old('phone', $patient->phone) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-muted">Age</label>
                            <input type="number" name="age" class="form-control rounded-pill px-3" value="{{ old('age', $patient->age) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-muted">Gender</label>
                            <select name="gender" class="form-select rounded-pill px-3">
                                <option value="Male" {{ $patient->gender === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ $patient->gender === 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ $patient->gender === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('patients.index') }}" class="btn btn-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn-laravel-primary rounded-pill px-4">
                            <i class="bi bi-check-circle me-1"></i> Update Patient
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
