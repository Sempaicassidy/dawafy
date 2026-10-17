@extends('layouts.app')

@section('content')
<!-- Traditional Users View -->
<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold text-dark mb-0">User Accounts & Permissions</h4>
        <small class="text-muted">Pharmacists, Cashiers, Store Managers, and System Administrators</small>
    </div>
    <div>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus me-1"></i> Add Staff Account
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success py-2 px-3 mb-3 d-flex align-items-center"><i class="bi bi-check-circle me-2"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger py-2 px-3 mb-3 d-flex align-items-center"><i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-header bg-light py-2 px-3">
        <h6 class="mb-0 fw-semibold text-dark">Registered Staff Accounts</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Role</th>
                        <th>Device Session</th>
                        <th>Created Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($users)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No user accounts found.</td></tr>
                    <?php endif; ?>
                    <?php foreach($users as $idx => $u): ?>
                        <tr>
                            <td class="text-muted"><?= $idx + 1 ?></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['full_name'] ?: 'N/A') ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($u['role']) ?></span></td>
                            <td>
                                @if(!empty($u['is_online']))
                                    <span class="badge bg-success"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Active Device</span>
                                @else
                                    <span class="badge bg-light text-muted border">No Active Session</span>
                                @endif
                            </td>
                            <td><small class="text-muted"><?= date('d M Y', strtotime($u['created_at'])) ?></small></td>
                            <td class="text-end">
                                @if(!empty($u['is_online']))
                                    <form method="POST" action="{{ route('users.reset_session', $u['id']) }}" class="d-inline" onsubmit="return confirm('Una uhakika unataka kufunga kikao cha mtumiaji huyu?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0" title="Force Logout / Reset Session">
                                            <i class="bi bi-power me-1"></i> Funga Session
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add User -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-light text-dark py-2 px-3">
        <h6 class="modal-title fw-semibold"><i class="bi bi-person-plus me-1"></i> Add Staff User Account</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control rounded-pill px-3" placeholder="e.g. Dr. John Mary" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control rounded-pill px-3" placeholder="e.g. john.mary" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control rounded-pill px-3" placeholder="Account password" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Role & Permissions <span class="text-danger">*</span></label>
                <select name="role" class="form-select rounded-pill px-3" required>
                    <option value="Cashier">Cashier (POS Checkout Only)</option>
                    <option value="Pharmacist">Pharmacist (POS & Inventory)</option>
                    <option value="Manager">Store Manager (All Operations)</option>
                    <option value="Admin">Administrator (Full System Access)</option>
                </select>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-laravel-primary rounded-pill px-4 fw-bold"><i class="bi bi-check-circle me-1"></i> Save Staff Account</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
