@extends('layouts.app')

@section('content')
<!-- Daawafy Executive Inventory View -->
<style>
#inventoryTabs .nav-link {
    color: #495057;
    background-color: transparent;
    border: 1px solid transparent;
    padding: 0.4rem 0.85rem;
    font-size: 0.8125rem;
    font-weight: 500;
}
#inventoryTabs .nav-link.active {
    color: #0d6efd !important;
    background-color: #ffffff !important;
    border-color: #dee2e6 #dee2e6 #ffffff !important;
    font-weight: 600;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold text-dark mb-0">Drug Inventory Directory</h4>
        <small class="text-muted">Active stock management, low-stock reorders, and batch status</small>
    </div>
    <div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDrugModal">
            <i class="bi bi-plus me-1"></i> Add New Medicine
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card p-3 mb-0">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">Active In-Stock</div>
                    <div class="h4 fw-bold text-dark my-1"><?= count($drugs) ?></div>
                </div>
                <div class="text-secondary fs-3"><i class="bi bi-capsule"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 mb-0">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">Out of Stock</div>
                    <div class="h4 fw-bold text-dark my-1"><?= count($out_of_stock_drugs) ?></div>
                </div>
                <div class="text-secondary fs-3"><i class="bi bi-exclamation-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 mb-0">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">Expired Stock</div>
                    <div class="h4 fw-bold text-dark my-1"><?= count($expired_drugs) ?></div>
                </div>
                <div class="text-secondary fs-3"><i class="bi bi-x-octagon"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 mb-0">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">Categories</div>
                    <div class="h4 fw-bold text-dark my-1"><?= count($categories) ?></div>
                </div>
                <div class="text-secondary fs-3"><i class="bi bi-tags"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Tabbed Table Card -->
<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">
        <ul class="nav nav-tabs card-header-tabs" id="inventoryTabs">
            <li class="nav-item">
                <button class="nav-link active" onclick="switchInventoryTab('active', this)">
                    Active Stock (<?= count($drugs) ?>)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" onclick="switchInventoryTab('out_of_stock', this)">
                    Out of Stock (<?= count($out_of_stock_drugs) ?>)
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" onclick="switchInventoryTab('expired', this)">
                    Expired Stock (<?= count($expired_drugs) ?>)
                </button>
            </li>
        </ul>
        <input type="text" class="form-control form-control-sm w-25" id="inventorySearch" placeholder="Filter medicines..." onkeyup="filterInventoryTable()">
    </div>
    <div class="card-body p-0">
        <!-- 1. Active Stock Table -->
        <div id="tabActive" class="tab-pane-content">
            <div class="table-responsive">
                <table class="table table-laravel mb-0" id="activeTable">
                    <thead>
                        <tr>
                            <th>Medicine & Brand</th>
                            <th>TMDA Classification</th>
                            <th>Sub-Category</th>
                            <th>Batch #</th>
                            <th>Available Stock</th>
                            <th>Price (TZS)</th>
                            <th>Expiry Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($drugs)): ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">No active in-stock medicines.</td></tr>
                        <?php else: ?>
                            <?php foreach($drugs as $d): ?>
                                <tr>
                                    <td>
                                        <strong class="text-dark d-block"><?= htmlspecialchars($d['name'] ?? '') ?></strong>
                                        <small class="text-muted">
                                            <span class="badge bg-light text-secondary border me-1"><?= htmlspecialchars($d['dosage_form'] ?? 'Tablet') ?></span>
                                            <?= htmlspecialchars(($d['brand_name'] ?? '') ?: 'Generic') ?><?= !empty($d['strength']) ? ' &bull; ' . htmlspecialchars($d['strength']) : '' ?>
                                        </small>
                                    </td>
                                    <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars(($d['category'] ?? '') ?: 'POM') ?></span></td>
                                    <td>
                                        <?php if(!empty($d['sub_category'])): ?>
                                            <span class="badge bg-light text-secondary border"><i class="bi bi-tag me-1"></i><?= htmlspecialchars($d['sub_category']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="font-monospace small"><?= htmlspecialchars(($d['batch_number'] ?? '') ?: 'N/A') ?></span></td>
                                    <td>
                                        <?php $qty = (int)($d['total_stock'] ?? $d['quantity'] ?? 0); ?>
                                        <span class="status-indicator <?= $qty > 10 ? 'text-success' : 'text-warning' ?>">
                                            <span class="status-dot <?= $qty > 10 ? 'status-dot-success' : 'status-dot-warning' ?>"></span>
                                            <span class="tabular-nums"><?= $qty ?></span> units
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark tabular-nums">TZS <?= number_format((float)($d['price'] ?? 0)) ?></td>
                                    <td><small class="text-muted"><?= htmlspecialchars(($d['nearest_expiry'] ?? $d['expiry_date'] ?? '') ?: 'N/A') ?></small></td>
                                    <td class="text-end" style="white-space: nowrap;">
                                        <button class="btn btn-sm btn-outline-secondary py-1 px-2 me-1" onclick="openEditDrugModal(<?= htmlspecialchars(json_encode($d)) ?>)">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-success py-1 px-2" onclick="openAddStockModal(<?= $d['id'] ?>, '<?= addslashes(htmlspecialchars($d['name'])) ?>')">
                                            <i class="bi bi-plus me-1"></i> Add Stock
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Out of Stock Table -->
        <div id="tabOutOfStock" class="tab-pane-content d-none">
            <div class="table-responsive">
                <table class="table table-laravel mb-0" id="outOfStockTable">
                    <thead>
                        <tr>
                            <th>Medicine & Brand</th>
                            <th>TMDA Classification</th>
                            <th>Sub-Category</th>
                            <th>Stock Status</th>
                            <th>Unit Price</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($out_of_stock_drugs)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No out-of-stock items. All medicines have inventory.</td></tr>
                        <?php else: ?>
                            <?php foreach($out_of_stock_drugs as $d): ?>
                                <tr>
                                    <td>
                                        <strong class="text-dark d-block"><?= htmlspecialchars($d['name']) ?></strong>
                                        <small class="text-muted">
                                            <span class="badge bg-light text-secondary border me-1"><?= htmlspecialchars($d['dosage_form'] ?? 'Tablet') ?></span>
                                            <?= htmlspecialchars($d['brand_name'] ?: 'Generic') ?><?= !empty($d['strength']) ? ' &bull; ' . htmlspecialchars($d['strength']) : '' ?>
                                        </small>
                                    </td>
                                    <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($d['category'] ?: 'POM') ?></span></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($d['sub_category'] ?: 'None') ?></small></td>
                                    <td>
                                        <span class="status-indicator text-danger">
                                            <span class="status-dot status-dot-danger"></span> 0 Units (Out of Stock)
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark tabular-nums">TZS <?= number_format($d['price']) ?></td>
                                    <td class="text-end" style="white-space: nowrap;">
                                        <button class="btn btn-sm btn-outline-secondary py-1 px-2 me-1" onclick="openEditDrugModal(<?= htmlspecialchars(json_encode($d)) ?>)">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-success py-1 px-2" onclick="openAddStockModal(<?= $d['id'] ?>, '<?= addslashes(htmlspecialchars($d['name'])) ?>')">
                                            <i class="bi bi-plus me-1"></i> Reorder / Add Stock
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Expired Stock Table -->
        <div id="tabExpired" class="tab-pane-content d-none">
            <div class="table-responsive">
                <table class="table table-laravel mb-0" id="expiredTable">
                    <thead>
                        <tr>
                            <th>Medicine & Brand</th>
                            <th>Batch #</th>
                            <th>Expired Quantity</th>
                            <th>Expiry Date</th>
                            <th>Quarantine Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($expired_drugs)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-shield-check text-success me-1"></i> Excellent! No expired batches in inventory.</td></tr>
                        <?php else: ?>
                            <?php foreach($expired_drugs as $d): ?>
                                <tr class="bg-danger bg-opacity-10">
                                    <td>
                                        <strong class="text-danger d-block"><?= htmlspecialchars($d['name'] ?? '') ?></strong>
                                        <small class="text-muted"><?= htmlspecialchars(($d['brand_name'] ?? '') ?: 'Generic') ?></small>
                                    </td>
                                    <td><span class="font-monospace text-dark font-bold"><?= htmlspecialchars(($d['batch_number'] ?? '') ?: 'N/A') ?></span></td>
                                    <td><span class="badge bg-danger rounded-pill"><?= htmlspecialchars((string)($d['quantity'] ?? $d['total_stock'] ?? 0)) ?> units</span></td>
                                    <td><strong class="text-danger"><?= htmlspecialchars((string)($d['expiry_date'] ?? $d['nearest_expiry'] ?? 'N/A')) ?></strong></td>
                                    <td><span class="badge bg-dark rounded-pill"><i class="bi bi-slash-circle me-1"></i> Quarantined (Do Not Dispense)</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Add New Medicine -->
<div class="modal fade" id="addDrugModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white py-2 px-3">
        <h6 class="modal-title mb-0"><i class="bi bi-plus-circle me-2"></i> Add New Medicine to Inventory</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('inventory.store') }}">
        @csrf
        <input type="hidden" name="add_drug" value="1">
        <div class="modal-body p-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Medicine Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Paracetamol" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Brand / Trade Name</label>
                    <input type="text" name="brand_name" class="form-control" placeholder="e.g. Panadol Extra">
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Dosage Form / Formulation <span class="text-danger">*</span></label>
                    <select name="dosage_form" class="form-select" required>
                        <option value="Tablet" selected>Tablet (Vidonge)</option>
                        <option value="Capsule">Capsule (Kapsuli)</option>
                        <option value="Syrup">Syrup (Siroti / Maji)</option>
                        <option value="Suspension">Suspension</option>
                        <option value="Injection">Injection (Sindano)</option>
                        <option value="Cream / Ointment">Cream / Ointment (Mafuta)</option>
                        <option value="Eye / Ear Drops">Eye / Ear Drops (Matone)</option>
                        <option value="Inhaler">Inhaler</option>
                        <option value="Suppository">Suppository</option>
                        <option value="Powder / Sachet">Powder / Sachet</option>
                        <option value="IV Infusion">IV Infusion / Drip</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Dosage / Strength</label>
                    <input type="text" name="strength" class="form-control" placeholder="e.g. 500mg, 125mg/5ml, 100ml">
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">TMDA Category <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Sub-Category</label>
                    <select name="sub_category_id" class="form-select">
                        <option value="">-- None --</option>
                        <?php foreach($sub_categories as $sc): ?>
                            <option value="<?= $sc['id'] ?>"><?= htmlspecialchars($sc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Unit Selling Price (TZS) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control" placeholder="e.g. 500" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Initial Stock Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" class="form-control" placeholder="e.g. 100" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Expiry Date <span class="text-danger">*</span></label>
                    <input type="date" name="expiry_date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Barcode / EAN</label>
                    <input type="text" name="barcode" class="form-control" placeholder="Scan barcode...">
                </div>
                <div class="col-md-12">
                    <label class="fw-semibold text-secondary small mb-1">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">-- Choose Supplier --</option>
                        <?php foreach($suppliers as $s): ?>
                            <option value="<?= is_array($s) ? $s['id'] : $s->id ?>"><?= htmlspecialchars(is_array($s) ? $s['name'] : $s->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer py-2 px-3 border-top bg-light">
            <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle me-1"></i> Save Medicine</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 2: Edit Medicine Details -->
<div class="modal fade" id="editDrugModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white py-2 px-3">
        <h6 class="modal-title mb-0"><i class="bi bi-pencil me-2"></i> Edit Medicine Details</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('inventory.update') }}">
        @csrf
        <input type="hidden" name="edit_drug" value="1">
        <input type="hidden" id="editDrugId" name="drug_id">
        <div class="modal-body p-3">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Medicine Name <span class="text-danger">*</span></label>
                    <input type="text" id="editDrugName" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Brand / Trade Name</label>
                    <input type="text" id="editDrugBrand" name="brand_name" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Dosage Form / Formulation <span class="text-danger">*</span></label>
                    <select id="editDrugDosageForm" name="dosage_form" class="form-select" required>
                        <option value="Tablet">Tablet (Vidonge)</option>
                        <option value="Capsule">Capsule (Kapsuli)</option>
                        <option value="Syrup">Syrup (Siroti / Maji)</option>
                        <option value="Suspension">Suspension</option>
                        <option value="Injection">Injection (Sindano)</option>
                        <option value="Cream / Ointment">Cream / Ointment (Mafuta)</option>
                        <option value="Eye / Ear Drops">Eye / Ear Drops (Matone)</option>
                        <option value="Inhaler">Inhaler</option>
                        <option value="Suppository">Suppository</option>
                        <option value="Powder / Sachet">Powder / Sachet</option>
                        <option value="IV Infusion">IV Infusion / Drip</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Dosage / Strength</label>
                    <input type="text" id="editDrugStrength" name="strength" class="form-control" placeholder="e.g. 500mg, 125mg/5ml">
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">TMDA Category <span class="text-danger">*</span></label>
                    <select id="editDrugCat" name="category_id" class="form-select" required>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Sub-Category</label>
                    <select id="editDrugSubCat" name="sub_category_id" class="form-select">
                        <option value="">-- None --</option>
                        <?php foreach($sub_categories as $sc): ?>
                            <option value="<?= $sc['id'] ?>"><?= htmlspecialchars($sc['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Unit Selling Price (TZS) <span class="text-danger">*</span></label>
                    <input type="number" id="editDrugPrice" name="price" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-secondary small mb-1">Barcode / EAN</label>
                    <input type="text" id="editDrugBarcode" name="barcode" class="form-control">
                </div>
            </div>
        </div>
        <div class="modal-footer py-2 px-3 border-top bg-light">
            <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle me-1"></i> Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 3: Add Batch Stock -->
<div class="modal fade" id="addStockModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white py-2 px-3">
        <h6 class="modal-title mb-0"><i class="bi bi-plus-circle me-2"></i> Add Stock / Reorder Batch</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('inventory.add_stock') }}">
        @csrf
        <input type="hidden" name="add_stock" value="1">
        <input type="hidden" id="stockDrugId" name="drug_id">
        <div class="modal-body p-3">
            <div class="mb-3">
                <label class="fw-semibold text-secondary small mb-1">Medicine Target</label>
                <input type="text" id="stockDrugName" class="form-control bg-light" readonly>
            </div>
            <div class="mb-3">
                <label class="fw-semibold text-secondary small mb-1">Quantity Added <span class="text-danger">*</span></label>
                <input type="number" name="quantity" class="form-control" placeholder="e.g. 50" required>
            </div>
            <div class="mb-3">
                <label class="fw-semibold text-secondary small mb-1">Expiry Date <span class="text-danger">*</span></label>
                <input type="date" name="expiry_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="fw-semibold text-secondary small mb-1">Batch Number (Optional)</label>
                <input type="text" name="batch_number" class="form-control" placeholder="Auto-generated if left blank">
            </div>
            <div class="mb-3">
                <label class="fw-semibold text-secondary small mb-1">Supplier (Optional)</label>
                <select name="supplier_id" class="form-select">
                    <option value="">-- Select Supplier --</option>
                    <?php foreach($suppliers as $s): ?>
                        <option value="<?= is_array($s) ? $s['id'] : $s->id ?>"><?= htmlspecialchars(is_array($s) ? $s['name'] : $s->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="modal-footer py-2 px-3 border-top bg-light">
            <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success px-4 text-white"><i class="bi bi-check-circle me-1"></i> Add Batch Stock</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function switchInventoryTab(tab, element) {
    document.querySelectorAll('#inventoryTabs .nav-link').forEach(btn => btn.classList.remove('active'));
    element.classList.add('active');
    
    document.querySelectorAll('.tab-pane-content').forEach(p => p.classList.add('d-none'));
    if(tab === 'active') document.getElementById('tabActive').classList.remove('d-none');
    else if(tab === 'out_of_stock') document.getElementById('tabOutOfStock').classList.remove('d-none');
    else if(tab === 'expired') document.getElementById('tabExpired').classList.remove('d-none');
}

function filterInventoryTable() {
    let input = document.getElementById("inventorySearch").value.toLowerCase();
    let rows = document.querySelectorAll(".tab-pane-content:not(.d-none) table tbody tr");
    rows.forEach(r => {
        let text = r.innerText.toLowerCase();
        r.style.display = text.includes(input) ? "" : "none";
    });
}

function openEditDrugModal(drug) {
    document.getElementById('editDrugId').value = drug.id;
    document.getElementById('editDrugName').value = drug.name || '';
    document.getElementById('editDrugBrand').value = drug.brand_name || '';
    document.getElementById('editDrugDosageForm').value = drug.dosage_form || 'Tablet';
    document.getElementById('editDrugCat').value = drug.category_id || '';
    document.getElementById('editDrugSubCat').value = drug.sub_category_id || '';
    document.getElementById('editDrugPrice').value = drug.price || '';
    document.getElementById('editDrugStrength').value = drug.strength || '';
    document.getElementById('editDrugBarcode').value = drug.barcode || '';
    new bootstrap.Modal(document.getElementById('editDrugModal')).show();
}

function openAddStockModal(drugId, drugName) {
    document.getElementById('stockDrugId').value = drugId;
    document.getElementById('stockDrugName').value = drugName;
    new bootstrap.Modal(document.getElementById('addStockModal')).show();
}
</script>
@endsection
