@extends('layouts.app')

@section('content')
<!-- Traditional Users View -->
<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
    <div>
        <h4 class="fw-bold text-dark mb-0">User Accounts & Permissions</h4>
        <small class="text-muted">Tenant Staff Roles: Manager, Pharmacist, and Cashier</small>
    </div>
    <div>
        <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus me-1"></i> Add Staff Account
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success py-2 px-3 mb-3 d-flex align-items-center rounded-pill"><i class="bi bi-check-circle me-2"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger py-2 px-3 mb-3 d-flex align-items-center rounded-pill"><i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}</div>
@endif

@if(isset($errors) && $errors->any())
    <div class="alert alert-danger py-2 px-3 mb-3 rounded-4 shadow-sm">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Registration Error:</div>
        <ul class="mb-0 small ps-3">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-header bg-light py-2 px-3 rounded-top-4">
        <h6 class="mb-0 fw-semibold text-dark"><i class="bi bi-people me-1 text-primary"></i> Registered Staff Accounts</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Phone</th>
                        <th>Tenant Role</th>
                        <th>Device Session</th>
                        <th>Created Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($users)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No user accounts found.</td></tr>
                    <?php endif; ?>
                    <?php foreach($users as $idx => $u): 
                        $effectiveRole = ($u['role'] === 'Admin') ? 'Manager' : $u['role'];
                    ?>
                        <tr>
                            <td class="text-muted"><?= $idx + 1 ?></td>
                            <td class="fw-semibold text-dark"><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['full_name'] ?? 'N/A') ?></td>
                            <td>
                                <?php if(!empty($u['phone'])): ?>
                                    <a href="tel:<?= htmlspecialchars($u['phone']) ?>" class="text-decoration-none text-dark">
                                        <i class="bi bi-telephone-fill text-success me-1"></i> <?= htmlspecialchars($u['phone']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                @if($effectiveRole === 'Manager')
                                    <span class="badge bg-primary px-2 py-1"><i class="bi bi-shield-check me-1"></i> Manager</span>
                                @elseif($effectiveRole === 'Pharmacist')
                                    <span class="badge bg-success px-2 py-1"><i class="bi bi-capsule me-1"></i> Pharmacist</span>
                                @elseif($effectiveRole === 'Cashier')
                                    <span class="badge bg-info text-dark px-2 py-1"><i class="bi bi-cart me-1"></i> Cashier</span>
                                @else
                                    <span class="badge bg-secondary px-2 py-1"><?= htmlspecialchars($effectiveRole) ?></span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($u['is_online']))
                                    <span class="badge bg-success"><i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> Active Device</span>
                                @else
                                    <span class="badge bg-light text-muted border">No Active Session</span>
                                @endif
                            </td>
                            <td><small class="text-muted"><?= date('d M Y', strtotime($u['created_at'])) ?></small></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0" 
                                            onclick="openEditUserModal('<?= $u['id'] ?>', '<?= addslashes(htmlspecialchars($u['full_name'] ?? '')) ?>', '<?= addslashes(htmlspecialchars($effectiveRole)) ?>', '<?= addslashes(htmlspecialchars($u['phone'] ?? '')) ?>')">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                    @if(!empty($u['is_online']))
                                        <form method="POST" action="{{ route('users.reset_session', $u['id']) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to terminate this user\'s session?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0" title="Force Logout / Reset Session">
                                                <i class="bi bi-power me-1"></i> Terminate Session
                                            </button>
                                        </form>
                                    @endif
                                </div>
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
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header bg-light text-dark py-2 px-3 rounded-top-4">
        <h6 class="modal-title fw-semibold"><i class="bi bi-person-plus me-1 text-primary"></i> Add Staff User Account</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="full_name" value="{{ old('full_name') }}" class="form-control rounded-pill px-3" placeholder="e.g. Dr. John Mary" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Username (No spaces, must be unique) <span class="text-danger">*</span></label>
                <input type="text" name="username" value="{{ old('username') }}" class="form-control rounded-pill px-3" placeholder="e.g. john.mary" required>
                <small class="text-muted">Used by staff to log in to POS and system.</small>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Phone Number (Optional)</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control rounded-pill px-3" placeholder="e.g. 0712345678">
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control rounded-pill px-3" placeholder="At least 4 characters" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Role & Permissions <span class="text-danger">*</span></label>
                <select name="role" class="form-select rounded-pill px-3" required>
                    <option value="Manager" {{ old('role') === 'Manager' ? 'selected' : '' }}>Manager (Full Store Operations & Reports)</option>
                    <option value="Pharmacist" {{ old('role', 'Pharmacist') === 'Pharmacist' ? 'selected' : '' }}>Pharmacist (POS, Dispensing & Inventory)</option>
                    <option value="Cashier" {{ old('role') === 'Cashier' ? 'selected' : '' }}>Cashier (POS Checkout Only)</option>
                </select>
                <small class="text-muted d-block mt-1">Tenant roles are strictly: Manager, Pharmacist, and Cashier.</small>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="bi bi-check-circle me-1"></i> Save Staff Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header bg-light text-dark py-2 px-3 rounded-top-4">
        <h6 class="modal-title fw-semibold"><i class="bi bi-pencil-square me-1 text-primary"></i> Edit Staff Account</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('users.update') }}">
        @csrf
        <input type="hidden" name="user_id" id="edit_user_id">
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="full_name" id="edit_full_name" class="form-control rounded-pill px-3" required>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Phone Number</label>
                <input type="text" name="phone" id="edit_phone" class="form-control rounded-pill px-3" placeholder="e.g. 0712345678">
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Role & Permissions <span class="text-danger">*</span></label>
                <select name="role" id="edit_role" class="form-select rounded-pill px-3" required>
                    <option value="Manager">Manager (Full Store Operations & Reports)</option>
                    <option value="Pharmacist">Pharmacist (POS, Dispensing & Inventory)</option>
                    <option value="Cashier">Cashier (POS Checkout Only)</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">New Password (Leave blank to keep current)</label>
                <input type="password" name="password" class="form-control rounded-pill px-3" placeholder="Optional new password">
            </div>
        </div>
        <div class="modal-footer border-0 bg-light rounded-bottom-4">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="bi bi-check-circle me-1"></i> Update Staff</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openEditUserModal(id, fullName, role, phone) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_full_name').value = fullName;
    document.getElementById('edit_phone').value = phone || '';
    const roleSelect = document.getElementById('edit_role');
    if (roleSelect) {
        if (role === 'Admin') role = 'Manager';
        roleSelect.value = role;
    }
    const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    editModal.show();
}

@if(isset($errors) && $errors->any())
document.addEventListener('DOMContentLoaded', function() {
    const addModal = new bootstrap.Modal(document.getElementById('addUserModal'));
    addModal.show();
});
@endif
</script>
@endsection
