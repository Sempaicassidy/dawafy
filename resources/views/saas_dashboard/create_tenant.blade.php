@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-building-add me-2 text-info"></i> Register New Pharmacy Tenant / Branch</h2>
        <p class="text-white-50 mb-0">Create and provision a new pharmacy business entity on Dawafy SaaS.</p>
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
                <h5 class="mb-0 text-primary"><i class="bi bi-shop me-2"></i> Tenant Configuration</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('saas.store_tenant') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Pharmacy Business Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control rounded-pill px-3" placeholder="e.g. Apex Healthcare Pharmacy" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Contact Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control rounded-pill px-3" placeholder="e.g. +255 712 345 678" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-pill px-3" placeholder="e.g. info@apexpharmacy.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Physical Address / City</label>
                        <input type="text" name="address" class="form-control rounded-pill px-3" placeholder="e.g. Samora Avenue, Dar es Salaam">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Subscription Plan</label>
                            <select name="plan" class="form-select rounded-pill px-3">
                                <option value="Standard">Standard Monthly (TZS 50,000)</option>
                                <option value="Professional" selected>Professional Enterprise (TZS 100,000)</option>
                                <option value="Trial">Free Trial (14 Days)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Payment Status</label>
                            <select name="payment_status" class="form-select rounded-pill px-3">
                                <option value="Paid" selected>Paid / Active</option>
                                <option value="Pending">Pending Payment</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('saas.dashboard') }}" class="btn btn-secondary rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn-laravel-primary rounded-pill px-4">
                            <i class="bi bi-check-circle me-1"></i> Provision Tenant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
