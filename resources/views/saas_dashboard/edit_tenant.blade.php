@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-pencil-square me-2 text-info"></i> Edit Tenant / Branch Contract</h2>
        <p class="text-white-50 mb-0">Update license terms, billing, and operational status for {{ $tenant->name }}.</p>
    </div>
    <div>
        <a href="{{ route('saas.dashboard') }}" class="btn btn-outline-light rounded-pill px-4">
            <i class="bi bi-arrow-left me-1"></i> Back to SaaS Portal
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card-laravel animate-fade-in">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0 text-primary"><i class="bi bi-shield-lock me-2"></i> Tenant Details: {{ $tenant->name }}</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('saas.update_tenant', $tenant) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Pharmacy Business Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-pill px-3" value="{{ old('name', $tenant->name) }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Contact Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control rounded-pill px-3" value="{{ old('phone', $tenant->phone) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-pill px-3" value="{{ old('email', $tenant->email) }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Physical Address / City</label>
                        <input type="text" name="address" class="form-control rounded-pill px-3" value="{{ old('address', $tenant->address) }}">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Status</label>
                            <select name="status" class="form-select rounded-pill px-3">
                                <option value="Active" {{ $tenant->status === 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ $tenant->status === 'Inactive' ? 'selected' : '' }}>Inactive / Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Payment Status</label>
                            <select name="payment_status" class="form-select rounded-pill px-3">
                                <option value="Paid" {{ $tenant->payment_status === 'Paid' ? 'selected' : '' }}>Paid / Valid</option>
                                <option value="Pending" {{ $tenant->payment_status === 'Pending' ? 'selected' : '' }}>Pending Payment</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Monthly Fee (TZS)</label>
                            <input type="number" step="0.01" name="subscription_price" class="form-control rounded-pill px-3" value="{{ old('subscription_price', $tenant->subscription_price ?? 100000) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">License Expiry Date <span class="text-danger">*</span></label>
                            <input type="date" name="license_expiry" class="form-control rounded-pill px-3" value="{{ old('license_expiry', $tenant->license_expiry ? date('Y-m-d', strtotime($tenant->license_expiry)) : date('Y-m-d', strtotime('+1 year'))) }}" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('saas.dashboard') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-check-circle me-1"></i> Update Contract
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
