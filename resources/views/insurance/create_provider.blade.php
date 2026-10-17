@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-shield-plus me-2 text-info"></i> Add Insurance Provider</h2>
        <p class="text-white-50 mb-0">Register an insurance company or health fund scheme.</p>
    </div>
    <div>
        <a href="{{ route('insurance.index') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="bi bi-arrow-left me-1"></i> Back to Insurance
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card-laravel animate-fade-in">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 text-primary"><i class="bi bi-building me-2"></i> Provider Details</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('insurance.store_provider') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Company / Scheme Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-pill px-3" placeholder="e.g. NHIF Tanzania, Jubilee Insurance" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control rounded-pill px-3" placeholder="e.g. Claims Officer">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Phone Number</label>
                            <input type="text" name="phone" class="form-control rounded-pill px-3" placeholder="e.g. +255 22 123 4567">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Email Address</label>
                        <input type="email" name="email" class="form-control rounded-pill px-3" placeholder="e.g. claims@insurance.co.tz">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Address / Headquarters</label>
                        <textarea name="address" class="form-control rounded px-3" rows="2" placeholder="Office location..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('insurance.index') }}" class="btn btn-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn-laravel-primary rounded-pill px-4">
                            <i class="bi bi-check-circle me-1"></i> Save Provider
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
