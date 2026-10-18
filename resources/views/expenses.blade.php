@extends('layouts.app')

@section('content')
<!-- Daawafy Executive Expenses & Petty Cash View -->
<div class="laravel-hero-banner mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3 pb-2 border-bottom animate-fade-in">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                <i class="bi bi-wallet2 me-1"></i> Cash Disbursements
            </span>
            <span class="text-muted small">| Petty Cash & Operating Costs</span>
        </div>
        <h2 class="fw-bold mb-1 text-dark">Pharmacy Operating Expenses</h2>
        <p class="text-muted mb-0 small">Track utilities, rent, logistics, staff expenses, maintenance, and petty cash disbursements in real time.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('reports.index', ['tab' => 'financial']) }}" class="btn btn-outline-secondary text-dark rounded-pill px-3 btn-sm fw-semibold">
            <i class="bi bi-pie-chart me-1 text-primary"></i> P&L Statement
        </a>
        <button class="btn btn-danger rounded-pill px-4 btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="bi bi-plus-circle me-1"></i> + Record Expense
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 animate-fade-in" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-6"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 animate-fade-in" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i> {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Executive KPI Summary Widgets -->
<div class="row g-3 mb-4 animate-fade-in">
    <!-- Filtered / Total Expenses -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 border-start border-4 border-danger rounded-4 p-3 h-100 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">Total Expenses (Selected Period)</span>
                <div class="rounded-circle bg-danger-subtle text-danger p-2" style="width: 36px; height: 36px; display: grid; place-items: center;">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1">TZS {{ number_format($total_expenses) }}</h3>
            <div class="d-flex justify-content-between align-items-center small text-muted">
                <span>{{ count($expenses) }} records listed</span>
                <span class="text-danger fw-semibold"><i class="bi bi-arrow-down-right"></i> Outflow</span>
            </div>
        </div>
    </div>

    <!-- Today's Petty Expenses (Cashier Drawer Relevance) -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 border-start border-4 border-warning rounded-4 p-3 h-100 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">Today's Disbursements</span>
                <div class="rounded-circle bg-warning-subtle text-warning p-2" style="width: 36px; height: 36px; display: grid; place-items: center;">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1">TZS {{ number_format($today_expenses) }}</h3>
            <div class="d-flex justify-content-between align-items-center small text-muted">
                <span>Cash drawer impact</span>
                <span class="text-warning fw-semibold"><i class="bi bi-clock-history"></i> Today</span>
            </div>
        </div>
    </div>

    <!-- This Month's Expenses -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 border-start border-4 border-primary rounded-4 p-3 h-100 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">This Month's Overhead</span>
                <div class="rounded-circle bg-primary-subtle text-primary p-2" style="width: 36px; height: 36px; display: grid; place-items: center;">
                    <i class="bi bi-calendar-check"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1">TZS {{ number_format($month_expenses) }}</h3>
            <div class="d-flex justify-content-between align-items-center small text-muted">
                <span>Current monthly cycle</span>
                <span class="text-primary fw-semibold"><i class="bi bi-graph-down"></i> Monthly</span>
            </div>
        </div>
    </div>

    <!-- Top Spending Category -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 border-start border-4 border-info rounded-4 p-3 h-100 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-semibold">Highest Spending Area</span>
                <div class="rounded-circle bg-info-subtle text-info p-2" style="width: 36px; height: 36px; display: grid; place-items: center;">
                    <i class="bi bi-pie-chart-fill"></i>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-1 text-truncate" title="{{ $top_category }}">{{ $top_category }}</h3>
            <div class="d-flex justify-content-between align-items-center small text-muted">
                <span>Dominant cost driver</span>
                <span class="text-info fw-semibold"><i class="bi bi-tag-fill"></i> Category</span>
            </div>
        </div>
    </div>
</div>

<!-- Search, Filter & Date Range Filter Card -->
<div class="card shadow-sm border-0 rounded-4 mb-4 bg-white animate-fade-in">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('expenses.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label text-muted small fw-bold mb-1">Search Note / Purpose</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-secondary"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search description..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small fw-bold mb-1">Category</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="All">All Categories</option>
                    @foreach($category_options as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
            </div>
            <div class="col-6 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100 fw-bold">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'category', 'from_date', 'to_date']))
                    <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="Clear Filters">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Main Expenses Ledger Card -->
<div class="card shadow-sm border-0 rounded-4 bg-white animate-fade-in overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="bi bi-journal-text fs-6"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Operational Expense Ledger</h6>
                <small class="text-muted">Itemized operational costs and petty cash logs</small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Ledger
            </button>
            <button class="btn btn-sm btn-danger rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="bi bi-plus-lg me-1"></i> Record Expense
            </button>
        </div>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="min-width: 110px;">Date</th>
                        <th style="min-width: 140px;">Category</th>
                        <th style="min-width: 200px;">Description & Purpose</th>
                        <th style="min-width: 140px;" class="text-end">Amount (TZS)</th>
                        <th style="min-width: 140px;">Recorded By</th>
                        <th style="min-width: 120px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $idx => $e)
                        <tr>
                            <td class="text-muted small">{{ $idx + 1 }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ date('d M Y', strtotime($e->expense_date)) }}</div>
                                <small class="text-muted" style="font-size: 0.72rem;">{{ $e->created_at ? $e->created_at->format('H:i A') : '' }}</small>
                            </td>
                            <td>
                                @php
                                    $catColor = match($e->category) {
                                        'Utilities' => 'primary',
                                        'Rent' => 'dark',
                                        'Salaries' => 'success',
                                        'Transport' => 'info',
                                        'Procurement' => 'warning',
                                        'Maintenance' => 'secondary',
                                        default => 'danger'
                                    };
                                @endphp
                                <span class="badge bg-{{ $catColor }}-subtle text-{{ $catColor }} border border-{{ $catColor }}-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-tag me-1"></i> {{ $e->category }}
                                </span>
                            </td>
                            <td>
                                <div class="text-dark fw-normal">{{ $e->description ?: 'Operational Expense' }}</div>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-danger fs-6">TZS {{ number_format($e->amount) }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-person-circle text-muted"></i>
                                    <span class="small fw-semibold text-dark">{{ $e->user?->name ?? 'Staff' }}</span>
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;">{{ $e->user?->role?->name ?? '' }}</small>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary rounded-pill p-1 px-2" 
                                            title="Edit Expense"
                                            onclick="openEditExpenseModal({{ $e->id }}, '{{ addslashes($e->category) }}', '{{ $e->amount }}', '{{ $e->expense_date }}', '{{ addslashes($e->description ?? '') }}')">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger rounded-pill p-1 px-2" 
                                            title="Delete Expense"
                                            onclick="confirmDeleteExpense({{ $e->id }}, '{{ addslashes($e->category) }}', '{{ number_format($e->amount) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                <h6 class="fw-bold text-dark mb-1">No Expense Records Found</h6>
                                <p class="text-muted small mb-3">No expenses match your current date or category filter.</p>
                                <button class="btn btn-sm btn-danger rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                                    <i class="bi bi-plus-circle me-1"></i> Record New Expense
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($expenses->isNotEmpty())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end text-dark">Total Outflow:</td>
                            <td class="text-end text-danger fs-6">TZS {{ number_format($total_expenses) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal 1: Record Operational Expense                                       -->
<!-- ========================================================================= -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-danger text-white py-3 px-4">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-cash-coin fs-5"></i>
            <h5 class="modal-title fw-bold mb-0">Record Operational Expense</h5>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('expenses.store') }}">
        @csrf
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="form-label fw-bold text-dark small mb-1">Expense Category <span class="text-danger">*</span></label>
                <select name="category" id="addCategorySelect" class="form-select rounded-3" required onchange="toggleCustomCategoryInput('add')">
                    <option value="Utilities">Utilities (Electricity / LUKU / Water)</option>
                    <option value="Rent">Pharmacy Store Rent</option>
                    <option value="Salaries">Staff Salaries & Allowances</option>
                    <option value="Transport">Logistics, Delivery & Transport</option>
                    <option value="Procurement">Procurement & Inventory Supply</option>
                    <option value="Maintenance">Repairs, AC & Equipment Maintenance</option>
                    <option value="Cleaning & Sanitation">Cleaning & Pharmacy Sanitation</option>
                    <option value="Stationery & Packaging">Packaging Bags, Receipts & Printing</option>
                    <option value="Regulatory & Licenses">Pharmacy Board / TMDA / Regulatory Fees</option>
                    <option value="Petty Cash">Minor Office / Cashier Petty Cash</option>
                    <option value="Other">Other (Custom Category)</option>
                </select>
            </div>
            
            <div class="mb-3 d-none" id="addCustomCategoryWrapper">
                <label class="form-label fw-bold text-dark small mb-1">Specify Custom Category <span class="text-danger">*</span></label>
                <input type="text" name="custom_category" id="addCustomCategoryInput" class="form-control rounded-3" placeholder="Enter custom category name...">
            </div>

            <div class="row g-2 mb-3">
                <div class="col-12 col-sm-6">
                    <label class="form-label fw-bold text-dark small mb-1">Amount (TZS) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted fw-bold">TZS</span>
                        <input type="number" step="0.01" min="1" name="amount" class="form-control rounded-end-3" placeholder="e.g. 25000" required>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label fw-bold text-dark small mb-1">Expense Date <span class="text-danger">*</span></label>
                    <input type="date" name="expense_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label fw-bold text-dark small mb-1">Description / Purpose / Slip #</label>
                <textarea name="description" class="form-control rounded-3" rows="3" placeholder="e.g. LUKU token recharge for counter AC, Receipt #892..."></textarea>
                <small class="text-muted">Include any invoice number, receipt slip number, or notes for auditing.</small>
            </div>
        </div>
        <div class="modal-footer border-top bg-light py-2 px-4">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                <i class="bi bi-check-circle me-1"></i> Save Expense
            </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- Modal 2: Edit Existing Expense                                            -->
<!-- ========================================================================= -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
      <div class="modal-header bg-dark text-white py-3 px-4">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-pencil-square fs-5 text-warning"></i>
            <h5 class="modal-title fw-bold mb-0">Edit Operational Expense</h5>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="editExpenseForm" method="POST" action="">
        @csrf
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="form-label fw-bold text-dark small mb-1">Expense Category <span class="text-danger">*</span></label>
                <select name="category" id="editCategorySelect" class="form-select rounded-3" required onchange="toggleCustomCategoryInput('edit')">
                    @foreach($category_options as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                    <option value="Other">Other (Custom Category)</option>
                </select>
            </div>

            <div class="mb-3 d-none" id="editCustomCategoryWrapper">
                <label class="form-label fw-bold text-dark small mb-1">Specify Custom Category <span class="text-danger">*</span></label>
                <input type="text" name="custom_category" id="editCustomCategoryInput" class="form-control rounded-3" placeholder="Enter custom category name...">
            </div>

            <div class="row g-2 mb-3">
                <div class="col-12 col-sm-6">
                    <label class="form-label fw-bold text-dark small mb-1">Amount (TZS) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted fw-bold">TZS</span>
                        <input type="number" step="0.01" min="1" name="amount" id="editAmountInput" class="form-control rounded-end-3" required>
                    </div>
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label fw-bold text-dark small mb-1">Expense Date <span class="text-danger">*</span></label>
                    <input type="date" name="expense_date" id="editDateInput" class="form-control rounded-3" required>
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label fw-bold text-dark small mb-1">Description / Purpose / Slip #</label>
                <textarea name="description" id="editDescriptionInput" class="form-control rounded-3" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer border-top bg-light py-2 px-4">
            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                <i class="bi bi-save me-1"></i> Update Expense
            </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteExpenseForm" method="POST" action="" class="d-none">
    @csrf
    @method('DELETE')
</form>

<script>
function toggleCustomCategoryInput(type) {
    const select = document.getElementById(type + 'CategorySelect');
    const wrapper = document.getElementById(type + 'CustomCategoryWrapper');
    const input = document.getElementById(type + 'CustomCategoryInput');
    if (select && wrapper) {
        if (select.value === 'Other') {
            wrapper.classList.remove('d-none');
            if (input) input.setAttribute('required', 'required');
        } else {
            wrapper.classList.add('d-none');
            if (input) {
                input.removeAttribute('required');
                input.value = '';
            }
        }
    }
}

function openEditExpenseModal(id, category, amount, date, description) {
    const form = document.getElementById('editExpenseForm');
    if (form) {
        form.action = "{{ url('/expenses') }}/" + id + "/update";
    }

    const catSelect = document.getElementById('editCategorySelect');
    if (catSelect) {
        let found = false;
        for (let i = 0; i < catSelect.options.length; i++) {
            if (catSelect.options[i].value === category) {
                catSelect.selectedIndex = i;
                found = true;
                break;
            }
        }
        if (!found) {
            catSelect.value = 'Other';
            toggleCustomCategoryInput('edit');
            const customInput = document.getElementById('editCustomCategoryInput');
            if (customInput) customInput.value = category;
        } else {
            toggleCustomCategoryInput('edit');
        }
    }

    const amountInput = document.getElementById('editAmountInput');
    if (amountInput) amountInput.value = amount;

    const dateInput = document.getElementById('editDateInput');
    if (dateInput) dateInput.value = date;

    const descInput = document.getElementById('editDescriptionInput');
    if (descInput) descInput.value = description;

    const modalEl = document.getElementById('editExpenseModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
}

function confirmDeleteExpense(id, category, amountFormatted) {
    if (confirm(`Are you sure you want to delete this expense record?\n\nCategory: ${category}\nAmount: TZS ${amountFormatted}\n\nThis will remove the disbursement from financial and shift accounts.`)) {
        const form = document.getElementById('deleteExpenseForm');
        if (form) {
            form.action = "{{ url('/expenses') }}/" + id;
            form.submit();
        }
    }
}
</script>
@endsection
