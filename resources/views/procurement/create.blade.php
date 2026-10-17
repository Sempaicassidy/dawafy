@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-cart-plus me-2 text-info"></i> Create Purchase Order</h2>
        <p class="text-white-50 mb-0">Order medicines and medical supplies from suppliers.</p>
    </div>
    <div>
        <a href="{{ route('procurement.index') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="bi bi-arrow-left me-1"></i> Back to Orders
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card-laravel animate-fade-in">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 text-primary"><i class="bi bi-file-earmark-plus me-2"></i> Purchase Order Details</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('procurement.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Select Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-select rounded-pill px-3" required>
                            <option value="">-- Choose Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Expected Delivery Date</label>
                        <input type="date" name="expected_delivery_date" class="form-control rounded-pill px-3" value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Notes / Special Instructions</label>
                        <textarea name="notes" class="form-control rounded px-3" rows="3" placeholder="Order details, terms, delivery location..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('procurement.index') }}" class="btn btn-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn-laravel-primary rounded-pill px-4">
                            <i class="bi bi-check-circle me-1"></i> Submit Purchase Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
