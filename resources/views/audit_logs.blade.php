@extends('layouts.app')

@section('content')
<!-- Daawafy Executive Store Audit Logs View -->
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-clock-history me-2 text-danger"></i> Store Activity & Audit Trail</h2>
        <p class="text-white-50 mb-0">Confidential log of cashier and staff actions, price adjustments, voids, logins, and system transactions.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-light rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>
</div>

<!-- Audit Trail Table Card -->
<div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4 animate-fade-in">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-lock-fill me-2 text-primary"></i> Confidential Activity History</h5>
            <small class="text-muted">Immutable forensic audit logs for fraud detection and operations accountability</small>
        </div>
        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill">Total Recorded: {{ $logs->total() }} events</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Timestamp</th>
                        <th>Staff Member</th>
                        <th>Action Performed</th>
                        <th>Details & Reference</th>
                        <th class="pe-4 text-end">Workstation / IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="ps-4 text-nowrap">
                            <span class="fw-semibold text-dark">{{ $log->created_at ? $log->created_at->format('M d, Y') : '—' }}</span>
                            <small class="text-muted d-block font-monospace">{{ $log->created_at ? $log->created_at->format('H:i:s') : '' }} ({{ $log->created_at ? $log->created_at->diffForHumans() : '' }})</small>
                        </td>
                        <td>
                            @if($log->user)
                                <div class="fw-semibold text-dark">{{ $log->user->full_name ?: $log->user->username }}</div>
                                <code class="text-muted small">@<?= $log->user->username ?> (<?= $log->user->role?->name ?? 'Staff' ?>)</code>
                            @else
                                <span class="text-muted fst-italic">System Automated Service</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $act = strtolower($log->action ?? '');
                                $badgeClass = 'bg-secondary';
                                if (str_contains($act, 'login')) $badgeClass = 'bg-primary';
                                elseif (str_contains($act, 'logout')) $badgeClass = 'bg-secondary';
                                elseif (str_contains($act, 'delete') || str_contains($act, 'void') || str_contains($act, 'cancel')) $badgeClass = 'bg-danger';
                                elseif (str_contains($act, 'sale') || str_contains($act, 'checkout')) $badgeClass = 'bg-success';
                                elseif (str_contains($act, 'update') || str_contains($act, 'edit') || str_contains($act, 'price')) $badgeClass = 'bg-warning text-dark';
                            @endphp
                            <span class="badge {{ $badgeClass }} rounded-pill px-3 py-1">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td>
                            <span class="text-dark">{{ $log->details ?: 'Standard operational event' }}</span>
                        </td>
                        <td class="pe-4 text-end">
                            <span class="badge bg-light text-dark border font-monospace">{{ $log->ip_address ?: '127.0.0.1' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-shield-check text-success fs-1 d-block mb-2"></i>
                            No activity audit logs recorded yet for this pharmacy.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center">
        <small class="text-muted">Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} logs</small>
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
