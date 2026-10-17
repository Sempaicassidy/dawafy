@extends('layouts.app')

@section('content')
<!-- Daawafy Executive Marg Wallet View -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-wallet2 me-2 text-info"></i> Marg Wallet & Customer Loyalty System</h2>
        <p class="text-white-50 mb-0">Prepaid customer balances, instant POS wallet top-ups, and transaction ledger.</p>
    </div>
    <div>
        <button class="btn-laravel-green text-white shadow" data-bs-toggle="modal" data-bs-target="#topupWalletModal">
            <i class="bi bi-plus-circle me-1"></i> + Top Up Customer Wallet
        </button>
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
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Total Active Balance</div>
                    <div class="metric-value text-success">TZS <?= number_format(array_sum(array_column($customers, 'wallet_balance'))) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-wallet2"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-cyan">
            <div class="d-flex align-items-center justify-content-between">
                <?php $active_wallets = count(array_filter($customers, fn($c) => $c['wallet_balance'] > 0)); ?>
                <div>
                    <div class="metric-title">Funded Customer Accounts</div>
                    <div class="metric-value"><?= $active_wallets ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-person-check-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Registered Customers</div>
                    <div class="metric-value"><?= count($customers) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-people-fill"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card-laravel animate-fade-in">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary"><i class="bi bi-person-badge me-2"></i> Customer Wallet Directory</h5>
        <input type="text" class="form-control form-control-sm rounded-pill px-3 w-25" id="walletSearch" placeholder="Search customer name/phone..." onkeyup="filterWalletTable()">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0" id="walletTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer Name</th>
                        <th>Phone Number</th>
                        <th>Current Wallet Balance</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($customers)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No customer wallet accounts found.</td></tr>
                    <?php else: ?>
                        <?php foreach($customers as $idx => $c): ?>
                            <tr>
                                <td class="text-muted"><?= $idx + 1 ?></td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($c['name']) ?></strong>
                                    <small class="text-muted"><?= htmlspecialchars($c['email'] ?: 'No email') ?></small>
                                </td>
                                <td><span class="font-monospace text-primary"><?= htmlspecialchars($c['phone'] ?: 'N/A') ?></span></td>
                                <td class="fw-bold <?= $c['wallet_balance'] > 0 ? 'text-success' : 'text-muted' ?>">
                                    TZS <?= number_format($c['wallet_balance']) ?>
                                </td>
                                <td>
                                    <?php if($c['wallet_balance'] > 0): ?>
                                        <span class="badge-laravel-green"><i class="bi bi-check-circle me-1"></i> Active Balance</span>
                                    <?php else: ?>
                                        <span class="badge-laravel-blue"><i class="bi bi-circle me-1"></i> Zero Balance</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-laravel-green rounded-pill px-3 shadow-sm me-1" onclick="openTopupModal(<?= $c['id'] ?>, '<?= addslashes(htmlspecialchars($c['name'])) ?>', 'credit')">
                                        <i class="bi bi-plus-circle me-1"></i> Top Up
                                    </button>
                                    <button class="btn btn-sm btn-laravel-primary rounded-pill px-3 shadow-sm" onclick="openTopupModal(<?= $c['id'] ?>, '<?= addslashes(htmlspecialchars($c['name'])) ?>', 'debit')">
                                        <i class="bi bi-dash-circle me-1"></i> Debit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Wallet Transaction -->
<div class="modal fade" id="topupWalletModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-wallet2 me-2"></i> Process Customer Wallet Transaction</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('wallet.transaction') }}">
        @csrf
        <input type="hidden" name="wallet_transaction" value="1">
        <input type="hidden" id="walletCustomerId" name="customer_id">
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Select Customer <span class="text-danger">*</span></label>
                <select id="walletCustomerSelect" class="form-select rounded-pill px-3" required onchange="syncCustomerSelect(this.value)">
                    <option value="">-- Choose Customer --</option>
                    <?php foreach($customers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>) - TZS <?= number_format($c['wallet_balance']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Transaction Type <span class="text-danger">*</span></label>
                <select id="transactionType" name="transaction_type" class="form-select rounded-pill px-3" required>
                    <option value="credit">Deposit / Top Up (+)</option>
                    <option value="debit">Withdrawal / Payment (-)</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Amount (TZS) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="amount" class="form-control rounded-pill px-3" placeholder="e.g. 50000" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Description / Reference</label>
                <input type="text" name="description" class="form-control rounded-pill px-3" placeholder="e.g. Cash Top Up at POS counter">
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-laravel-primary rounded-pill px-4 fw-bold"><i class="bi bi-check-circle me-1"></i> Submit Transaction</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function filterWalletTable() {
    let input = document.getElementById("walletSearch").value.toLowerCase();
    let rows = document.querySelectorAll("#walletTable tbody tr");
    rows.forEach(r => {
        let text = r.innerText.toLowerCase();
        r.style.display = text.includes(input) ? "" : "none";
    });
}

function openTopupModal(customerId, customerName, type) {
    document.getElementById('walletCustomerId').value = customerId;
    document.getElementById('walletCustomerSelect').value = customerId;
    document.getElementById('transactionType').value = type;
    new bootstrap.Modal(document.getElementById('topupWalletModal')).show();
}

function syncCustomerSelect(val) {
    document.getElementById('walletCustomerId').value = val;
}
</script>
@endsection
