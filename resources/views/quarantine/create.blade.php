@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-shield-x me-2 text-warning"></i> Place Batch in Quarantine</h2>
        <p class="text-white-50 mb-0">Isolate a batch from POS dispensing and active inventory.</p>
    </div>
    <div>
        <a href="{{ route('quarantine.index') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="bi bi-arrow-left me-1"></i> Back to Quarantine List
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card-laravel animate-fade-in">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 text-danger"><i class="bi bi-exclamation-triangle me-2"></i> Quarantine Order Details</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('quarantine.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Select Batch <span class="text-danger">*</span></label>
                        <select name="batch_id" class="form-select rounded-pill px-3" required>
                            <option value="">-- Choose Medicine Batch --</option>
                            @foreach($batches as $b)
                                <option value="{{ $b->id }}">{{ $b->drug?->name ?? 'Medicine' }} (Batch: {{ $b->batch_number }}, Exp: {{ $b->expiry_date }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Reason for Quarantine <span class="text-danger">*</span></label>
                        <select name="reason" class="form-select rounded-pill px-3" required>
                            <option value="Damaged Packaging">Damaged Packaging</option>
                            <option value="Manufacturer Recall">Manufacturer Recall</option>
                            <option value="Quality Defect / Discoloration">Quality Defect / Discoloration</option>
                            <option value="Storage Temperature Compromise">Storage Temperature Compromise</option>
                            <option value="Near Expiry / Expired">Near Expiry / Expired</option>
                            <option value="Suspected Counterfeit">Suspected Counterfeit</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Audit Notes / Investigation Findings</label>
                        <textarea name="notes" class="form-control rounded px-3" rows="3" placeholder="Explain details of defect or supplier batch recall notice..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('quarantine.index') }}" class="btn btn-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-danger rounded-pill px-4">
                            <i class="bi bi-shield-x me-1"></i> Quarantine Batch
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
