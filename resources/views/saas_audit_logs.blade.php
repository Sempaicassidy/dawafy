@extends('layouts.app')

@section('content')
<!-- Daawafy Executive SaaS Global Audit Trail View -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-clock-history me-2 text-info"></i> Global Audit Trail & Compliance Logs</h2>
        <p class="text-white-50 mb-0">Track platform security events, administrative changes, and user activities across all pharmacy tenants.</p>
    </div>
    <div>
        <a href="{{ route('saas.dashboard') }}" class="btn btn-outline-light rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> SaaS Dashboard
        </a>
    </div>
</div>

<!-- Audit Trail Table Card -->
<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4 animate-fade-in">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-text me-2 text-primary"></i> System Activity Logs</h5>
        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">Total Recorded: {{ $logs->total() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Timestamp</th>
                        <th>User</th>
                        <th>Pharmacy / Tenant</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th class="pe-4 text-end">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="ps-4 text-nowrap">
                            <span class="fw-semibold text-dark">{{ $log->created_at ? $log->created_at->format('M d, Y') : '—' }}</span>
                            <small class="text-muted d-block">{{ $log->created_at ? $log->created_at->format('H:i:s') : '' }}</small>
                        </td>
                        <td>
                            @if($log->user)
                                <div class="fw-semibold text-dark">{{ $log->user->name ?? $log->user->username }}</div>
                                <code class="text-muted small">{{ $log->user->username }}</code>
                            @else
                                <span class="text-muted fst-italic">System / Unknown</span>
                            @endif
                        </td>
                        <td>
                            @if($log->tenant)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-building me-1"></i> {{ $log->tenant->pharmacy_name ?? 'Branch' }}
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">
                                    <i class="bi bi-globe me-1"></i> Platform SaaS
                                </span>
                            @endif
                        </td>
                        <td>
                            @php
                                $act = strtolower($log->action ?? '');
                                $badgeClass = 'bg-secondary';
                                if (str_contains($act, 'create') || str_contains($act, 'add') || str_contains($act, 'register')) {
                                    $badgeClass = 'bg-success';
                                } elseif (str_contains($act, 'delete') || str_contains($act, 'remove') || str_contains($act, 'deactivate')) {
                                    $badgeClass = 'bg-danger';
                                } elseif (str_contains($act, 'update') || str_contains($act, 'edit') || str_contains($act, 'contract')) {
                                    $badgeClass = 'bg-warning text-dark';
                                } elseif (str_contains($act, 'login') || str_contains($act, 'support')) {
                                    $badgeClass = 'bg-info text-dark';
                                }
                            @endphp
                            <span class="badge {{ $badgeClass }} rounded-pill px-2 py-1">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="text-wrap" style="max-width: 320px;">
                            <span class="text-secondary small">{{ $log->details ?? '—' }}</span>
                        </td>
                        <td class="pe-4 text-end text-nowrap">
                            <code class="text-muted small">{{ $log->ip_address ?? '—' }}</code>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                            Hakuna kumbukumbu za ukaguzi zilizopatikana (No audit logs recorded yet).
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white py-3 px-4 border-top">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">Inaonyesha {{ $logs->firstItem() }} hadi {{ $logs->lastItem() }} kati ya kumbukumbu {{ $logs->total() }}</small>
            <div>
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
