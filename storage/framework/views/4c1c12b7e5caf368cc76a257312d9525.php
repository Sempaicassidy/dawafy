<?php $__env->startSection('content'); ?>
<!-- Traditional POS View -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold text-dark mb-0">Point of Sale & Dispensing</h4>
        <small class="text-muted">Prescription checkout, medicine catalogue, and counter register</small>
    </div>
    <div class="d-flex gap-2 align-items-center mt-2 mt-md-0">
        <span class="badge bg-light text-secondary border"><span class="status-dot status-dot-success me-1"></span> Terminal Active</span>
        <span class="badge bg-light text-secondary border">Shift Open</span>
        <div class="d-none d-lg-flex gap-1 ms-2">
            <span class="kbd-shortcut" title="Press F2 to search medicines">F2 Search</span>
            <span class="kbd-shortcut" title="Press F4 to pay with Cash">F4 Pay Cash</span>
            <span class="kbd-shortcut" title="Press Esc to clear search">Esc</span>
        </div>
    </div>
</div>

<!-- Customer Phone Lookup Bar -->
<div class="card mb-3">
    <div class="card-body p-2">
        <div class="row align-items-center g-2">
            <div class="col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-secondary"><i class="bi bi-telephone"></i></span>
                    <input type="text" id="custPhone" class="form-control" placeholder="Search Customer Phone (e.g. 0712345678)..." onkeyup="checkCustomerPhone()">
                </div>
            </div>
            <div class="col-md-6">
                <div id="custDetails" class="d-none badge bg-success-subtle text-success border border-success p-2 text-center w-100">
                    <i class="bi bi-person-check me-1"></i> Customer: <strong id="custNameDisplay"></strong>
                    <input type="hidden" id="selectedCustomerId">
                </div>
                <div id="custRegister" class="d-none d-flex gap-2">
                    <input type="text" id="newCustName" class="form-control form-control-sm" placeholder="New Customer Name">
                    <button class="btn btn-sm btn-primary text-nowrap" onclick="registerQuickCustomer()"><i class="bi bi-plus me-1"></i> Save Customer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main POS Grid -->
<div class="row g-3">
    <!-- Left Column: Sub-Category Chips & Drug Catalogue -->
    <div class="col-md-7">
        <!-- Drug Search & Results Card -->
        <div class="card">
            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="mb-0 fw-semibold text-dark">Medicine Catalogue</h6>
                    <div id="searchValidatorBadge">
                        <span class="badge bg-light text-secondary border fw-normal">Showing Active Stock</span>
                    </div>
                </div>
                <span class="badge bg-secondary" id="itemCountDisplay">Loading...</span>
            </div>
            <div class="card-body p-3">
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text bg-light text-secondary"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Type medicine name, brand, barcode, or category... (or press F2)" onkeyup="searchDrugs()">
                    <button class="btn btn-outline-secondary" type="button" onclick="clearSearchInput()">Clear</button>
                </div>
                
                <div id="searchResults" style="max-height: 520px; overflow-y: auto; overflow-x: hidden;" class="pe-1">
                    <div id="drugsTbody" class="row row-cols-1 row-cols-lg-2 g-2">
                        <div class="col-12 text-center py-4 text-muted">Loading medicines...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Grand Total Banner, Cart Items, & Payment Checkout -->
    <div class="col-md-5">
        <!-- Grand Total Panel (Modern Executive POS Display) -->
        <div class="pos-grand-total-panel mb-3 animate-fade-in-up">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="pos-total-label"><i class="bi bi-receipt me-1"></i> Grand Total Due</div>
                    <div class="pos-total-amount" id="cartTotalDisplay">TZS 0</div>
                </div>
                <div class="text-end">
                    <span class="pos-cart-badge" id="cartCountBadge"><i class="bi bi-basket3 me-1"></i> 0 Items</span>
                </div>
            </div>
        </div>

        <!-- Cart & Checkout Card -->
        <div class="card animate-fade-in-up delay-1">
            <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center py-2 px-3">
                <h6 class="mb-0 fw-semibold text-dark"><i class="bi bi-bag-check me-1"></i> Sales Cart</h6>
                <button class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;" onclick="clearCart()"><i class="bi bi-trash me-1"></i> Clear</button>
            </div>
            <div class="card-body p-3">
                <div id="cartEmptyState" class="text-center py-4 text-muted">
                    <i class="bi bi-cart-x fs-2 d-block mb-1 text-secondary opacity-50"></i>
                    Cart is empty. Click <strong>"+ Add"</strong> on items left.
                </div>

                <div id="cartActiveState" class="d-none">
                    <div id="cartList" style="max-height: 280px; overflow-y: auto;"></div>

                    <!-- Delivery Checkbox -->
                    <div class="form-check my-2 p-2 bg-light border rounded">
                        <input class="form-check-input ms-1 me-2" type="checkbox" id="isDelivery" onchange="toggleDeliveryInputs()">
                        <label class="form-check-label fw-semibold text-secondary" for="isDelivery">Delivery Order</label>
                    </div>
                    <div id="deliveryInputs" class="d-none mb-3 p-2 bg-light border rounded">
                        <input type="text" id="delAddress" class="form-control form-control-sm mb-2" placeholder="Delivery Address">
                        <input type="text" id="delPhone" class="form-control form-control-sm" placeholder="Recipient Phone">
                    </div>

                    <!-- Payment Action Grid (Vibrant Executive Action Buttons) -->
                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <button class="btn btn-payment-cash w-100 py-2 fw-semibold" onclick="initiateCheckout('Cash')">
                                <i class="bi bi-cash-stack me-1 fs-5 align-middle"></i> Cash (F4)
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-payment-mobile w-100 py-2 fw-semibold" onclick="initiateCheckout('Mobile Money')">
                                <i class="bi bi-phone me-1 fs-5 align-middle"></i> M-Pesa / Tigo
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-payment-wallet w-100 py-2 fw-semibold" onclick="initiateCheckout('Wallet')">
                                <i class="bi bi-wallet2 me-1 fs-5 align-middle"></i> Customer Wallet
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-payment-insurance w-100 py-2 fw-semibold" onclick="initiateCheckout('Insurance')">
                                <i class="bi bi-shield-plus me-1 fs-5 align-middle"></i> Insurance (NHIF)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Insurance Details -->
<div class="modal fade" id="insuranceModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-dark text-white py-2 px-3">
        <h6 class="modal-title mb-0"><i class="bi bi-shield-check me-2"></i> Health Insurance Dispensation</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <div class="mb-3">
            <label class="fw-semibold text-secondary small mb-1">Insurance Provider</label>
            <select id="insProviderId" class="form-select" required>
                <option value="">-- Select Insurance Provider --</option>
                <?php foreach($insurance_providers as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="fw-semibold text-secondary small mb-1">Insurance Card / Member No.</label>
            <input type="text" id="insMemberNumber" class="form-control" placeholder="e.g. NHIF-12345678" required>
        </div>
        <div class="mb-3">
            <label class="fw-semibold text-secondary small mb-1">Diagnosis (ICD-10 Code / Description)</label>
            <input type="text" id="insDiagnosis" class="form-control" placeholder="e.g. Malaria (B50.9)">
        </div>
        <div class="mb-3">
            <label class="fw-semibold text-secondary small mb-1">Prescribing Doctor</label>
            <input type="text" id="insPrescriber" class="form-control" placeholder="e.g. Dr. John Doe">
        </div>
      </div>
      <div class="modal-footer py-2 px-3 border-top">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="confirmInsuranceCheckout()">Confirm Insurance Payment</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Receipt Print -->
<div class="modal fade" id="receiptModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-body text-center p-4">
        <div class="receipt-success-icon-wrapper">
            <i class="bi bi-check-circle-fill receipt-success-icon"></i>
        </div>
        <h5 class="fw-bold text-success mb-3">Sale Completed Successfully!</h5>
        <div class="printable-receipt-card p-3 rounded mb-3 text-start font-monospace small" id="printableReceiptArea" style="max-width: 290px; margin: 0 auto;">
            <!-- TRA-Style Receipt Header -->
            <div class="text-center fw-bold mb-2">
                <div class="fs-5"><?= htmlspecialchars($SYS_SETTINGS['company_name'] ?? 'PHARMACY NAME') ?></div>
                <small class="text-muted"><?= htmlspecialchars($SYS_SETTINGS['address'] ?? 'Address') ?></small><br>
                <small class="text-muted">Phone: <?= htmlspecialchars($SYS_SETTINGS['phone'] ?? 'Phone') ?></small>
            </div>
            <div class="text-center border-top border-bottom py-1 mb-2">
                <strong>TAX INVOICE / RECEIPT</strong>
            </div>

            <!-- TRA Info -->
            <div class="mb-2">
                <small>TIN: <?= htmlspecialchars($SYS_SETTINGS['tin'] ?? '101-001-001') ?></small><br>
                <small>VAT Reg: <?= htmlspecialchars($SYS_SETTINGS['vat_reg'] ?? 'VAT-001') ?></small>
            </div>

            <!-- Transaction Details -->
            <div class="mb-2">
                <small>Invoice No:</small> <strong id="recTxnId"></strong><br>
                <small>Date:</small> <strong id="recDate"></strong><br>
                <small>Time:</small> <strong id="recTime"></strong>
            </div>

            <!-- Customer Info -->
            <div class="mb-2" id="recCustomerSection" style="display: none;">
                <small>Customer:</small> <strong id="recCustomer"></strong>
            </div>

            <!-- Items Header -->
            <div class="border-top border-bottom py-1 mb-1 fw-bold">
                <div class="d-flex justify-content-between">
                    <small>ITEM</small>
                    <small>QTY</small>
                    <small>PRICE</small>
                    <small>TOTAL</small>
                </div>
            </div>

            <!-- Items List -->
            <div id="recItems" class="mb-2"></div>

            <!-- Totals -->
            <div class="border-top pt-1">
                <div class="d-flex justify-content-between mb-1">
                    <small>Subtotal:</small>
                    <small id="recSubtotal">TZS 0</small>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <small>VAT (18%):</small>
                    <small id="recVat">TZS 0</small>
                </div>
                <div class="d-flex justify-content-between fw-bold border-top pt-1">
                    <small>TOTAL:</small>
                    <small id="recTotal" class="text-dark">TZS 0</small>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="border-top pt-1 mt-2">
                <div class="d-flex justify-content-between">
                    <small>Payment:</small>
                    <small id="recMethod"></small>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-3 border-top pt-2">
                <small class="text-muted">Thank you for being our customer <?php echo e($SYS_SETTINGS['company_name'] ?? 'Dawafy Pharmacy'); ?></small><br>
                <small class="text-muted">Powered by Haula Technologies</small>
            </div>
        </div>
        <button class="btn btn-primary w-100 mb-2" onclick="printReceipt()"><i class="bi bi-printer me-1"></i> Print Receipt</button>
        <button class="btn btn-secondary w-100" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<style>
.drug-item-card {
    transition: border-color 0.15s ease, background-color 0.15s ease;
    border: 1px solid #e2e8f0 !important;
}
.drug-item-card:hover {
    border-color: #94a3b8 !important;
    background-color: #f8fafc;
}
.cart-item-card {
    padding: 0.65rem;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    background-color: #ffffff;
    margin-bottom: 0.5rem;
}
.cart-item-card:hover {
    background-color: #f8fafc;
}
@media print {
    body * {
        visibility: hidden;
    }
    #receiptModal, #receiptModal * {
        visibility: visible;
    }
    #receiptModal {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .modal-backdrop {
        display: none !important;
    }
    .btn {
        display: none !important;
    }
    .rounded-circle {
        display: none !important;
    }
    h5 {
        display: none !important;
    }
    #printableReceiptArea {
        border: none !important;
        padding: 10px !important;
        background: white !important;
        max-width: 80mm !important;
        font-size: 12px !important;
        font-family: 'Courier New', monospace !important;
    }
    #printableReceiptArea * {
        font-size: 12px !important;
        font-family: 'Courier New', monospace !important;
    }
    .small {
        font-size: 11px !important;
    }
}
</style>

<!-- POS JavaScript Engine -->
<script>
let cart = [];
let pendingCheckoutMethod = 'Cash';
let allLoadedDrugs = [];
let currentSubCatFilter = '';

let allLoadedDrugsMap = {};

function renderDrugsList(data) {
    let html = '';
    if(!data || data.length === 0) {
        html = `<div class="col-12 text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No medicines found in inventory.</div>`;
        document.getElementById('itemCountDisplay').innerText = '0 Medicines';
    } else {
        document.getElementById('itemCountDisplay').innerText = data.length + ' Medicines';
        data.forEach(d => {
            let drugId = d.drug_id || d.id;
            allLoadedDrugsMap[drugId] = d;
            let catName = d.category || 'POM';
            if(catName.toLowerCase().includes('prescription') || catName.toLowerCase().includes('pom')) catName = 'POM';
            else if(catName.toLowerCase().includes('controlled')) catName = 'Controlled';
            else if(catName.toLowerCase().includes('addo')) catName = 'ADDO';

            let subCatBadge = d.sub_category ? `<span class="badge bg-light text-secondary border me-1" style="font-size: 0.7rem;">${d.sub_category}</span>` : '';
            let stockQty = parseInt(d.stock) || 0;
            let isOutOfStock = stockQty <= 0;
            
            let stockStatusHtml = stockQty > 10 
                ? `<span class="status-indicator text-success"><span class="status-dot status-dot-success"></span> ${stockQty} in stock</span>`
                : (stockQty > 0 
                    ? `<span class="status-indicator text-warning"><span class="status-dot status-dot-warning"></span> ${stockQty} left</span>` 
                    : `<span class="status-indicator text-danger"><span class="status-dot status-dot-danger"></span> Out of stock</span>`);

            html += `
                <div class="col">
                    <div class="card p-2 mb-0 h-100 d-flex flex-column justify-content-between drug-item-card ${isOutOfStock ? 'opacity-75' : ''}">
                        <div>
                            <div class="mb-1">
                                <strong class="d-block text-dark text-truncate" style="font-size: 0.875rem;" title="${d.name}">${d.name}</strong>
                                <small class="text-muted d-block text-truncate" style="font-size: 0.75rem;">${d.brand_name || d.generic_name || 'Generic'}</small>
                            </div>
                            <div class="d-flex align-items-center flex-wrap gap-1 mb-2">
                                <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">${d.dosage_form || 'Tablet'}</span>
                                <span class="badge bg-light text-dark border" style="font-size: 0.7rem; font-weight: 500;">${catName}</span>
                                ${subCatBadge}
                            </div>
                        </div>
                        <div class="border-top pt-2 mt-1 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="mb-1">${stockStatusHtml}</div>
                                <span class="fw-bold text-dark tabular-nums" style="font-size: 0.925rem;">TZS ${parseFloat(d.price).toLocaleString()}</span>
                            </div>
                            <button class="btn btn-sm ${isOutOfStock ? 'btn-secondary disabled' : 'btn-primary'} py-1 px-2" style="font-size: 0.78rem;" onclick="addToCartById(${drugId})" ${isOutOfStock ? 'disabled' : ''}>
                                <i class="bi bi-plus me-1"></i> Add
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
    }
    document.getElementById('drugsTbody').innerHTML = html;
}

function addToCartById(drugId) {
    let drug = allLoadedDrugsMap[drugId] || allLoadedDrugs.find(i => (i.drug_id == drugId || i.id == drugId));
    if(drug) addToCart(drug);
}

function normStr(str) {
    return (str || '').toLowerCase().replace(/[^a-z0-9]/g, '');
}

function clearSearchInput() {
    document.getElementById('searchInput').value = '';
    searchDrugs();
}

function searchDrugs() {
    let rawQ = document.getElementById('searchInput').value.trim();
    let normQ = normStr(rawQ);
    let badge = document.getElementById('searchValidatorBadge');

    if(rawQ.length > 0) {
        if(badge) badge.innerHTML = `<span class="badge bg-light text-secondary border"><i class="bi bi-search me-1"></i> Filtered by: "${rawQ}"</span>`;
    } else {
        if(badge) badge.innerHTML = `<span class="badge bg-light text-secondary border fw-normal">Showing Active Stock</span>`;
    }

    if (allLoadedDrugs.length > 0) {
        applyLocalFilters(normQ);
    } else {
        fetch(`<?php echo e(route('pos.search')); ?>?q=`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(data => {
                if (!Array.isArray(data)) data = [];
                allLoadedDrugs = data;
                applyLocalFilters(normQ);
            })
            .catch(err => {
                console.error('POS searchDrugs error:', err);
                document.getElementById('drugsTbody').innerHTML = `<div class="col-12 text-center py-4 text-muted"><i class="bi bi-info-circle me-1"></i> No medicines available.</div>`;
                document.getElementById('itemCountDisplay').innerText = '0 Medicines';
            });
    }
}

function applyLocalFilters(normQ) {
    if(normQ === undefined) normQ = normStr(document.getElementById('searchInput').value);
    
    let filtered = allLoadedDrugs.filter(d => {
        if(!normQ) return true;
        let nameNorm = normStr(d.name);
        let brandNorm = normStr(d.brand_name);
        let barcodeNorm = normStr(d.barcode);
        let subCatNorm = normStr(d.sub_category);
        let catNorm = normStr(d.category);
        
        return nameNorm.includes(normQ) 
            || brandNorm.includes(normQ) 
            || barcodeNorm.includes(normQ) 
            || subCatNorm.includes(normQ) 
            || catNorm.includes(normQ);
    });
    renderDrugsList(filtered);
}

function addToCart(drug) {
    let drugId = drug.drug_id || drug.id;
    let existing = cart.find(i => (i.drug_id == drugId || i.id == drugId));
    if(existing) {
        existing.qty += 1;
        existing.is_new = true;
    } else {
        cart.push({ ...drug, drug_id: drugId, id: drugId, qty: 1, dose_qty: '', dose_times: '', dose_days: '', is_new: true });
    }
    if(window.notifyAddedSuccess) {
        window.notifyAddedSuccess(drug.name);
    }
    renderCart();

    // Trigger pulse bump animation on grand total & badge
    let totalEl = document.getElementById('cartTotalDisplay');
    if(totalEl) {
        totalEl.classList.remove('animate-bump');
        void totalEl.offsetWidth; // trigger reflow
        totalEl.classList.add('animate-bump');
    }
    let badgeEl = document.getElementById('cartCountBadge');
    if(badgeEl) {
        badgeEl.classList.remove('animate-bump');
        void badgeEl.offsetWidth;
        badgeEl.classList.add('animate-bump');
    }
}

function updateQty(index, change) {
    cart[index].qty += change;
    if(cart[index].qty <= 0) cart.splice(index, 1);
    renderCart();
}

function updateDose(index, field, val) {
    cart[index][field] = val;
    calculateDoseQty(index);
}

function calculateDoseQty(index) {
    let item = cart[index];
    let doseQty = parseFloat(item.dose_qty) || 0;
    let doseTimes = parseFloat(item.dose_times) || 0;
    let doseDays = parseFloat(item.dose_days) || 0;

    // Calculate total tablets based on dosage
    let totalTablets = doseQty * doseTimes * doseDays;

    // Update quantity to calculated tablets if dosage is filled
    if (totalTablets > 0) {
        item.qty = Math.round(totalTablets);
        // Show notification that calculation was done
        if(window.notifyAddedSuccess) {
            window.notifyAddedSuccess(`Calculated: ${item.qty} tablets based on dosage`);
        }
    }
    renderCart();
}

function calculateTotalTablets(item) {
    let doseQty = parseFloat(item.dose_qty) || 0;
    let doseTimes = parseFloat(item.dose_times) || 0;
    let doseDays = parseFloat(item.dose_days) || 0;
    let total = doseQty * doseTimes * doseDays;
    return total > 0 ? total : item.qty;
}

function clearCart() {
    cart = [];
    renderCart();
}

function renderCart() {
    if(cart.length === 0) {
        document.getElementById('cartEmptyState').classList.remove('d-none');
        document.getElementById('cartActiveState').classList.add('d-none');
        document.getElementById('cartCountBadge').innerHTML = '<i class="bi bi-basket3 me-1"></i> 0 Items';
        document.getElementById('cartTotalDisplay').innerText = 'TZS 0';
        return;
    }
    
    document.getElementById('cartEmptyState').classList.add('d-none');
    document.getElementById('cartActiveState').classList.remove('d-none');
    document.getElementById('cartCountBadge').innerHTML = `<i class="bi bi-basket3 me-1"></i> ${cart.length} ${cart.length === 1 ? 'Item' : 'Items'}`;

    let html = '';
    let total = 0;
    cart.forEach((item, index) => {
        let subtotal = item.price * item.qty;
        total += subtotal;
        let isNewClass = item.is_new ? 'cart-item-new' : '';
        item.is_new = false; // consume
        html += `
            <div class="cart-item-card ${isNewClass}">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong class="text-dark" style="font-size: 0.875rem;">${item.name}</strong>
                    <button class="btn btn-sm text-danger border-0 p-0" title="Remove Item" onclick="updateQty(${index}, -${item.qty})"><i class="bi bi-trash"></i></button>
                </div>
                <div class="row g-1 mb-2">
                    <div class="col-4">
                        <label class="text-muted text-uppercase d-block" style="font-size: 0.65rem; font-weight: 600;">Dose Qty</label>
                        <input type="number" class="form-control form-control-sm text-center" placeholder="e.g. 2" value="${item.dose_qty || ''}" onchange="updateDose(${index}, 'dose_qty', this.value)" style="font-size: 0.78rem;">
                    </div>
                    <div class="col-4">
                        <label class="text-muted text-uppercase d-block" style="font-size: 0.65rem; font-weight: 600;">Times/Day</label>
                        <input type="number" class="form-control form-control-sm text-center" placeholder="e.g. 3" value="${item.dose_times || ''}" onchange="updateDose(${index}, 'dose_times', this.value)" style="font-size: 0.78rem;">
                    </div>
                    <div class="col-4">
                        <label class="text-muted text-uppercase d-block" style="font-size: 0.65rem; font-weight: 600;">Days</label>
                        <input type="number" class="form-control form-control-sm text-center" placeholder="e.g. 5" value="${item.dose_days || ''}" onchange="updateDose(${index}, 'dose_days', this.value)" style="font-size: 0.78rem;">
                    </div>
                </div>
                <div class="mb-2">
                    <small class="text-secondary fw-semibold" style="font-size: 0.78rem;">
                        <i class="bi bi-calculator me-1"></i>
                        Calculated Tablets: <span id="calcTablets-${index}" class="text-dark fw-bold tabular-nums">${calculateTotalTablets(item)}</span>
                    </small>
                </div>
                <div class="d-flex justify-content-between align-items-center border-top pt-2">
                    <small class="text-muted tabular-nums">TZS ${parseFloat(item.price).toLocaleString()} &times; ${item.qty}</small>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;" onclick="updateQty(${index}, -1)">-</button>
                        <span class="fw-bold fs-6 tabular-nums">${item.qty}</span>
                        <button class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.75rem;" onclick="updateQty(${index}, 1)">+</button>
                        <strong class="text-dark tabular-nums ms-2" style="font-size: 0.9rem;">TZS ${subtotal.toLocaleString()}</strong>
                    </div>
                </div>
            </div>
        `;
    });

    document.getElementById('cartList').innerHTML = html;
    document.getElementById('cartTotalDisplay').innerText = 'TZS ' + total.toLocaleString();
}

function initiateCheckout(method) {
    if(cart.length === 0) return alert('Cart is empty!');
    pendingCheckoutMethod = method;
    if(method === 'Insurance') {
        new bootstrap.Modal(document.getElementById('insuranceModal')).show();
        return;
    }
    finalizeCheckout();
}

function confirmInsuranceCheckout() {
    let providerId = document.getElementById('insProviderId').value;
    let memberNumber = document.getElementById('insMemberNumber').value;
    let diagnosis = document.getElementById('insDiagnosis').value;
    let prescriber = document.getElementById('insPrescriber').value;

    if(!providerId || !memberNumber) {
        alert('Please select insurance and enter card number');
        return;
    }

    // Store insurance details temporarily
    window.pendingInsurance = {
        provider_id: providerId,
        member_number: memberNumber,
        diagnosis: diagnosis,
        prescriber: prescriber
    };

    bootstrap.Modal.getInstance(document.getElementById('insuranceModal')).hide();
    finalizeCheckout();
}

function finalizeCheckout() {
    console.log('Starting checkout for method:', pendingCheckoutMethod);
    console.log('Cart items:', cart);

    let total = cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
    let formData = new FormData();
    formData.append('action', 'checkout');
    formData.append('cart', JSON.stringify(cart));
    formData.append('payment_method', pendingCheckoutMethod);

    // Add customer ID if exists
    let customerId = document.getElementById('selectedCustomerId').value;
    let customerName = document.getElementById('custNameDisplay').innerText;
    if(customerId) {
        formData.append('customer_id', customerId);
    }

    // Add delivery info if checked
    if(document.getElementById('isDelivery').checked) {
        formData.append('is_delivery', '1');
        formData.append('delivery_address', document.getElementById('delAddress').value);
        formData.append('delivery_phone', document.getElementById('delPhone').value);
    }

    // Add insurance details if payment method is Insurance
    if(pendingCheckoutMethod === 'Insurance' && window.pendingInsurance) {
        formData.append('insurance_provider_id', window.pendingInsurance.provider_id);
        formData.append('insurance_member_number', window.pendingInsurance.member_number);
        formData.append('insurance_diagnosis', window.pendingInsurance.diagnosis || '');
        formData.append('insurance_prescriber', window.pendingInsurance.prescriber || '');
        window.pendingInsurance = null; // Clear after use
    }

    console.log('Sending checkout request...');
    fetch(`<?php echo e(route('pos.transaction')); ?>`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
        .then(res => {
            console.log('Response status:', res.status);
            return res.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if(data.success) {
                if(window.notifySalesSuccess) {
                    window.notifySalesSuccess(data.transaction_id || 'REC-' + Math.floor(Math.random()*90000), total);
                    setTimeout(function() { window.notifyPaidSuccess(total, pendingCheckoutMethod); }, 500);
                }

                // Calculate VAT (18%)
                let subtotal = total / 1.18;
                let vat = total - subtotal;

                // Populate receipt with full details
                document.getElementById('recTxnId').innerText = data.transaction_id || 'REC-' + Math.floor(Math.random()*90000);
                document.getElementById('recDate').innerText = new Date().toLocaleDateString();
                document.getElementById('recTime').innerText = new Date().toLocaleTimeString();
                document.getElementById('recSubtotal').innerText = 'TZS ' + Math.round(subtotal).toLocaleString();
                document.getElementById('recVat').innerText = 'TZS ' + Math.round(vat).toLocaleString();
                document.getElementById('recTotal').innerText = 'TZS ' + total.toLocaleString();
                document.getElementById('recMethod').innerText = pendingCheckoutMethod;

                // Show customer info if exists
                if(customerName) {
                    document.getElementById('recCustomer').innerText = customerName;
                    document.getElementById('recCustomerSection').style.display = 'block';
                } else {
                    document.getElementById('recCustomerSection').style.display = 'none';
                }

                // Populate items list in TRA format
                let itemsHtml = '';
                cart.forEach(item => {
                    let subtotal = item.price * item.qty;
                    itemsHtml += `
                        <div class="d-flex justify-content-between mb-1">
                            <small style="width: 40%; text-align: left;">${item.name.substring(0, 15)}</small>
                            <small style="width: 15%; text-align: center;">${item.qty}</small>
                            <small style="width: 20%; text-align: right;">${item.price.toLocaleString()}</small>
                            <small style="width: 25%; text-align: right;">${subtotal.toLocaleString()}</small>
                        </div>
                    `;
                });
                document.getElementById('recItems').innerHTML = itemsHtml;

                cart = [];
                renderCart();
                new bootstrap.Modal(document.getElementById('receiptModal')).show();
            } else {
                alert('Checkout failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Network error: ' + error.message);
            console.error('Checkout error:', error);
        });
}

function printReceipt() {
    window.print();
}

function checkCustomerPhone() {
    let p = document.getElementById('custPhone').value;
    if(p.length >= 10) {
        fetch(`<?php echo e(url('/pos/customer-lookup')); ?>?phone=${encodeURIComponent(p)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('custNameDisplay').innerText = data.customer.name;
                    document.getElementById('selectedCustomerId').value = data.customer.id;
                    document.getElementById('custDetails').classList.remove('d-none');
                    document.getElementById('custRegister').classList.add('d-none');
                } else {
                    document.getElementById('custDetails').classList.add('d-none');
                    document.getElementById('custRegister').classList.remove('d-none');
                }
            })
            .catch(err => console.error('Customer lookup error:', err));
    }
}

function registerQuickCustomer() {
    let name = document.getElementById('newCustName').value;
    let phone = document.getElementById('custPhone').value;
    if(!name) return alert('Please enter customer name');
    let fd = new FormData();
    fd.append('name', name);
    fd.append('phone', phone);
    fetch(`<?php echo e(url('/pos/customer-register')); ?>`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: fd
    })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById('custNameDisplay').innerText = data.name;
                document.getElementById('selectedCustomerId').value = data.id;
                document.getElementById('custDetails').classList.remove('d-none');
                document.getElementById('custRegister').classList.add('d-none');
                if(window.notifyAddedSuccess) window.notifyAddedSuccess('New Customer Saved');
            }
        })
        .catch(err => console.error('Register customer error:', err));
}

function toggleDeliveryInputs() {
    let chk = document.getElementById('isDelivery').checked;
    if(chk) document.getElementById('deliveryInputs').classList.remove('d-none');
    else document.getElementById('deliveryInputs').classList.add('d-none');
}

// Enterprise Keyboard Shortcuts (F2: Search, F4: Pay Cash, Esc: Clear Search / Modal)
document.addEventListener('keydown', function(e) {
    if (e.key === 'F2') {
        e.preventDefault();
        let searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    } else if (e.key === 'F4') {
        e.preventDefault();
        if (cart && cart.length > 0) {
            initiateCheckout('Cash');
        }
    } else if (e.key === 'Escape') {
        let searchInput = document.getElementById('searchInput');
        if (document.activeElement === searchInput && searchInput.value) {
            clearSearchInput();
        }
    }
});

if(document.readyState === 'complete' || document.readyState === 'interactive') {
    searchDrugs();
} else {
    document.addEventListener('DOMContentLoaded', searchDrugs);
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\pms\resources\views/pos.blade.php ENDPATH**/ ?>