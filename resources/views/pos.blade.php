@extends('layouts.app')

@section('content')
<!-- Traditional POS View -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold text-dark mb-0">Point of Sale & Dispensing</h4>
        <small class="text-muted">Prescription checkout, medicine catalogue, and counter register</small>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center mt-2 mt-md-0">
        @if($has_cashier)
            <button class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold shadow-sm" onclick="openReadyToDispenseModal()" title="Orders paid by cashier ready for physical release">
                <i class="bi bi-box2-heart me-1 text-success"></i> Ready to Dispense <span id="readyToDispenseCount" class="badge bg-success rounded-pill ms-1">0</span>
            </button>
            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-sm" onclick="openPendingPaymentsModal()" title="Prescriptions awaiting payment collection">
                <i class="bi bi-wallet2 me-1 text-primary"></i> Cashier Queue <span id="cashierQueueCount" class="badge bg-primary rounded-pill ms-1">0</span>
            </button>
        @endif
        <div class="d-none d-lg-flex gap-1 ms-2">
            <span class="kbd-shortcut" title="Press F2 to search medicines">F2 Search</span>
            @if($has_cashier && $is_pharmacist)
                <span class="kbd-shortcut" title="Press F4 to send to cashier">F4 Send Cashier</span>
            @else
                <span class="kbd-shortcut" title="Press F4 to checkout cash">F4 Pay Cash</span>
            @endif
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

                    <!-- Workflow Action: Send to Cashier (When Cashier is available on premise) -->
                    @if($has_cashier)
                        <div class="mb-2">
                            <button type="button" class="btn btn-warning w-100 py-2 fw-bold text-dark shadow-sm border-0 d-flex align-items-center justify-content-center gap-2" onclick="sendOrderToCashier()">
                                <i class="bi bi-send-check-fill fs-5"></i>
                                <span>Send to Cashier (Prescribe & Bill)</span>
                            </button>
                            @if($is_pharmacist)
                                <small class="text-muted d-block text-center mt-1" style="font-size: 0.72rem;">
                                    <i class="bi bi-info-circle text-primary me-1"></i> Cashier active on premise. Order will be billed at Cashier counter.
                                </small>
                            @endif
                        </div>
                    @endif

                    <!-- Payment Action Grid (Visible to Cashier/Manager, or to Pharmacist when tenant has NO Cashier) -->
                    @if(!$has_cashier || !$is_pharmacist)
                        @if($has_cashier)
                            <div class="text-center my-1">
                                <small class="text-muted text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">— Or Direct Checkout (Manager / Walk-in) —</small>
                            </div>
                        @else
                            <div class="text-center my-2">
                                <span class="badge bg-light text-secondary border fw-normal py-1 px-2" style="font-size: 0.72rem;">
                                    <i class="bi bi-person-check text-success me-1"></i> Single-Operator Mode (Direct Dispense & Payment)
                                </span>
                            </div>
                        @endif

                        @php
                            $activeMethods = \App\Models\PaymentMethod::getActiveMethods();
                            $hasMobile = $activeMethods->contains('name', 'Mobile Money');
                            $hasCard = $activeMethods->contains('name', 'Card');
                            $hasWallet = $activeMethods->contains('name', 'Wallet');
                            $hasInsurance = $activeMethods->contains('name', 'Insurance');
                        @endphp
                        <div class="row g-2 mt-1">
                            <div class="col-6">
                                <button class="btn btn-payment-cash w-100 py-2 fw-semibold" onclick="initiateCheckout('Cash')">
                                    <i class="bi bi-cash-stack me-1 fs-5 align-middle"></i> Cash (F4)
                                </button>
                            </div>
                            @if($hasMobile)
                            <div class="col-6">
                                <button class="btn btn-payment-mobile w-100 py-2 fw-semibold" onclick="initiateCheckout('Mobile Money')">
                                    <i class="bi bi-phone me-1 fs-5 align-middle"></i> M-Pesa / Tigo
                                </button>
                            </div>
                            @endif
                            @if($hasCard)
                            <div class="col-6">
                                <button class="btn btn-primary w-100 py-2 fw-semibold" onclick="initiateCheckout('Card')" style="background-color: #0284c7; border-color: #0284c7;">
                                    <i class="bi bi-credit-card me-1 fs-5 align-middle"></i> Bank Card / POS
                                </button>
                            </div>
                            @endif
                            @if($hasWallet)
                            <div class="col-6">
                                <button class="btn btn-payment-wallet w-100 py-2 fw-semibold" onclick="initiateCheckout('Wallet')">
                                    <i class="bi bi-wallet2 me-1 fs-5 align-middle"></i> Customer Wallet
                                </button>
                            </div>
                            @endif
                            @if($hasInsurance)
                            <div class="col-6">
                                <button class="btn btn-payment-insurance w-100 py-2 fw-semibold" onclick="initiateCheckout('Insurance')">
                                    <i class="bi bi-shield-plus me-1 fs-5 align-middle"></i> Insurance (NHIF)
                                </button>
                            </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Insurance Details with Real-time Card Verification -->
<div class="modal fade" id="insuranceModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-dark text-white py-3 px-4 rounded-top-4">
        <h6 class="modal-title mb-0 fw-bold"><i class="bi bi-shield-check me-2 text-primary"></i> Health Insurance Dispensation & Verification</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="mb-3">
            <label class="fw-semibold text-secondary small mb-1">Insurance Provider <span class="text-danger">*</span></label>
            <select id="insProviderId" class="form-select rounded-3" required>
                <option value="">-- Select Insurance Provider --</option>
                <?php foreach($insurance_providers as $p): ?>
                    <option value="<?= $p['id'] ?>" data-code="<?= $p['code'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= strtoupper($p['api_environment'] ?? 'SANDBOX') ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="fw-semibold text-secondary small mb-1">Insurance Card / Member No. <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="text" id="insMemberNumber" class="form-control rounded-start-3 font-monospace" placeholder="e.g. 01-88492014" required>
                <button class="btn btn-primary px-3 rounded-end-3" type="button" id="btnVerifyPosCard" onclick="verifyInsuranceCardInPos()">
                    <i class="bi bi-search me-1"></i> Verify Card
                </button>
            </div>
            <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;">Sample active cards: <code>01-88492014</code> (NHIF), <code>JUB-4491</code> (Jubilee), <code>STRAT-1029</code> (Strategis).</small>
        </div>

        <!-- Real-time Verification Badge Result -->
        <div id="insPosVerifyBadge" class="mb-3" style="display: none;">
            <div class="card p-3 rounded-4 shadow-sm" id="insPosBadgeCard">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="badge rounded-pill px-2 py-1" id="insPosStatusBadge">ACTIVE</span>
                    <span class="small font-monospace fw-bold" id="insPosAuthCodeBadge">AUTH: ---</span>
                </div>
                <div class="fw-bold text-dark fs-6" id="insPosMemberName">---</div>
                <small class="text-muted d-block" id="insPosScheme">Scheme: ---</small>
            </div>
        </div>

        <input type="hidden" id="insVerifiedMemberName">
        <input type="hidden" id="insVerifiedAuthCode">
        <input type="hidden" id="insVerifiedScheme">

        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="fw-semibold text-secondary small mb-1">Prescription / Form 2C No.</label>
                <input type="text" id="insReferralNumber" class="form-control form-control-sm rounded-3 font-monospace" placeholder="e.g. 2C-90142">
            </div>
            <div class="col-6">
                <label class="fw-semibold text-secondary small mb-1">Prescribing Doctor</label>
                <input type="text" id="insPrescriber" class="form-control form-control-sm rounded-3" placeholder="e.g. Dr. Mushi">
            </div>
        </div>

        <div class="mb-2">
            <label class="fw-semibold text-secondary small mb-1">Diagnosis (ICD-10 Code / Description)</label>
            <input type="text" id="insDiagnosis" class="form-control form-control-sm rounded-3" placeholder="e.g. Malaria (B50.9) / Hypertension">
        </div>
      </div>
      <div class="modal-footer py-2 px-4 border-top">
        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" id="btnConfirmInsPayment" onclick="confirmInsuranceCheckout()">
            <i class="bi bi-check2-circle me-1"></i> Confirm & Proceed
        </button>
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
                @if(!empty($SYS_SETTINGS['logo_path']))
                    <div class="mb-1 text-center">
                        <img src="{{ asset($SYS_SETTINGS['logo_path']) }}" alt="Logo" style="max-height: 44px; max-width: 140px; object-fit: contain;">
                    </div>
                @endif
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
                <small class="text-muted">Thank you for being our customer {{ $SYS_SETTINGS['company_name'] ?? 'Dawafy Pharmacy' }}</small><br>
                <small class="text-muted">Powered by Haula Technologies</small>
            </div>
        </div>
        <button class="btn btn-primary w-100 mb-2" onclick="printReceipt()"><i class="bi bi-printer me-1"></i> Print Receipt</button>
        <button class="btn btn-secondary w-100" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Pharmacist Stock Refill Request -->
<div class="modal fade" id="stockRefillModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-warning text-dark py-2 px-3">
        <h6 class="modal-title mb-0 fw-bold"><i class="bi bi-box-arrow-in-down me-2"></i> Request Stock Refill from Main Store</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <input type="hidden" id="refillDrugId">
        <div class="alert alert-light border d-flex align-items-center mb-3">
            <i class="bi bi-capsule fs-3 text-warning me-3"></i>
            <div>
                <strong id="refillDrugName" class="d-block text-dark fs-6"></strong>
                <small class="text-muted">Current Dispensary Stock: <strong id="refillCurrentStock" class="text-danger">0</strong> | Reorder Alert Level: <span id="refillReorderLevel" class="text-secondary">10</span></small>
            </div>
        </div>
        <div class="mb-3">
            <label class="fw-semibold text-secondary small mb-1">Requested Quantity (Boxes / Packs / Units) <span class="text-danger">*</span></label>
            <input type="number" id="refillQuantity" class="form-control" placeholder="e.g. 5" min="1" required>
            <small class="text-muted" style="font-size: 0.75rem;">This request will be routed directly to the Manager for review & issuance.</small>
        </div>
        <div class="mb-3">
            <label class="fw-semibold text-secondary small mb-1">Notes / Reason (Optional)</label>
            <textarea id="refillNotes" class="form-control" rows="2" placeholder="e.g. High demand this week / Emergency shelf depletion"></textarea>
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-warning fw-bold text-dark px-3" onclick="submitRefillRequest()">
                <i class="bi bi-send-fill me-1"></i> Submit Refill Request
            </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Cashier Pending Payments -->
<div class="modal fade" id="pendingPaymentsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white py-2 px-3">
        <h6 class="modal-title mb-0 fw-bold"><i class="bi bi-cash-coin me-2"></i> Cashier Queue — Pending Payments from Pharmacists</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <small class="text-muted">Pharmacists have prescribed these orders. Collect payment from patient and approve below.</small>
            <button class="btn btn-sm btn-outline-primary" onclick="loadPendingPayments()"><i class="bi bi-arrow-clockwise me-1"></i> Refresh</button>
        </div>
        <div id="pendingPaymentsList" style="max-height: 420px; overflow-y: auto;">
            <div class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Loading pending payments...</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal for Ready to Dispense (Pharmacist Final Handover) -->
<div class="modal fade" id="readyToDispenseModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-success text-white py-2 px-3">
        <h6 class="modal-title mb-0 fw-bold"><i class="bi bi-bag-check-fill me-2"></i> Paid Orders Ready for Dispensation</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <small class="text-muted">Payments are approved by Cashier. Click "Confirm Dispense" to officially deduct inventory and hand medicines to patient.</small>
            <button class="btn btn-sm btn-outline-success" onclick="loadReadyToDispense()"><i class="bi bi-arrow-clockwise me-1"></i> Refresh</button>
        </div>
        <div id="readyToDispenseList" style="max-height: 420px; overflow-y: auto;">
            <div class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Loading paid orders...</div>
        </div>
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
let enabledPaymentMethods = @json(\App\Models\PaymentMethod::getActiveNames());
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

            // TMDA Classification Short Form (POM, P, GSL, Controlled)
            let tmdaShort = d.tmda_short;
            if(!tmdaShort) {
                let cat = (d.category || '').toLowerCase();
                if(cat.includes('prescription') || cat.includes('pom')) tmdaShort = 'POM';
                else if(cat.includes('controlled')) tmdaShort = 'Controlled';
                else if(cat.includes('general') || cat.includes('gsl')) tmdaShort = 'GSL';
                else if(cat.includes('pharmacy') || cat === 'p') tmdaShort = 'P';
                else tmdaShort = 'POM';
            }

            let tmdaBadgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
            if(tmdaShort === 'Controlled') tmdaBadgeClass = 'bg-danger-subtle text-danger border border-danger-subtle fw-bold';
            else if(tmdaShort === 'GSL') tmdaBadgeClass = 'bg-success-subtle text-success border border-success-subtle';
            else if(tmdaShort === 'P') tmdaBadgeClass = 'bg-info-subtle text-info-emphasis border border-info-subtle';

            let subCatBadge = d.sub_category ? `<span class="badge bg-light text-secondary border me-1" style="font-size: 0.7rem;">${d.sub_category}</span>` : '';
            let stockQty = parseInt(d.stock) || 0;
            let reorderLevel = parseInt(d.reorder_level) || 10;
            
            // Stock Traffic Light Indicator: Red (0), Yellow (<= reorderLevel), Green (> reorderLevel)
            let stockLight = d.stock_light;
            if(!stockLight) {
                if(stockQty <= 0) stockLight = 'red';
                else if(stockQty <= reorderLevel) stockLight = 'yellow';
                else stockLight = 'green';
            }

            let cardBorderClass = 'border-start border-3 border-success';
            let stockStatusHtml = '';
            let showRefillBtn = false;

            if(stockLight === 'red') {
                cardBorderClass = 'border-start border-3 border-danger';
                stockStatusHtml = `<span class="badge bg-danger text-white py-1 px-2 d-inline-flex align-items-center gap-1" style="font-size:0.72rem;"><i class="bi bi-circle-fill text-white" style="font-size:0.45rem;"></i> 🔴 Out of Stock (0)</span>`;
                showRefillBtn = true;
            } else if(stockLight === 'yellow') {
                cardBorderClass = 'border-start border-3 border-warning';
                stockStatusHtml = `<span class="badge bg-warning text-dark py-1 px-2 d-inline-flex align-items-center gap-1" style="font-size:0.72rem;"><i class="bi bi-circle-fill text-dark" style="font-size:0.45rem;"></i> 🟡 Low Stock (${stockQty})</span>`;
                showRefillBtn = true;
            } else {
                cardBorderClass = 'border-start border-3 border-success';
                stockStatusHtml = `<span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2 d-inline-flex align-items-center gap-1" style="font-size:0.72rem;"><i class="bi bi-circle-fill text-success" style="font-size:0.45rem;"></i> 🟢 In Stock (${stockQty})</span>`;
            }

            let isOutOfStock = stockQty <= 0;
            let safeName = (d.name || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');

            html += `
                <div class="col">
                    <div class="card p-2 mb-0 h-100 d-flex flex-column justify-content-between drug-item-card ${cardBorderClass} ${isOutOfStock ? 'opacity-75' : ''}">
                        <div>
                            <div class="mb-1">
                                <strong class="d-block text-dark text-truncate" style="font-size: 0.875rem;" title="${d.name}">${d.name}</strong>
                                <small class="text-muted d-block text-truncate" style="font-size: 0.75rem;">${d.brand_name || d.generic_name || 'Generic'}</small>
                            </div>
                            <div class="d-flex align-items-center flex-wrap gap-1 mb-2">
                                <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">${d.dosage_form || 'Tablet'}</span>
                                <span class="badge ${tmdaBadgeClass}" style="font-size: 0.7rem;" title="TMDA Classification">${tmdaShort}</span>
                                ${subCatBadge}
                                ${d.batches_count && d.batches_count > 1 ? `<span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.68rem;" title="Batches managed automatically by FIFO across ${d.batches_count} batches"><i class="bi bi-layers-half me-1"></i>${d.batches_count} Batches (FIFO)</span>` : ''}
                            </div>
                        </div>
                        <div class="border-top pt-2 mt-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>${stockStatusHtml}</div>
                                <span class="fw-bold text-dark tabular-nums" style="font-size: 0.925rem;">TZS ${parseFloat(d.price).toLocaleString()}</span>
                            </div>
                            <div class="d-flex gap-1 mt-1">
                                <button class="btn btn-sm ${isOutOfStock ? 'btn-secondary disabled' : 'btn-primary'} flex-grow-1 py-1 px-2" style="font-size: 0.78rem;" onclick="addToCartById(${drugId})" ${isOutOfStock ? 'disabled' : ''}>
                                    <i class="bi bi-plus me-1"></i> Add
                                </button>
                                ${showRefillBtn ? `
                                    <button type="button" class="btn btn-sm btn-outline-warning text-dark py-1 px-2" title="Request Stock Refill from Main Store" onclick="openRefillModal(${drugId}, '${safeName}', ${stockQty}, ${reorderLevel})">
                                        <i class="bi bi-box-arrow-in-down"></i> Refill
                                    </button>
                                ` : ''}
                            </div>
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
        fetch(`{{ route('pos.search') }}?q=`, {
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
    let targetNormName = normStr(drug.name);
    let existing = cart.find(i => (i.drug_id == drugId || i.id == drugId) || (targetNormName && normStr(i.name) === targetNormName));
    if(existing) {
        existing.qty += 1;
        existing.is_new = true;
    } else {
        cart.push({
            ...drug,
            drug_id: drugId,
            id: drugId,
            matched_drug_ids: drug.matched_drug_ids || [drugId],
            qty: 1,
            dose_qty: '',
            dose_times: '',
            dose_days: '',
            is_new: true
        });
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

function verifyInsuranceCardInPos() {
    let providerId = document.getElementById('insProviderId').value;
    let memberNumber = document.getElementById('insMemberNumber').value.trim();
    let btn = document.getElementById('btnVerifyPosCard');
    let badgeArea = document.getElementById('insPosVerifyBadge');
    let badgeCard = document.getElementById('insPosBadgeCard');

    if(!providerId) {
        alert('Tafadhali chagua bima (Provider) kwanza!');
        document.getElementById('insProviderId').focus();
        return;
    }

    if(!memberNumber) {
        alert('Tafadhali ingiza nambari ya kadi ya bima!');
        document.getElementById('insMemberNumber').focus();
        return;
    }

    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Verifying...`;

    fetch(`{{ route('insurance.verify_card') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            provider_id: providerId,
            card_number: memberNumber
        })
    })
    .then(async r => {
        let text = await r.text();
        try {
            return JSON.parse(text);
        } catch(e) {
            throw new Error(text.substring(0, 120));
        }
    })
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-search me-1"></i> Verify Card`;
        badgeArea.style.display = 'block';

        if (res.is_valid) {
            badgeCard.className = 'card p-3 rounded-4 shadow-sm border border-success bg-success-subtle';
            document.getElementById('insPosStatusBadge').className = 'badge bg-success rounded-pill px-2 py-1';
            document.getElementById('insPosStatusBadge').innerText = 'ACTIVE (UTHIBITISHO UMEFAULU)';
            document.getElementById('insPosAuthCodeBadge').innerText = res.authorization_code || 'AUTH: APPROVED';
            document.getElementById('insPosMemberName').innerText = res.member_name || 'Verified Member';
            document.getElementById('insPosScheme').innerText = 'Scheme: ' + (res.scheme || 'NHIF Cover');

            document.getElementById('insVerifiedMemberName').value = res.member_name || '';
            document.getElementById('insVerifiedAuthCode').value = res.authorization_code || '';
            document.getElementById('insVerifiedScheme').value = res.scheme || '';

            // Also auto-suggest this member name to customer name display if customer not picked
            let custDisplay = document.getElementById('custNameDisplay');
            if (custDisplay && (!custDisplay.innerText || custDisplay.innerText.includes('Walk-in') || custDisplay.innerText.includes('---'))) {
                custDisplay.innerText = res.member_name + ' (NHIF)';
            }
        } else {
            badgeCard.className = 'card p-3 rounded-4 shadow-sm border border-danger bg-danger-subtle';
            document.getElementById('insPosStatusBadge').className = 'badge bg-danger rounded-pill px-2 py-1';
            document.getElementById('insPosStatusBadge').innerText = 'HAIRUHUSIWI / ' + (res.status || 'EXPIRED');
            document.getElementById('insPosAuthCodeBadge').innerText = 'NO AUTH CODE';
            document.getElementById('insPosMemberName').innerText = res.member_name || 'Unknown Member';
            document.getElementById('insPosScheme').innerText = res.message || 'Kadi hii haijathibitishwa na bima.';

            document.getElementById('insVerifiedMemberName').value = '';
            document.getElementById('insVerifiedAuthCode').value = '';
            document.getElementById('insVerifiedScheme').value = '';
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = `<i class="bi bi-search me-1"></i> Verify Card`;
        alert('Kosa wakati wa kuwasiliana na seva ya bima: ' + err.message);
    });
}

function confirmInsuranceCheckout() {
    let providerId = document.getElementById('insProviderId').value;
    let memberNumber = document.getElementById('insMemberNumber').value.trim();
    let diagnosis = document.getElementById('insDiagnosis').value;
    let prescriber = document.getElementById('insPrescriber').value;
    let referralNumber = document.getElementById('insReferralNumber').value;
    let memberName = document.getElementById('insVerifiedMemberName').value;
    let authCode = document.getElementById('insVerifiedAuthCode').value;
    let scheme = document.getElementById('insVerifiedScheme').value;

    if(!providerId || !memberNumber) {
        alert('Tafadhali chagua bima na uingize nambari ya kadi');
        return;
    }

    // Store insurance details temporarily
    window.pendingInsurance = {
        provider_id: providerId,
        member_number: memberNumber,
        member_name: memberName,
        authorization_code: authCode,
        scheme_name: scheme,
        referral_number: referralNumber,
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
        formData.append('member_number', window.pendingInsurance.member_number);
        formData.append('member_name', window.pendingInsurance.member_name || '');
        formData.append('authorization_code', window.pendingInsurance.authorization_code || '');
        formData.append('scheme_name', window.pendingInsurance.scheme_name || '');
        formData.append('referral_number', window.pendingInsurance.referral_number || '');
        formData.append('insurance_diagnosis', window.pendingInsurance.diagnosis || '');
        formData.append('insurance_prescriber', window.pendingInsurance.prescriber || '');
        window.pendingInsurance = null; // Clear after use
    }

    console.log('Sending checkout request...');
    fetch(`{{ route('pos.transaction') }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
        fetch(`{{ url('/pos/customer-lookup') }}?phone=${encodeURIComponent(p)}`, {
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
    fetch(`{{ url('/pos/customer-register') }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
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

// ----------------------------------------------------
// Dawafy Pharmacist & Cashier Separated Workflow Engines
// ----------------------------------------------------

// 1. Send Order to Cashier (Pharmacist Workflow)
function sendOrderToCashier() {
    if(cart.length === 0) return alert('Cart is empty! Add medicines before sending to cashier.');

    let total = cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
    let formData = new FormData();
    formData.append('cart', JSON.stringify(cart));
    formData.append('payment_method', 'Cash');

    let customerId = document.getElementById('selectedCustomerId').value;
    if(customerId) formData.append('customer_id', customerId);

    if(document.getElementById('isDelivery').checked) {
        formData.append('is_delivery', '1');
        formData.append('delivery_address', document.getElementById('delAddress').value);
        formData.append('delivery_phone', document.getElementById('delPhone').value);
    }

    let btn = event ? event.currentTarget : null;
    if(btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Sending to Cashier...`;
    }

    fetch(`{{ route('pos.send_to_cashier') }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(btn) {
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-send-check-fill fs-5"></i> <span>Send to Cashier (Prescribe & Bill)</span>`;
        }
        if(data.success) {
            if(window.notifySalesSuccess) {
                window.notifySalesSuccess(data.transaction_id || 'ORDER', total);
            }
            alert(`Prescription order (${data.transaction_id || 'ORDER'}) successfully sent to Cashier Queue! Inventory is preserved until payment approval.`);
            cart = [];
            renderCart();
            refreshWorkflowCounters();
        } else {
            alert('Failed to send order to Cashier: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => {
        if(btn) {
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-send-check-fill fs-5"></i> <span>Send to Cashier (Prescribe & Bill)</span>`;
        }
        alert('Network error sending order: ' + err.message);
    });
}

// 2. Stock Refill Request Workflow
function openRefillModal(drugId, drugName, currentStock, reorderLevel) {
    document.getElementById('refillDrugId').value = drugId;
    document.getElementById('refillDrugName').innerText = drugName;
    document.getElementById('refillCurrentStock').innerText = currentStock;
    document.getElementById('refillReorderLevel').innerText = reorderLevel || 10;
    document.getElementById('refillQuantity').value = '';
    document.getElementById('refillNotes').value = '';
    new bootstrap.Modal(document.getElementById('stockRefillModal')).show();
}

function submitRefillRequest() {
    let drugId = document.getElementById('refillDrugId').value;
    let qty = document.getElementById('refillQuantity').value;
    let notes = document.getElementById('refillNotes').value;

    if(!qty || parseInt(qty) <= 0) {
        return alert('Please enter a valid requested quantity greater than 0.');
    }

    let fd = new FormData();
    fd.append('drug_id', drugId);
    fd.append('requested_quantity', qty);
    fd.append('notes', notes);

    fetch(`{{ route('refills.request') }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert(data.message || 'Stock refill request submitted to Manager!');
            let modalEl = document.getElementById('stockRefillModal');
            let m = bootstrap.Modal.getInstance(modalEl);
            if(m) m.hide();
        } else {
            alert('Error: ' + (data.message || 'Could not submit refill request.'));
        }
    })
    .catch(err => alert('Network error: ' + err.message));
}

// 3. Cashier Queue: Pending Payments
function openPendingPaymentsModal() {
    new bootstrap.Modal(document.getElementById('pendingPaymentsModal')).show();
    loadPendingPayments();
}

function loadPendingPayments() {
    let container = document.getElementById('pendingPaymentsList');
    container.innerHTML = `<div class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Loading pending payments...</div>`;

    fetch(`{{ route('pos.pending_payments') }}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if(!Array.isArray(data) || data.length === 0) {
            container.innerHTML = `<div class="text-center py-4 text-muted"><i class="bi bi-check2-circle fs-3 d-block text-success mb-1"></i> No pending payments waiting for approval.</div>`;
            updateBadge('cashierQueueCount', 0);
            return;
        }
        updateBadge('cashierQueueCount', data.length);
        let html = '';
        data.forEach(t => {
            let cust = t.customer ? t.customer.name : 'Walk-in Patient';
            let pharm = t.pharmacist ? t.pharmacist.name : 'Pharmacist';
            let items = Array.isArray(t.items) ? t.items : (typeof t.items === 'string' ? JSON.parse(t.items || '[]') : []);
            let itemsSummary = items.map(i => `${i.name || i.drug_name || 'Item'} &times; ${i.quantity || i.qty || 1}`).join(', ');

            html += `
                <div class="card p-3 mb-2 border shadow-sm">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-warning text-dark me-2">Pending Payment</span>
                            <strong class="text-dark fs-6">${t.transaction_id || ('#TXN-' + t.id)}</strong>
                            <div class="small text-muted mt-1">
                                <i class="bi bi-person-fill me-1"></i> Patient: <strong>${cust}</strong> |
                                <i class="bi bi-person-badge me-1"></i> Prescribed by: <strong>${pharm}</strong>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="fs-5 fw-bold text-success tabular-nums">TZS ${parseFloat(t.total_amount).toLocaleString()}</span>
                            <small class="d-block text-muted" style="font-size: 0.72rem;">${t.created_at ? new Date(t.created_at).toLocaleTimeString() : ''}</small>
                        </div>
                    </div>
                    <div class="bg-light p-2 rounded mb-2 border small text-secondary">
                        <strong class="text-dark">Prescription Items:</strong> ${itemsSummary || 'Prescription items'}
                    </div>
                    <div class="d-flex justify-content-end align-items-center gap-2">
                        <select class="form-select form-select-sm w-auto" id="payMethod_${t.id}">
                            <option value="Cash">Cash</option>
                            ${enabledPaymentMethods.includes('Mobile Money') ? '<option value="Mobile Money">Mobile Money (M-Pesa/Tigo)</option>' : ''}
                            ${enabledPaymentMethods.includes('Card') ? '<option value="Card">Bank Card (POS)</option>' : ''}
                            ${enabledPaymentMethods.includes('Wallet') ? '<option value="Wallet">Customer Wallet</option>' : ''}
                            ${enabledPaymentMethods.includes('Insurance') ? '<option value="Insurance">Insurance (NHIF)</option>' : ''}
                        </select>
                        <button type="button" class="btn btn-sm btn-primary fw-bold px-3" onclick="approveCashierPayment(${t.id}, ${t.total_amount})">
                            <i class="bi bi-check-circle-fill me-1"></i> Approve Payment & Receipt
                        </button>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    })
    .catch(err => {
        container.innerHTML = `<div class="text-danger p-3 text-center">Failed to load payments: ${err.message}</div>`;
    });
}

function approveCashierPayment(txnId, totalAmount) {
    let methodEl = document.getElementById('payMethod_' + txnId);
    let method = methodEl ? methodEl.value : 'Cash';

    let fd = new FormData();
    fd.append('payment_method', method);

    fetch(`{{ url('/pos/transactions') }}/${txnId}/approve-payment`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert(`Payment approved successfully! Order status is now PAID. Pharmacist notified to dispense.`);
            
            // Populate receipt for Cashier to print
            let txn = data.transaction || {};
            let subtotal = (txn.total_amount || totalAmount) / 1.18;
            let vat = (txn.total_amount || totalAmount) - subtotal;

            document.getElementById('recTxnId').innerText = txn.transaction_id || ('#TXN-' + txnId);
            document.getElementById('recDate').innerText = new Date().toLocaleDateString();
            document.getElementById('recTime').innerText = new Date().toLocaleTimeString();
            document.getElementById('recSubtotal').innerText = 'TZS ' + Math.round(subtotal).toLocaleString();
            document.getElementById('recVat').innerText = 'TZS ' + Math.round(vat).toLocaleString();
            document.getElementById('recTotal').innerText = 'TZS ' + parseFloat(txn.total_amount || totalAmount).toLocaleString();
            document.getElementById('recMethod').innerText = method;

            if(txn.customer && txn.customer.name) {
                document.getElementById('recCustomer').innerText = txn.customer.name;
                document.getElementById('recCustomerSection').style.display = 'block';
            } else {
                document.getElementById('recCustomerSection').style.display = 'none';
            }

            let items = Array.isArray(txn.items) ? txn.items : (typeof txn.items === 'string' ? JSON.parse(txn.items || '[]') : []);
            let itemsHtml = '';
            items.forEach(item => {
                let qty = item.quantity || item.qty || 1;
                let price = item.price || item.unit_price || 0;
                let lineTotal = qty * price;
                itemsHtml += `
                    <div class="d-flex justify-content-between mb-1">
                        <small style="width: 40%; text-align: left;">${(item.name || item.drug_name || 'Item').substring(0, 15)}</small>
                        <small style="width: 15%; text-align: center;">${qty}</small>
                        <small style="width: 20%; text-align: right;">${parseFloat(price).toLocaleString()}</small>
                        <small style="width: 25%; text-align: right;">${parseFloat(lineTotal).toLocaleString()}</small>
                    </div>
                `;
            });
            document.getElementById('recItems').innerHTML = itemsHtml;

            loadPendingPayments();
            refreshWorkflowCounters();
            new bootstrap.Modal(document.getElementById('receiptModal')).show();
        } else {
            alert('Approval error: ' + (data.message || 'Unknown'));
        }
    })
    .catch(err => alert('Network error: ' + err.message));
}

// 4. Pharmacist Queue: Ready to Dispense (Paid Orders)
function openReadyToDispenseModal() {
    new bootstrap.Modal(document.getElementById('readyToDispenseModal')).show();
    loadReadyToDispense();
}

function loadReadyToDispense() {
    let container = document.getElementById('readyToDispenseList');
    container.innerHTML = `<div class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Loading paid orders ready for handover...</div>`;

    fetch(`{{ route('pos.ready_to_dispense') }}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if(!Array.isArray(data) || data.length === 0) {
            container.innerHTML = `<div class="text-center py-4 text-muted"><i class="bi bi-bag-check fs-3 d-block text-secondary mb-1"></i> No paid prescriptions awaiting dispensation right now.</div>`;
            updateBadge('readyToDispenseCount', 0);
            return;
        }
        updateBadge('readyToDispenseCount', data.length);
        let html = '';
        data.forEach(t => {
            let cust = t.customer ? t.customer.name : 'Walk-in Patient';
            let cashierName = t.cashier ? t.cashier.name : 'Cashier';
            let items = Array.isArray(t.items) ? t.items : (typeof t.items === 'string' ? JSON.parse(t.items || '[]') : []);

            let itemsTable = items.map(i => `
                <div class="d-flex justify-content-between border-bottom py-1 small">
                    <div>
                        <strong class="text-dark">${i.name || i.drug_name || 'Drug'}</strong>
                        <span class="text-muted ms-1">(Qty: <strong>${i.quantity || i.qty || 1}</strong>)</span>
                        ${i.dose_qty ? `<small class="d-block text-primary"><i class="bi bi-clock-history me-1"></i> Dosage: ${i.dose_qty} tabs &times; ${i.dose_times || 1}/day &times; ${i.dose_days || 1} days</small>` : ''}
                    </div>
                    <span class="text-secondary fw-semibold">TZS ${parseFloat((i.price || 0) * (i.quantity || i.qty || 1)).toLocaleString()}</span>
                </div>
            `).join('');

            html += `
                <div class="card p-3 mb-2 border shadow-sm border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-success text-white me-2"><i class="bi bi-check2-all me-1"></i> PAID</span>
                            <strong class="text-dark fs-6">${t.transaction_id || ('#TXN-' + t.id)}</strong>
                            <div class="small text-muted mt-1">
                                <i class="bi bi-person-fill me-1"></i> Patient: <strong>${cust}</strong> |
                                <i class="bi bi-cash-stack me-1"></i> Payment Received by: <strong>${cashierName}</strong> (${t.payment_method || 'Cash'})
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="fs-5 fw-bold text-dark tabular-nums">TZS ${parseFloat(t.total_amount).toLocaleString()}</span>
                        </div>
                    </div>
                    <div class="bg-light p-2 rounded mb-3 border">
                        <strong class="small text-dark d-block mb-1">Verify Medicines Before Handover:</strong>
                        ${itemsTable}
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-success fw-bold px-3 py-2" onclick="confirmDispenseOrder(${t.id})">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Confirm Dispense & Deduct Stock
                        </button>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    })
    .catch(err => {
        container.innerHTML = `<div class="text-danger p-3 text-center">Failed to load orders: ${err.message}</div>`;
    });
}

function confirmDispenseOrder(txnId) {
    if(!confirm('Are you sure you want to finalize dispensation and deduct inventory for this order?')) return;

    fetch(`{{ url('/pos/transactions') }}/${txnId}/confirm-dispense`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert('Dispensation confirmed! Inventory has been officially deducted and sale completed.');
            loadReadyToDispense();
            refreshWorkflowCounters();
            // Refresh stock search to show updated counts
            allLoadedDrugs = [];
            searchDrugs();
        } else {
            alert('Dispense error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(err => alert('Network error: ' + err.message));
}

function updateBadge(id, count) {
    let badge = document.getElementById(id);
    if(badge) badge.innerText = count || 0;
}

function refreshWorkflowCounters() {
    fetch(`{{ route('pos.pending_payments') }}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(data => { if(Array.isArray(data)) updateBadge('cashierQueueCount', data.length); })
        .catch(() => {});

    fetch(`{{ route('pos.ready_to_dispense') }}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(data => { if(Array.isArray(data)) updateBadge('readyToDispenseCount', data.length); })
        .catch(() => {});
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
            @if($has_cashier && $is_pharmacist)
                sendOrderToCashier();
            @else
                initiateCheckout('Cash');
            @endif
        }
    } else if (e.key === 'Escape') {
        let searchInput = document.getElementById('searchInput');
        if (document.activeElement === searchInput && searchInput.value) {
            clearSearchInput();
        }
    }
});

function initPos() {
    searchDrugs();
    refreshWorkflowCounters();
    setInterval(refreshWorkflowCounters, 30000); // Poll workflow queue every 30s
}

if(document.readyState === 'complete' || document.readyState === 'interactive') {
    initPos();
} else {
    document.addEventListener('DOMContentLoaded', initPos);
}
</script>
@endsection
