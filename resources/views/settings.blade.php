@extends('layouts.app')

@section('content')
<!-- Dawafy Executive Settings View (Mobile-First & Brand Logo Upload) -->
<div class="laravel-hero-banner mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1 text-dark"><i class="bi bi-sliders me-2 text-primary"></i> System & Pharmacy Brand Settings</h2>
        <p class="text-muted mb-0 small">Manage your pharmacy identity, upload official logo, configure thermal receipt headers, and TMDA parameters.</p>
    </div>
    <div>
        <button class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm" onclick="document.getElementById('settingsForm').submit()">
            <i class="bi bi-check-circle me-1"></i> Save Changes
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Insurance API Integration Quick Link Banner -->
<div class="card shadow-sm border-0 border-start border-4 border-primary rounded-4 mb-4 p-3 bg-white animate-fade-in">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                <i class="bi bi-shield-check fs-3"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Electronic Insurance Gateways (NHIF, Jubilee & Strategis)</h6>
                <small class="text-muted">Manage API credentials, facility codes, test connections, and toggle between Sandbox (Sample Mode) and Live Mode.</small>
            </div>
        </div>
        <div>
            <a href="{{ route('insurance.index') }}" class="btn btn-outline-primary rounded-pill px-4 fw-semibold">
                <i class="bi bi-gear-wide-connected me-1"></i> Configure Insurance APIs
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-4 animate-fade-in mb-4 bg-white">
    <div class="card-header bg-white border-bottom py-3 px-4">
        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-shop me-2 text-primary"></i> Pharmacy Identity & Receipt Header</h5>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" id="settingsForm">
            @csrf

            <!-- Logo Upload Section -->
            <div class="row align-items-center mb-4 pb-4 border-bottom g-3">
                <div class="col-md-3 text-center text-md-start">
                    <label class="fw-bold text-dark d-block mb-1">Pharmacy Logo</label>
                    <small class="text-muted d-block mb-2">Displayed across navbar, reports, and thermal receipts (PNG / JPEG, max 2MB).</small>
                    
                    <div class="border rounded-4 p-2 d-inline-block bg-light shadow-sm" style="width: 130px; height: 130px; position: relative; overflow: hidden;">
                        <img id="logoPreview" 
                             src="{{ !empty($settings['logo_path']) ? asset($settings['logo_path']) : asset('images/logo.png') }}" 
                             alt="Pharmacy Logo" 
                             class="w-100 h-100 object-fit-contain rounded-3"
                             onerror="this.src='https://ui-avatars.com/api/?name=Dawafy+Pharmacy&background=0D8ABC&color=fff&size=128'">
                    </div>
                </div>
                <div class="col-md-9">
                    <label class="fw-semibold text-secondary small mb-1">Choose New Logo File</label>
                    <input type="file" name="logo" class="form-control rounded-pill" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="previewLogo(event)">
                    <small class="text-muted mt-1 d-block">Recommended resolution: 400x400 transparent PNG for crisp thermal printing.</small>
                </div>
            </div>

            <!-- Pharmacy Name & Contacts -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="fw-bold text-dark small mb-1">Pharmacy Trade Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="bi bi-building"></i></span>
                        <input type="text" name="company_name" class="form-control" value="{{ $settings['company_name'] ?? 'Dawafy Pharmacy' }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-dark small mb-1">Official Phone Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="bi bi-telephone"></i></span>
                        <input type="text" name="phone" class="form-control" value="{{ $settings['phone'] ?? '+255 712 345 678' }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-dark small mb-1">TRA TIN Number</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="bi bi-receipt"></i></span>
                        <input type="text" name="tin" class="form-control" value="{{ $settings['tin'] ?? '101-001-001' }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-dark small mb-1">VAT Registration Number</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary"><i class="bi bi-percent"></i></span>
                        <input type="text" name="vat_reg" class="form-control" value="{{ $settings['vat_reg'] ?? 'VAT-001' }}">
                    </div>
                </div>
                <div class="col-md-12">
                    <label class="fw-bold text-dark small mb-1">Physical Location & Receipt Header Address</label>
                    <textarea name="address" class="form-control rounded-3" rows="3" placeholder="e.g. Plot 42, Samora Avenue, P.O. Box 1234, Dar es Salaam, Tanzania">{{ $settings['address'] ?? '' }}</textarea>
                    <small class="text-muted">This address is printed directly on top of customer thermal receipts.</small>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                    <i class="bi bi-save me-1"></i> Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewLogo(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
