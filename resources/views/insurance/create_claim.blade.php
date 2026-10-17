@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-file-earmark-plus me-2 text-info"></i> File Insurance Claim</h2>
        <p class="text-white-50 mb-0">Submit patient medication expense claim to insurance provider.</p>
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
                <h5 class="mb-0 text-primary"><i class="bi bi-receipt me-2"></i> Insurance Claim Submission</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('insurance.store_claim') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Select Insurance Provider <span class="text-danger">*</span></label>
                        <select name="insurance_provider_id" class="form-select rounded-pill px-3" required>
                            <option value="">-- Choose Provider --</option>
                            @foreach($providers as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Transaction ID <span class="text-danger">*</span></label>
                            <input type="number" name="transaction_id" class="form-control rounded-pill px-3" placeholder="Enter Sales ID" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Claim Amount (TZS) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control rounded-pill px-3" placeholder="e.g. 75000" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Member Policy # / Notes</label>
                        <textarea name="notes" class="form-control rounded px-3" rows="3" placeholder="Member card number, pre-authorization code..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('insurance.index') }}" class="btn btn-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn-laravel-primary rounded-pill px-4">
                            <i class="bi bi-send me-1"></i> Submit Claim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
