@extends('layouts.app')

@section('content')
<!-- Daawafy Executive VAT Settings View -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-percent me-2 text-info"></i> VAT & Tax Configuration Hub</h2>
        <p class="text-white-50 mb-0">TRA VAT rate configuration, EFD/VAT invoice registration, and tax calculation logs.</p>
    </div>
</div>

<?php if(isset($msg)): ?>
    <div class="alert alert-success rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-check-circle me-2"></i> <?= $msg ?></div>
<?php endif; ?>
<?php if(isset($error)): ?>
    <div class="alert alert-danger rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-exclamation-triangle me-2"></i> <?= $error ?></div>
<?php endif; ?>

<!-- Stats Overview -->
<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-md-4">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Active VAT Rate</div>
                    <div class="metric-value text-primary"><?= number_format($vat_settings['vat_rate'], 1) ?>%</div>
                </div>
                <div class="metric-icon"><i class="bi bi-percent"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Monthly Tax Collected</div>
                    <div class="metric-value text-success">TZS <?= number_format($monthly_vat) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-cash-stack"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-yellow">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">TRA Registration</div>
                    <div class="metric-value text-warning"><?= $vat_settings['is_vat_registered'] ? 'Registered' : 'Exempt' ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-shield-check"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Main Form Card -->
<div class="card-laravel animate-fade-in">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0 text-primary"><i class="bi bi-sliders me-2"></i> Tax Rate & Registration Settings</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('addons.vat') }}">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Standard VAT Tax Rate (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="vat_rate" class="form-control rounded-pill px-3" value="<?= htmlspecialchars($vat_settings['vat_rate']) ?>" required>
                    <small class="text-muted ps-2">Default TRA VAT rate in Tanzania is 18.00%</small>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">TRA Taxpayer TIN / VRN Number</label>
                    <input type="text" name="vat_number" class="form-control rounded-pill px-3" placeholder="e.g. 100-200-300 VRN 40012" value="<?= htmlspecialchars($vat_settings['vat_number']) ?>">
                </div>
                <div class="col-md-12">
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_vat_registered" id="vatRegCheck" <?= $vat_settings['is_vat_registered'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold text-muted small" for="vatRegCheck">Pharmacy is TRA VAT Registered (Automatically itemize VAT on POS Receipts)</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn-laravel-primary">
                <i class="bi bi-check-circle me-1"></i> Save VAT Preferences
            </button>
        </form>
    </div>
</div>
@endsection
