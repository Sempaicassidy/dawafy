@extends('layouts.app')

@section('content')
<!-- Daawafy Executive Settings View (100% Professional English) -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-sliders me-2 text-info"></i> System & Pharmacy Settings</h2>
        <p class="text-white-50 mb-0">Pharmacy info, receipt headers, TMDA license details, VAT rates, and printer settings.</p>
    </div>
    <div>
        <button class="btn-laravel-green text-white shadow" onclick="document.querySelector('form').submit()">
            <i class="bi bi-check-circle me-1"></i> Save Configuration
        </button>
    </div>
</div>

<div class="card-laravel animate-fade-in">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-sliders me-2"></i> General Configuration</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="fw-bold text-muted small">Pharmacy Company Name</label>
                    <input type="text" name="company_name" class="form-control rounded-pill px-3" value="<?= htmlspecialchars($settings['company_name'] ?? 'Daawafy Pharmacy') ?>">
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-muted small">Phone Number</label>
                    <input type="text" name="phone" class="form-control rounded-pill px-3" value="<?= htmlspecialchars($settings['phone'] ?? '') ?>">
                </div>
                <div class="col-md-12">
                    <label class="fw-bold text-muted small">Receipt Address Header</label>
                    <textarea name="address" class="form-control rounded-3" rows="3"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
                </div>
            </div>
            <button type="submit" name="save_settings" class="btn-laravel-primary">
                <i class="bi bi-save me-1"></i> Save Preferences
            </button>
        </form>
    </div>
</div>

<!-- Active Feature Add-ons & Subscription Privileges -->
<div class="card-laravel mt-4 animate-fade-in">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary"><i class="bi bi-star-fill text-warning me-2"></i> Contract Policies & Active Feature Privileges</h5>
        <span class="badge-laravel-green"><i class="bi bi-check-all me-1"></i> SaaS Subscription Active</span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <h6 class="fw-bold text-muted small uppercase mb-3"><i class="bi bi-file-earmark-check me-1 text-info"></i> Contract Agreements & Policies</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2 border-0">
                        <span><i class="bi bi-check-circle-fill text-success me-2"></i> Service Level Agreement (SLA) Signed</span>
                        <span class="badge bg-success rounded-pill">Verified</span>
                    </li>
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2 border-0">
                        <span><i class="bi bi-check-circle-fill text-success me-2"></i> Non-Disclosure Agreement (NDA) Signed</span>
                        <span class="badge bg-success rounded-pill">Verified</span>
                    </li>
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2 border-0">
                        <span><i class="bi bi-check-circle-fill text-success me-2"></i> System Training Completed</span>
                        <span class="badge bg-success rounded-pill">Completed</span>
                    </li>
                    <li class="list-group-item d-flex align-items-center justify-content-between px-0 py-2 border-0">
                        <span><i class="bi bi-check-circle-fill text-success me-2"></i> Initial Setup & Config Done</span>
                        <span class="badge bg-success rounded-pill">Configured</span>
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold text-muted small uppercase mb-3"><i class="bi bi-plug-fill me-1 text-primary"></i> Enabled Feature Add-ons</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php 
                    global $SYS_SETTINGS;
                    $isSuperAdmin = (($_SESSION['actual_role'] ?? $_SESSION['role'] ?? '') === 'Super Admin');
                    $features_map = [
                        'feature_ai_sales' => ['label' => 'AI Sales Report', 'icon' => 'bi-robot', 'url' => 'ai_sales_report.php'],
                        'feature_advanced_pharmacy' => ['label' => 'Advanced Pharmacy Ops', 'icon' => 'bi-shield-check', 'url' => 'prescriptions.php'],
                        'feature_whatsapp' => ['label' => 'WhatsApp Integration', 'icon' => 'bi-whatsapp', 'url' => 'whatsapp_customers.php'],
                        'feature_wallet' => ['label' => 'Marg Wallet', 'icon' => 'bi-wallet2', 'url' => 'wallet.php'],
                        'feature_data_transfer' => ['label' => 'Data Transfer', 'icon' => 'bi-arrow-left-right', 'url' => 'data_transfer.php'],
                        'feature_erp' => ['label' => 'ERP Integration', 'icon' => 'bi-diagram-3', 'url' => 'erp_integration.php'],
                        'feature_online_purchases' => ['label' => 'Online Purchases', 'icon' => 'bi-cart-check', 'url' => 'online_purchases.php'],
                        'feature_vat' => ['label' => 'VAT Settings', 'icon' => 'bi-percent', 'url' => 'vat_settings.php'],
                        'feature_backup' => ['label' => 'Cloud Backup', 'icon' => 'bi-cloud-arrow-up', 'url' => 'backup_system.php']
                    ];
                    ?>
                    <?php foreach($features_map as $key => $f): ?>
                        <?php if($isSuperAdmin || !empty($SYS_SETTINGS[$key])): ?>
                            <a href="<?= $f['url'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-2 text-decoration-none shadow-sm fw-semibold">
                                <i class="bi <?= $f['icon'] ?> text-info me-1"></i> <?= $f['label'] ?> <i class="bi bi-check-circle-fill text-success ms-1"></i>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
