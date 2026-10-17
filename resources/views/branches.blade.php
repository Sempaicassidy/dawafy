@extends('layouts.app')

@section('content')
<!-- Daawafy Executive Branches Directory View -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-diagram-3 me-2 text-info"></i> Pharmacy Branches & Multi-Store Management</h2>
        <p class="text-white-50 mb-0">Register and control sub-branches, manage locations, and monitor branch status.</p>
    </div>
    <div>
        <button class="btn-laravel-green text-white shadow" data-bs-toggle="modal" data-bs-target="#addBranchModal">
            <i class="bi bi-plus-circle me-1"></i> + Add New Branch
        </button>
    </div>
</div>

<?php if(isset($msg)): ?>
    <div class="alert alert-success rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-check-circle me-2"></i> <?= $msg ?></div>
<?php endif; ?>
<?php if(isset($error)): ?>
    <div class="alert alert-danger rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-exclamation-triangle me-2"></i> <?= $error ?></div>
<?php endif; ?>

<!-- Stats Row -->
<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-md-4">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Total Registered Branches</div>
                    <div class="metric-value text-primary"><?= count($branches) ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-building"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <?php 
                    $branchList = is_array($branches) ? $branches : (method_exists($branches, 'toArray') ? $branches->toArray() : (array)$branches);
                    $active_b = count(array_filter($branchList, fn($b) => ($b['status'] ?? '') === 'Active')); 
                ?>
                <div>
                    <div class="metric-title">Active Operational Branches</div>
                    <div class="metric-value text-success"><?= $active_b ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-check-circle-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-yellow">
            <div class="d-flex align-items-center justify-content-between">
                <?php $inactive_b = count(array_filter($branchList, fn($b) => ($b['status'] ?? '') !== 'Active')); ?>
                <div>
                    <div class="metric-title">Inactive / Pending Branches</div>
                    <div class="metric-value text-warning"><?= $inactive_b ?></div>
                </div>
                <div class="metric-icon"><i class="bi bi-pause-circle"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card-laravel animate-fade-in">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary"><i class="bi bi-shop me-2"></i> Pharmacy Branches Directory</h5>
        <input type="text" class="form-control form-control-sm rounded-pill px-3 w-25" id="branchSearch" placeholder="Search branch name/address..." onkeyup="filterBranchTable()">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0" id="branchTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Branch Name</th>
                        <th>Address</th>
                        <th>Contact Phone & Email</th>
                        <th>Subscription</th>
                        <th>Status</th>
                        <th>Date Registered</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($branches)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No branch outlets registered yet. Click "+ Add New Branch" to register a branch.</td></tr>
                    <?php else: ?>
                        <?php foreach($branches as $idx => $b): ?>
                            <tr>
                                <td class="text-muted"><?= $idx + 1 ?></td>
                                <td>
                                    <strong class="text-dark d-block"><i class="bi bi-shop text-primary me-1"></i> <?= htmlspecialchars($b['name']) ?></strong>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($b['address'] ?: 'N/A') ?></small></td>
                                <td>
                                    <span class="font-monospace small"><?= htmlspecialchars($b['phone'] ?: 'N/A') ?></span>
                                    <?php if(!empty($b['email'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($b['email']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($b['payment_status'] === 'Paid'): ?>
                                        <span class="badge-laravel-green"><i class="bi bi-check-circle me-1"></i> Paid</span>
                                    <?php else: ?>
                                        <span class="badge-laravel-yellow"><i class="bi bi-clock me-1"></i> Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($b['status'] === 'Active'): ?>
                                        <span class="badge-laravel-green"><i class="bi bi-check-circle-fill me-1"></i> Active</span>
                                    <?php else: ?>
                                        <span class="badge-laravel-danger"><i class="bi bi-pause-circle me-1"></i> Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= date('d M Y', strtotime($b['created_at'])) ?></small></td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('branches.toggle') }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="branch_id" value="<?= $b['id'] ?>">
                                        <?php if($b['status'] === 'Active'): ?>
                                            <button type="submit" name="toggle_branch" value="deactivate" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm me-1">
                                                <i class="bi bi-pause-fill me-1"></i> Deactivate
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="toggle_branch" value="activate" class="btn btn-sm btn-laravel-green text-white rounded-pill px-3 shadow-sm me-1">
                                                <i class="bi bi-play-fill me-1"></i> Activate
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                    <button class="btn btn-sm btn-laravel-primary rounded-pill px-3 shadow-sm" onclick="openEditBranchModal(<?= htmlspecialchars(json_encode($b)) ?>)">
                                        <i class="bi bi-pencil me-1"></i> Edit
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

<!-- Modal 1: Add New Branch -->
<div class="modal fade" id="addBranchModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i> Register New Pharmacy Branch</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('branches.store') }}">
        @csrf
        <input type="hidden" name="add_branch" value="1">
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Branch Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control rounded-pill px-3" placeholder="e.g. Kariakoo Branch Outlet" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Phone Number <span class="text-danger">*</span></label>
                <input type="text" name="phone" class="form-control rounded-pill px-3" placeholder="e.g. +255 712 345 678" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Email Address</label>
                <input type="email" name="email" class="form-control rounded-pill px-3" placeholder="branch@pharmacy.com">
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Physical Location Address</label>
                <textarea name="address" class="form-control rounded-3" rows="2" placeholder="Street, City, Building..."></textarea>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-laravel-primary rounded-pill px-4 fw-bold"><i class="bi bi-check-circle me-1"></i> Register Branch</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 2: Edit Branch -->
<div class="modal fade" id="editBranchModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i> Edit Branch Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('branches.update_branch') }}">
        @csrf
        <input type="hidden" name="edit_branch" value="1">
        <input type="hidden" id="editBranchId" name="branch_id">
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Branch Name <span class="text-danger">*</span></label>
                <input type="text" id="editBranchName" name="name" class="form-control rounded-pill px-3" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Phone Number <span class="text-danger">*</span></label>
                <input type="text" id="editBranchPhone" name="phone" class="form-control rounded-pill px-3" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Email Address</label>
                <input type="email" id="editBranchEmail" name="email" class="form-control rounded-pill px-3">
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Physical Location Address</label>
                <textarea id="editBranchAddress" name="address" class="form-control rounded-3" rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-laravel-primary rounded-pill px-4 fw-bold"><i class="bi bi-check-circle me-1"></i> Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function filterBranchTable() {
    let input = document.getElementById("branchSearch").value.toLowerCase();
    let rows = document.querySelectorAll("#branchTable tbody tr");
    rows.forEach(r => {
        let text = r.innerText.toLowerCase();
        r.style.display = text.includes(input) ? "" : "none";
    });
}

function openEditBranchModal(branch) {
    document.getElementById('editBranchId').value = branch.id;
    document.getElementById('editBranchName').value = branch.name || '';
    document.getElementById('editBranchPhone').value = branch.phone || '';
    document.getElementById('editBranchEmail').value = branch.email || '';
    document.getElementById('editBranchAddress').value = branch.address || '';
    new bootstrap.Modal(document.getElementById('editBranchModal')).show();
}
</script>
@endsection
