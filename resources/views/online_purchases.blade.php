@extends('layouts.app')

@section('content')
<!-- Daawafy Executive Online Purchases View -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-cart-check me-2 text-info"></i> Online Supplier Purchases & Portal</h2>
        <p class="text-white-50 mb-0">Import purchase orders from wholesale supplier portals, track shipments, and sync inventory.</p>
    </div>
    <div>
        <button class="btn-laravel-green text-white shadow" data-bs-toggle="modal" data-bs-target="#importPurchaseModal">
            <i class="bi bi-cloud-arrow-down me-1"></i> + Import Online Purchase
        </button>
    </div>
</div>

@if(session('success') || isset($msg))
    <div class="alert alert-success rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-check-circle me-2"></i> {{ session('success') ?? $msg }}</div>
@endif
@if(session('error') || isset($error))
    <div class="alert alert-danger rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') ?? $error }}</div>
@endif
@if(isset($errors) && $errors->any())
    <div class="alert alert-danger rounded-4 px-4 mb-4 animate-fade-in">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> Error:</div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- System Cards -->
<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-md-3">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Total Purchase Orders</div>
                    <div class="metric-value text-primary"><?= count($purchase_orders) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-bag-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-yellow">
            <div class="d-flex align-items-center justify-content-between">
                <?php $pending = count(array_filter($purchase_orders, fn($po) => $po['status'] === 'pending')); ?>
                <div>
                    <div class="metric-title">Pending Delivery</div>
                    <div class="metric-value text-warning"><?= $pending ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-truck"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <?php $received = count(array_filter($purchase_orders, fn($po) => $po['status'] === 'received')); ?>
                <div>
                    <div class="metric-title">Received & Stocked</div>
                    <div class="metric-value text-success"><?= $received ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-box-seam-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="metric-widget metric-red">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Total Spend</div>
                    <div class="metric-value text-danger">TZS <?= number_format(array_sum(array_column($purchase_orders, 'total_amount'))) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-cash-stack"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card-laravel animate-fade-in">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary"><i class="bi bi-receipt me-2"></i> Online Supplier Purchase History</h5>
        <input type="text" class="form-control form-control-sm rounded-pill px-3 w-25" id="poSearch" placeholder="Search PO # or supplier..." onkeyup="filterPoTable()">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0" id="poTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order Ref</th>
                        <th>Supplier</th>
                        <th>Platform</th>
                        <th>Total Amount</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($purchase_orders)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No online purchase orders recorded.</td></tr>
                    <?php else: ?>
                        <?php foreach($purchase_orders as $idx => $po): ?>
                            <tr>
                                <td class="text-muted"><?= $idx + 1 ?></td>
                                <td><strong class="text-dark font-monospace"><?= htmlspecialchars($po['order_reference']) ?></strong></td>
                                <td><?= htmlspecialchars($po['supplier_name'] ?: 'Wholesale Supplier') ?></td>
                                <td><span class="badge-laravel-blue"><?= htmlspecialchars($po['source_platform']) ?></span></td>
                                <td class="fw-bold text-primary">TZS <?= number_format($po['total_amount']) ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($po['order_date']) ?></small></td>
                                <td>
                                    <?php if($po['status'] === 'received'): ?>
                                        <span class="badge-laravel-green"><i class="bi bi-check-circle me-1"></i> Received</span>
                                    <?php else: ?>
                                        <span class="badge-laravel-yellow"><i class="bi bi-clock me-1"></i> Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if($po['status'] !== 'received'): ?>
                                        <form method="POST" action="{{ route('addons.online_purchases') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="purchase_order_id" value="<?= $po['id'] ?>">
                                            <button type="submit" name="mark_received" class="btn btn-sm btn-laravel-green rounded-pill text-white shadow-sm">
                                                <i class="bi bi-check-lg me-1"></i> Receive & Add to Stock
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small"><i class="bi bi-shield-check text-success me-1"></i> Inventory Stocked</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Import Purchase Order -->
<div class="modal fade" id="importPurchaseModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-cloud-arrow-down me-2"></i> Import Online Purchase Order</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('addons.online_purchases') }}">
        @csrf
        <input type="hidden" name="import_purchase" value="1">
        <div class="modal-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" class="form-select rounded-pill px-3" required>
                        <option value="">-- Select Supplier --</option>
                        <?php foreach($suppliers as $sup): ?>
                            <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold text-muted small mb-1">Source Platform / Portal <span class="text-danger">*</span></label>
                    <select name="source_platform" class="form-select rounded-pill px-3" required>
                        <option value="MSD Online">MSD Portal (Medical Stores Dept)</option>
                        <option value="PharmAccess B2B">PharmAccess B2B Marketplace</option>
                        <option value="Wholesale Portal">Direct Wholesale Portal</option>
                        <option value="CSV/Excel Import">CSV / Excel Direct File</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold text-muted small mb-1">PO Reference # <span class="text-danger">*</span></label>
                    <input type="text" name="order_reference" class="form-control rounded-pill px-3" placeholder="e.g. PO-2026-089" required>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold text-muted small mb-1">Total Order Value (TZS) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="total_amount" class="form-control rounded-pill px-3" placeholder="e.g. 450000" required>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold text-muted small mb-1">Order Date <span class="text-danger">*</span></label>
                    <input type="date" name="order_date" class="form-control rounded-pill px-3" value="<?= date('Y-m-d') ?>" required>
                </div>
                <input type="hidden" name="items_data" value="[]">
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-laravel-primary rounded-pill px-4 fw-bold"><i class="bi bi-check-circle me-1"></i> Save Order</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function filterPoTable() {
    let input = document.getElementById("poSearch").value.toLowerCase();
    let rows = document.querySelectorAll("#poTable tbody tr");
    rows.forEach(r => {
        let text = r.innerText.toLowerCase();
        r.style.display = text.includes(input) ? "" : "none";
    });
}
</script>
@endsection
