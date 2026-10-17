@extends('layouts.app')

@section('content')
<!-- Daawafy Executive Expenses View (100% Professional English) -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-cash-coin me-2 text-info"></i> Pharmacy Operational Expenses</h2>
        <p class="text-white-50 mb-0">Utilities, rent, salaries, transport, and inventory procurement costs.</p>
    </div>
    <div>
        <button class="btn-laravel-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="bi bi-plus-circle me-1"></i> + Record Expense
        </button>
    </div>
</div>

<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-md-6">
        <div class="metric-widget metric-red">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Total Expenses</div>
                    <div class="metric-value text-danger">TZS <?= number_format($total_expenses) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-wallet2"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Expense Records</div>
                    <div class="metric-value text-primary"><?= count($expenses) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-receipt"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card-laravel animate-fade-in">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i> Expense Log</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount (TZS)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($expenses)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No operational expenses recorded.</td></tr>
                    <?php endif; ?>
                    <?php foreach($expenses as $idx => $e): ?>
                        <tr>
                            <td class="text-muted"><?= $idx + 1 ?></td>
                            <td><?= date('d M Y', strtotime($e['expense_date'])) ?></td>
                            <td><span class="badge-laravel-blue"><?= htmlspecialchars($e['category']) ?></span></td>
                            <td><small class="text-muted"><?= htmlspecialchars($e['description'] ?: 'N/A') ?></small></td>
                            <td class="fw-bold text-danger">TZS <?= number_format($e['amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
</div>

<!-- Modal: Add Expense -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-cash-coin me-2"></i> Record Operational Expense</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('expenses.store') }}">
        @csrf
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Expense Category <span class="text-danger">*</span></label>
                <select name="category" class="form-select rounded-pill px-3" required>
                    <option value="Utilities">Utilities (Electricity/Water)</option>
                    <option value="Rent">Pharmacy Rent</option>
                    <option value="Salaries">Staff Salaries</option>
                    <option value="Transport">Logistics & Transport</option>
                    <option value="Procurement">Procurement & Inventory</option>
                    <option value="Maintenance">Maintenance & Repairs</option>
                    <option value="Other">Other Operational</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Amount (TZS) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="amount" class="form-control rounded-pill px-3" placeholder="e.g. 50000" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Expense Date <span class="text-danger">*</span></label>
                <input type="date" name="expense_date" class="form-control rounded-pill px-3" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Description / Notes</label>
                <textarea name="description" class="form-control rounded px-3" rows="2" placeholder="Details of expense..."></textarea>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold"><i class="bi bi-check-circle me-1"></i> Record Expense</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
