@extends('layouts.app')

@section('content')
<!-- Daawafy Executive SaaS Super Admin Management View -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-shield-lock me-2 text-warning"></i> Platform Super Admin Management</h2>
        <p class="text-white-50 mb-0">Manage global system administrators, security credentials, and platform access control.</p>
    </div>
    <div>
        <button class="btn-laravel-green text-white shadow" data-bs-toggle="modal" data-bs-target="#addSuperAdminModal">
            <i class="bi bi-person-plus-fill me-1"></i> + Add Super Admin
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-check-circle me-2"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger rounded-pill px-4 mb-4 animate-fade-in"><i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}</div>
@endif
@if(isset($errors) && $errors->any())
    <div class="alert alert-danger rounded-3 px-4 mb-4 animate-fade-in">
        <ul class="mb-0">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Quick Metrics / Security Overview -->
<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-md-4">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Total Super Admins</div>
                    <div class="metric-value text-primary">{{ $superAdmins->count() }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-shield-shaded"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Single Session Enforced</div>
                    <div class="metric-value text-success">Enabled</div>
                </div>
                <div class="metric-icon"><i class="bi bi-fingerprint"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-purple">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Your Current Account</div>
                    <div class="metric-value fs-5 mt-1">{{ auth()->user()?->username ?? 'SuperAdmin' }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-person-check"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Super Admin Directory Table Card -->
<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4 animate-fade-in">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill me-2 text-primary"></i> Super Admin Accounts</h5>
        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">Platform Level Governance</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Administrator</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Session Status</th>
                        <th>Last Activity</th>
                        <th>Created</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($superAdmins as $adminItem)
                    @php
                        $admin = is_array($adminItem) ? (object) $adminItem : $adminItem;
                        $adminName = $admin->full_name ?? $admin->name ?? $admin->username ?? 'Super Admin';
                        $adminUsername = $admin->username ?? 'admin';
                        $adminId = $admin->id ?? 0;
                        $adminEmail = $admin->email ?? '—';
                        $adminInitials = strtoupper(substr($adminName ?: $adminUsername, 0, 2));
                        $hasSession = !empty($admin->session_id);
                        $lastAct = $admin->last_activity ?? null;
                        $isRecent = $lastAct && \Carbon\Carbon::parse($lastAct)->diffInMinutes(now()) < 30;
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size: 15px;">
                                    {{ $adminInitials }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $adminName }}</div>
                                    @if($adminId === auth()->id())
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small px-2">You (Current)</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <code class="text-primary fw-semibold">{{ $adminUsername }}</code>
                        </td>
                        <td>
                            {{ $adminEmail }}
                        </td>
                        <td>
                            @if($hasSession)
                                @if($isRecent)
                                    <span class="badge bg-success rounded-pill px-2 py-1"><i class="bi bi-circle-fill me-1 small"></i> Online</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-2 py-1"><i class="bi bi-moon-fill me-1 small"></i> Inactive</span>
                                @endif
                                @if(!empty($admin->last_login_ip))
                                    <small class="text-muted d-block mt-1 font-monospace" style="font-size: 11px;">IP: {{ $admin->last_login_ip }}</small>
                                @endif
                            @else
                                <span class="badge bg-light text-secondary border rounded-pill px-2 py-1">Offline</span>
                            @endif
                        </td>
                        <td>
                            @if(!empty($lastAct))
                                <span class="text-muted small">{{ \Carbon\Carbon::parse($lastAct)->diffForHumans() }}</span>
                            @else
                                <span class="text-muted small">Never</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ !empty($admin->created_at) ? \Carbon\Carbon::parse($admin->created_at)->format('M d, Y') : '—' }}</small>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3" 
                                    onclick="openResetPasswordModal('{{ $adminId }}', '{{ $adminUsername }}', '{{ addslashes($adminName) }}')">
                                    <i class="bi bi-key me-1"></i> Reset Password
                                </button>

                                @if($adminId !== auth()->id())
                                    <form action="{{ route('saas.delete_super_admin', $adminId) }}" method="POST" onsubmit="return confirm('Je, una uhakika unataka kumfuta Super Admin huyu ({{ $adminUsername }})? Hatua hii haiwezi kurudishwa!');" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                            <i class="bi bi-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-sm btn-light border rounded-pill px-3 text-muted" disabled title="Cannot delete your own account">
                                        <i class="bi bi-lock me-1"></i> Active
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Hamna Super Admin mwengine aliyepatikana.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add New Super Admin -->
<div class="modal fade" id="addSuperAdminModal" tabindex="-1" aria-labelledby="addSuperAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('saas.store_super_admin') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <h5 class="modal-title fw-bold text-dark" id="addSuperAdminModalLabel">
                        <i class="bi bi-person-plus-fill me-2 text-primary"></i> Register New Super Admin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <div class="alert alert-warning small rounded-3 mb-3">
                        <i class="bi bi-shield-exclamation me-1"></i> <strong>Tahadhari:</strong> Super Admin ana mamlaka ya juu kabisa ya kusimamia mfumo mzima (maduka yote, watumiaji, na data za SaaS).
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jina Kamili (Full Name) <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control rounded-3" placeholder="Mfano: Cassidy SuperAdmin" required value="{{ old('full_name') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jina la Kuingilia (Username) <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control rounded-3" placeholder="Mfano: superadmin2" required value="{{ old('username') }}">
                        <small class="text-muted">Username lazima iwe ya kipekee.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Barua Pepe (Email) <span class="text-muted">(Optional)</span></label>
                        <input type="email" name="email" class="form-control rounded-3" placeholder="superadmin@haulatech.com" value="{{ old('email') }}">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nenosiri (Password) <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control rounded-3" placeholder="Angalau tarakimu 6" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Thibitisha Nenosiri <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control rounded-3" placeholder="Rudia nenosiri" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Ghairi</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-check-circle me-1"></i> Sajili Super Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="resetPasswordForm" method="POST" action="">
                @csrf
                <div class="modal-header border-bottom px-4 pt-4 pb-3">
                    <h5 class="modal-title fw-bold text-dark" id="resetPasswordModalLabel">
                        <i class="bi bi-key-fill me-2 text-warning"></i> Badili Nenosiri la Super Admin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <p class="text-muted mb-3">
                        Unabadilisha nenosiri la: <strong id="resetTargetName" class="text-dark"></strong> (<code id="resetTargetUsername" class="text-primary"></code>)
                    </p>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nenosiri Jipya <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control rounded-3" placeholder="Angalau tarakimu 6" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Thibitisha Nenosiri <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control rounded-3" placeholder="Rudia nenosiri jipya" required>
                        </div>
                    </div>
                    <small class="text-muted d-block">
                        Kumbuka: Mtumiaji akibadilishiwa nenosiri atatolewa (logged out) kwenye vifaa vyote hadi aingie tena na nenosiri hili jipya.
                    </small>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Ghairi</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">
                        <i class="bi bi-check-circle me-1"></i> Hifadhi Nenosiri Jipya
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openResetPasswordModal(userId, username, fullName) {
    document.getElementById('resetTargetName').innerText = fullName || username;
    document.getElementById('resetTargetUsername').innerText = username;
    document.getElementById('resetPasswordForm').action = "/saas-dashboard/super-admins/" + userId + "/reset-password";
    var modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    modal.show();
}
</script>
@endsection
