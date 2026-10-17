<?php $__env->startSection('content'); ?>
<!-- Daawafy Executive Advanced Pharmacy Ops & Prescriptions View -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-shield-check me-2 text-info"></i> Advanced Pharmacy Ops & DDA Compliance Register</h2>
        <p class="text-white-50 mb-0">Prescription-Only Medicines (POM) patient logs, prescriber details, and Dangerous Drugs Act (DDA) register.</p>
    </div>
</div>

<!-- Stats Overview -->
<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-md-6">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Prescription Records (POM)</div>
                    <div class="metric-value text-primary"><?= count($prescriptions) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-file-earmark-medical"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="metric-widget metric-red">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">DDA Controlled Register Entries</div>
                    <div class="metric-value text-danger"><?= count($dda) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-shield-exclamation"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Tabbed Card Container -->
<div class="card-laravel animate-fade-in">
    <div class="card-header bg-white border-bottom">
        <ul class="nav nav-pills card-header-pills" id="pharmacyTabs">
            <li class="nav-item">
                <button class="nav-link active rounded-pill fw-bold" onclick="switchPharmacyTab('pom', this)">
                    <i class="bi bi-file-earmark-medical me-1"></i> POM Prescriptions (<?= count($prescriptions) ?>)
                </button>
            </li>
            <li class="nav-item ms-2">
                <button class="nav-link rounded-pill fw-bold" onclick="switchPharmacyTab('dda', this)">
                    <i class="bi bi-shield-exclamation me-1 text-danger"></i> DDA Controlled Register (<?= count($dda) ?>)
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        <!-- 1. POM Prescriptions Tab -->
        <div id="tabPom" class="tab-pane-content">
            <div class="table-responsive">
                <table class="table table-laravel mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Dispensed Medicine</th>
                            <th>Patient Name</th>
                            <th>Prescribing Doctor</th>
                            <th>Hospital / Clinic</th>
                            <th>Dispensed Qty</th>
                            <th>Date Logged</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($prescriptions)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No POM prescriptions logged yet.</td></tr>
                        <?php else: ?>
                            <?php foreach($prescriptions as $idx => $p): ?>
                                <tr>
                                    <td class="text-muted"><?= $idx + 1 ?></td>
                                    <td><strong class="text-dark d-block"><?= htmlspecialchars($p['drug_name'] ?? 'Medicine') ?></strong></td>
                                    <td><i class="bi bi-person me-1 text-primary"></i> <?= htmlspecialchars(($p['patient_name'] ?? '') ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars(($p['doctor_name'] ?? '') ?: 'Dr. Prescriber') ?></td>
                                    <td><small class="text-muted"><?= htmlspecialchars(($p['hospital_name'] ?? '') ?: 'Clinic') ?></small></td>
                                    <td><span class="badge-laravel-blue"><?= $p['quantity'] ?? 1 ?> units</span></td>
                                    <td><small class="text-muted"><?= isset($p['created_at']) ? date('d M Y, H:i', strtotime($p['created_at'])) : 'N/A' ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. DDA Register Tab -->
        <div id="tabDda" class="tab-pane-content d-none">
            <div class="table-responsive">
                <table class="table table-laravel mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Controlled Medicine</th>
                            <th>Patient Name & ID</th>
                            <th>Prescriber Details</th>
                            <th>Refill Instructions</th>
                            <th>Dispensed Qty</th>
                            <th>Audit Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($dda)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted"><i class="bi bi-shield-check text-success me-1"></i> No DDA controlled drug register entries.</td></tr>
                        <?php else: ?>
                            <?php foreach($dda as $idx => $d): ?>
                                <tr>
                                    <td class="text-muted"><?= $idx + 1 ?></td>
                                    <td><strong class="text-danger d-block"><?= htmlspecialchars($d['drug_name'] ?? 'Controlled Medicine') ?></strong></td>
                                    <td><?= htmlspecialchars(($d['patient_name'] ?? '') ?: 'N/A') ?></td>
                                    <td><?= htmlspecialchars(($d['doctor_name'] ?? '') ?: 'N/A') ?></td>
                                    <td><span class="badge-laravel-yellow"><?= htmlspecialchars(($d['instructions'] ?? '') ?: 'Standard DDA') ?></span></td>
                                    <td><span class="badge-laravel-danger"><?= $d['quantity'] ?? 1 ?> units</span></td>
                                    <td><small class="text-muted"><?= isset($d['created_at']) ? date('d M Y, H:i', strtotime($d['created_at'])) : 'N/A' ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function switchPharmacyTab(tab, btn) {
    document.querySelectorAll('#pharmacyTabs .nav-link').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.tab-pane-content').forEach(p => p.classList.add('d-none'));
    if(tab === 'pom') document.getElementById('tabPom').classList.remove('d-none');
    else if(tab === 'dda') document.getElementById('tabDda').classList.remove('d-none');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\pms\resources\views/prescriptions.blade.php ENDPATH**/ ?>