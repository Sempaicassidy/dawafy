<?php $__env->startSection('content'); ?>
<!-- Daawafy Executive SaaS Super Admin Dashboard View -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-building-gear me-2 text-info"></i> SaaS Super Admin Portal & Multi-Tenant Hub</h2>
        <p class="text-white-50 mb-0">Control pharmacy tenants, activate branch licenses, manage contract add-ons, and MRR billing.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('saas.super_admins')); ?>" class="btn btn-outline-light shadow rounded-pill px-3">
            <i class="bi bi-shield-lock me-1"></i> Super Admins
        </a>
        <button class="btn-laravel-green text-white shadow" data-bs-toggle="modal" data-bs-target="#addTenantModal">
            <i class="bi bi-plus-circle me-1"></i> + Register New Pharmacy Tenant
        </button>
    </div>
</div>

<?php if(session('success') || isset($msg)): ?>
    <div class="alert alert-success rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-check-circle me-2"></i> <?php echo e(session('success') ?? $msg); ?></div>
<?php endif; ?>
<?php if(session('error') || isset($error)): ?>
    <div class="alert alert-danger rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-exclamation-triangle me-2"></i> <?php echo e(session('error') ?? $error); ?></div>
<?php endif; ?>
<?php if(session('info')): ?>
    <div class="alert alert-info rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-info-circle me-2"></i> <?php echo e(session('info')); ?></div>
<?php endif; ?>

<!-- Stats Overview Cards -->
<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-md-3">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Active HQ Pharmacies</div>
                    <div class="metric-value"><?= count($hq_tenants) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-buildings"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-cyan">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Total Sub-Branches</div>
                    <div class="metric-value"><?= count($all_branches_raw) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-shop"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">MRR (This Month)</div>
                    <div class="metric-value">TZS <?= number_format($monthly_revenue) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-cash-coin"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-purple">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Lifetime SaaS Revenue</div>
                    <div class="metric-value">TZS <?= number_format($total_revenue) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-wallet2"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Branch Activations (If any) -->
<?php if(!empty($pending_branches)): ?>
<div class="card-laravel mb-4 border-warning animate-fade-in">
    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold"><i class="bi bi-hourglass-split me-2"></i> Sub-Branches Awaiting Activation</h5>
        <span class="badge bg-dark rounded-pill px-3"><?= count($pending_branches) ?> Pending</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0">
                <thead>
                    <tr>
                        <th>Branch Outlet Name</th>
                        <th>Parent HQ Pharmacy</th>
                        <th>Phone / Contact</th>
                        <th>Address</th>
                        <th>Requested On</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pending_branches as $pb): ?>
                        <tr>
                            <td>
                                <strong class="text-dark d-block"><?= htmlspecialchars($pb['name']) ?></strong>
                                <span class="badge-laravel-yellow"><i class="bi bi-lock me-1"></i> Inactive</span>
                            </td>
                            <td><span class="badge-laravel-blue"><i class="bi bi-building me-1"></i> <?= htmlspecialchars($pb['parent_name']) ?></span></td>
                            <td><span class="font-monospace small"><?= htmlspecialchars($pb['phone'] ?: 'N/A') ?></span></td>
                            <td><small class="text-muted"><?= htmlspecialchars($pb['address'] ?: 'N/A') ?></small></td>
                            <td><small class="text-muted"><?= date('d M Y, H:i', strtotime($pb['created_at'])) ?></small></td>
                            <td class="text-end">
                                <a href="<?php echo e(route('saas.activate_branch', $pb['id'])); ?>" class="btn btn-sm btn-laravel-green text-white rounded-pill px-3 shadow-sm">
                                    <i class="bi bi-check-circle me-1"></i> Activate Branch License
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Pharmacy Tenants Directory -->
<div class="card-laravel animate-fade-in">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary"><i class="bi bi-buildings me-2"></i> Registered Pharmacy Tenants & Sub-Branches</h5>
        <input type="text" class="form-control form-control-sm rounded-pill px-3 w-25" id="saasSearch" placeholder="Search pharmacy tenant..." onkeyup="filterSaasTable()">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0" id="saasTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pharmacy Tenant & Branches</th>
                        <th>Contact Info</th>
                        <th>Active Add-on Features</th>
                        <th>Subscription & Expiry</th>
                        <th>Status</th>
                        <th class="text-end">Super Admin Controls</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($hq_tenants as $idx => $t): ?>
                        <tr class="table-light">
                            <td class="fw-bold text-muted"><?= $idx + 1 ?></td>
                            <td>
                                <strong class="text-dark d-block fs-6"><i class="bi bi-building text-primary me-1"></i> <?= htmlspecialchars($t['name']) ?></strong>
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($t['address'] ?: 'HQ') ?>
                                    <?php if($t['branch_count'] > 0): ?>
                                        <button class="btn btn-sm btn-light border rounded-pill px-2 py-0 ms-2 text-primary fw-bold shadow-sm" onclick="toggleBranchRows(<?= $t['id'] ?>)" id="btn-branch-<?= $t['id'] ?>">
                                            <i class="bi bi-chevron-down me-1" id="icon-branch-<?= $t['id'] ?>"></i> <span id="label-branch-<?= $t['id'] ?>">Show <?= $t['branch_count'] ?> Branches</span>
                                        </button>
                                    <?php else: ?>
                                        <span class="ms-1 text-muted">| No Sub-branches</span>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <span class="font-monospace small text-primary"><?= htmlspecialchars($t['phone'] ?: 'N/A') ?></span>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php if(!empty($t['feature_ai_sales'])): ?><span class="badge-laravel-blue"><i class="bi bi-robot"></i> AI Sales</span><?php endif; ?>
                                    <?php if(!empty($t['feature_whatsapp'])): ?><span class="badge-laravel-green"><i class="bi bi-whatsapp"></i> WhatsApp</span><?php endif; ?>
                                    <?php if(!empty($t['feature_wallet'])): ?><span class="badge-laravel-yellow"><i class="bi bi-wallet2"></i> Wallet</span><?php endif; ?>
                                    <?php if(!empty($t['feature_erp'])): ?><span class="badge-laravel-blue"><i class="bi bi-diagram-3"></i> ERP</span><?php endif; ?>
                                    <?php if(!empty($t['feature_advanced_pharmacy'])): ?><span class="badge-laravel-green"><i class="bi bi-shield-check"></i> Ops</span><?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <strong class="text-primary d-block">TZS <?= number_format($t['subscription_price'] ?: 50000) ?> / mo</strong>
                                <small class="text-muted"><?= $t['license_expiry'] ? date('d M Y', strtotime($t['license_expiry'])) : 'Unlimited' ?></small>
                            </td>
                            <td>
                                <?php if($t['status'] === 'Active'): ?>
                                    <span class="badge-laravel-green"><i class="bi bi-check-circle-fill me-1"></i> Active</span>
                                <?php else: ?>
                                    <span class="badge-laravel-danger"><i class="bi bi-pause-circle me-1"></i> Suspended</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end align-middle" style="white-space: nowrap;">
                                <div class="d-inline-flex align-items-center gap-1 justify-content-end">
                                    <a href="<?php echo e(route('saas.support_login', $t['id'])); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold text-nowrap" title="Log in as Admin for support">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Support Login
                                    </a>
                                    <button class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-semibold text-nowrap" onclick="openContractModal(<?= htmlspecialchars(json_encode($t)) ?>)">
                                        <i class="bi bi-sliders me-1"></i> Contract & Add-ons
                                    </button>
                                    <a href="<?php echo e(route('saas.toggle_status', $t['id'])); ?>" class="btn btn-sm btn-outline-danger rounded-circle p-1 ms-1 d-inline-flex align-items-center justify-content-center" style="width: 30px; height: 30px;" title="Toggle Active/Suspended">
                                        <i class="bi bi-power"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Sub-branches under this HQ (Collapsible) -->
                        <?php 
                        $subBranches = !empty($branches_by_hq[$t['id']]) ? $branches_by_hq[$t['id']] : (!empty($t['branches']) ? $t['branches'] : []);
                        ?>
                        <?php if(!empty($subBranches)): ?>
                            <?php foreach($subBranches as $sub): ?>
                                <tr class="branch-row-<?= $t['id'] ?> d-none" style="background-color: #f8fafc;">
                                    <td></td>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-arrow-return-right text-primary me-2 fs-6"></i>
                                            <div>
                                                <strong class="text-dark d-block">
                                                    <i class="bi bi-shop me-1 text-primary"></i> <?= htmlspecialchars($sub['name']) ?>
                                                </strong>
                                                <small class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($sub['address'] ?: 'Branch Outlet') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="font-monospace small text-primary"><?= htmlspecialchars($sub['phone'] ?: 'N/A') ?></span></td>
                                    <td><span class="badge bg-light text-secondary border rounded-pill px-2 py-1"><i class="bi bi-diagram-2 me-1"></i> Sub-Branch</span></td>
                                    <td><small class="text-muted">Under HQ License</small></td>
                                    <td>
                                        <?php if($sub['status'] === 'Active'): ?>
                                            <span class="badge-laravel-green"><i class="bi bi-check-circle-fill me-1"></i> Active</span>
                                        <?php else: ?>
                                            <span class="badge-laravel-yellow"><i class="bi bi-lock me-1"></i> Pending Activation</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end align-middle" style="white-space: nowrap;">
                                        <div class="d-inline-flex align-items-center gap-1 justify-content-end">
                                            <a href="<?php echo e(route('saas.support_login', $sub['id'])); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold text-nowrap" title="Log in as Admin for this branch">
                                                <i class="bi bi-box-arrow-in-right me-1"></i> Support Login
                                            </a>
                                            <?php if($sub['status'] !== 'Active'): ?>
                                                <a href="<?php echo e(route('saas.activate_branch', $sub['id'])); ?>" class="btn btn-sm btn-laravel-green text-white rounded-pill px-3 py-1 shadow-sm">
                                                    <i class="bi bi-check-lg me-1"></i> Activate Branch
                                                </a>
                                            <?php else: ?>
                                                <a href="<?php echo e(route('saas.deactivate_branch', $sub['id'])); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1">
                                                    Deactivate
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal 1: Register New Pharmacy Tenant -->
<div class="modal fade" id="addTenantModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-building-plus me-2"></i> Register New Pharmacy Tenant</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="<?php echo e(route('saas.store_tenant')); ?>">
        <?php echo csrf_field(); ?>
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Pharmacy Company Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control rounded-pill px-3" placeholder="e.g. Apex Pharmacy Ltd" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Contact Phone Number <span class="text-danger">*</span></label>
                <input type="text" name="phone" class="form-control rounded-pill px-3" placeholder="e.g. +255 712 345 678" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Physical Location Address</label>
                <input type="text" name="address" class="form-control rounded-pill px-3" placeholder="e.g. Samora Avenue, Dar es Salaam">
            </div>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="fw-bold text-muted small mb-1">Subscription Price (TZS)</label>
                    <input type="number" name="subscription_price" class="form-control rounded-pill px-3" value="50000">
                </div>
                <div class="col-6">
                    <label class="fw-bold text-muted small mb-1">License Expiry Date</label>
                    <input type="date" name="license_expiry" class="form-control rounded-pill px-3">
                </div>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-laravel-primary rounded-pill px-4 fw-bold"><i class="bi bi-check-circle me-1"></i> Register Tenant</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 2: Contract Policies & Feature Add-ons Manager -->
<div class="modal fade" id="contractModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-sliders me-2"></i> Manage Tenant Contract & Enabled Add-ons</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="<?php echo e(route('saas.update_contract')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" id="contractTenantId" name="tenant_id">
        <div class="modal-body p-4">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Target Pharmacy</label>
                    <input type="text" id="contractTenantName" class="form-control rounded-pill px-3 bg-light" readonly>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Payment Status <span class="text-danger">*</span></label>
                    <select id="contractPaymentStatus" name="payment_status" class="form-select rounded-pill px-3" required>
                        <option value="Paid">Paid / Active Subscription</option>
                        <option value="Pending">Pending Payment</option>
                        <option value="Overdue">Overdue / Suspended</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Subscription Price (TZS)</label>
                    <input type="number" id="contractSubPrice" name="subscription_price" class="form-control rounded-pill px-3" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">License Expiry Date</label>
                    <input type="date" id="contractLicenseExpiry" name="license_expiry" class="form-control rounded-pill px-3">
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-star-fill text-warning me-2"></i> Enable Feature Add-ons for Tenant</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="f_ai" id="f_ai">
                        <label class="form-check-label fw-bold text-muted small" for="f_ai">🤖 AI Sales Report</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="f_wa" id="f_wa">
                        <label class="form-check-label fw-bold text-muted small" for="f_wa">💬 WhatsApp Integration</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="f_wal" id="f_wal">
                        <label class="form-check-label fw-bold text-muted small" for="f_wal">💳 Marg Wallet & Points</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="f_dt" id="f_dt">
                        <label class="form-check-label fw-bold text-muted small" for="f_dt">🔄 Data Transfer</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="f_erp" id="f_erp">
                        <label class="form-check-label fw-bold text-muted small" for="f_erp">🔗 ERP Integration</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="f_op" id="f_op">
                        <label class="form-check-label fw-bold text-muted small" for="f_op">🛒 Online Purchases</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="f_vat" id="f_vat">
                        <label class="form-check-label fw-bold text-muted small" for="f_vat">📊 VAT Settings</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="f_bu" id="f_bu">
                        <label class="form-check-label fw-bold text-muted small" for="f_bu">☁️ Cloud Backup</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="f_adv" id="f_adv">
                        <label class="form-check-label fw-bold text-muted small" for="f_adv">🛡️ Advanced Pharmacy Ops</label>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-file-earmark-check me-2"></i> Contract Policies</h6>
            <div class="row g-2">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="policy_sla" id="p_sla">
                        <label class="form-check-label small" for="p_sla">Service Level Agreement (SLA) Signed</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="policy_nda" id="p_nda">
                        <label class="form-check-label small" for="p_nda">Non-Disclosure Agreement (NDA) Signed</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="policy_training" id="p_tr">
                        <label class="form-check-label small" for="p_tr">System Training Completed</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="policy_setup" id="p_st">
                        <label class="form-check-label small" for="p_st">Initial Setup & Config Done</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-laravel-primary rounded-pill px-4 fw-bold"><i class="bi bi-save me-1"></i> Save Contract Privileges</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function filterSaasTable() {
    let input = document.getElementById("saasSearch").value.toLowerCase();
    let rows = document.querySelectorAll("#saasTable tbody tr");
    rows.forEach(r => {
        let text = r.innerText.toLowerCase();
        r.style.display = text.includes(input) ? "" : "none";
    });
}

function openContractModal(tenant) {
    document.getElementById('contractTenantId').value = tenant.id;
    document.getElementById('contractTenantName').value = tenant.name;
    document.getElementById('contractPaymentStatus').value = tenant.payment_status || 'Paid';
    document.getElementById('contractSubPrice').value = tenant.subscription_price || 50000;
    document.getElementById('contractLicenseExpiry').value = tenant.license_expiry || '';

    // Check Add-on Features
    document.getElementById('f_ai').checked = !!tenant.feature_ai_sales;
    document.getElementById('f_wa').checked = !!tenant.feature_whatsapp;
    document.getElementById('f_wal').checked = !!tenant.feature_wallet;
    document.getElementById('f_dt').checked = !!tenant.feature_data_transfer;
    document.getElementById('f_erp').checked = !!tenant.feature_erp;
    document.getElementById('f_op').checked = !!tenant.feature_online_purchases;
    document.getElementById('f_vat').checked = !!tenant.feature_vat;
    document.getElementById('f_bu').checked = !!tenant.feature_backup;
    document.getElementById('f_adv').checked = !!tenant.feature_advanced_pharmacy;

    // Check Contract Policies
    let policies = {};
    try { policies = JSON.parse(tenant.contract_policies || '{}'); } catch(e) {}
    document.getElementById('p_sla').checked = !!policies['SLA Signed'];
    document.getElementById('p_nda').checked = !!policies['NDA Signed'];
    document.getElementById('p_tr').checked = !!policies['Training Completed'];
    document.getElementById('p_st').checked = !!policies['System Setup'];

    new bootstrap.Modal(document.getElementById('contractModal')).show();
}

function toggleBranchRows(hqId) {
    let rows = document.querySelectorAll('.branch-row-' + hqId);
    let icon = document.getElementById('icon-branch-' + hqId);
    let label = document.getElementById('label-branch-' + hqId);
    if (!rows || rows.length === 0) return;

    let currentlyHidden = rows[0].classList.contains('d-none');
    rows.forEach(r => {
        if (currentlyHidden) {
            r.classList.remove('d-none');
        } else {
            r.classList.add('d-none');
        }
    });

    if(icon && label) {
        if(currentlyHidden) {
            icon.className = 'bi bi-chevron-up me-1';
            label.innerText = 'Hide ' + rows.length + ' Branches';
        } else {
            icon.className = 'bi bi-chevron-down me-1';
            label.innerText = 'Show ' + rows.length + ' Branches';
        }
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\pms\resources\views/saas_dashboard.blade.php ENDPATH**/ ?>