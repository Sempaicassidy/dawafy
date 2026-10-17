@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-clock-history me-2 text-info"></i> Cashier Shift Management</h2>
        <p class="text-white-50 mb-0">Track cash drawers, opening balances, shift handovers, and closing reconciliations.</p>
    </div>
    <div>
        <button class="btn-laravel-green text-white shadow" data-bs-toggle="modal" data-bs-target="#startShiftModal">
            <i class="bi bi-play-circle me-1"></i> Start New Shift
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-pill px-4 mb-4 animate-fade-in">
        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
    </div>
@endif

<div class="row g-3 mb-4 animate-fade-in">
    <div class="col-md-4">
        <div class="metric-widget metric-blue">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Total Recorded Shifts</div>
                    <div class="metric-value text-primary">{{ count($shifts) }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-clock"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Active Shifts</div>
                    <div class="metric-value text-success">{{ $shifts->whereNull('end_time')->count() }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-person-workspace"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-purple">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Closed Shifts</div>
                    <div class="metric-value">{{ $shifts->whereNotNull('end_time')->count() }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-check2-circle"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card-laravel animate-fade-in">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0 text-primary"><i class="bi bi-list-check me-2"></i> Shift Audit Logs</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cashier / User</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Opening Cash (TZS)</th>
                        <th>Closing Cash (TZS)</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $idx => $s)
                        <tr>
                            <td class="text-muted">{{ $idx + 1 }}</td>
                            <td><strong class="text-dark">{{ $s->user?->name ?? 'Staff Cashier' }}</strong></td>
                            <td>{{ $s->start_time ? \Carbon\Carbon::parse($s->start_time)->format('d M Y, H:i') : 'N/A' }}</td>
                            <td>{{ $s->end_time ? \Carbon\Carbon::parse($s->end_time)->format('d M Y, H:i') : '-' }}</td>
                            <td class="fw-bold text-primary">{{ number_format($s->opening_balance ?? 0) }}</td>
                            <td class="fw-bold text-success">{{ $s->closing_balance !== null ? number_format($s->closing_balance) : '-' }}</td>
                            <td>
                                @if(!$s->end_time)
                                    <span class="badge-laravel-green"><i class="bi bi-record-circle me-1"></i> Running</span>
                                @else
                                    <span class="badge-laravel-gray">Closed</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(!$s->end_time)
                                    <form action="{{ route('shifts.end', $s->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="closing_balance" value="{{ $s->opening_balance ?? 0 }}">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm">
                                            <i class="bi bi-stop-circle me-1"></i> End Shift
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small"><i class="bi bi-shield-check text-success me-1"></i> Reconciled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No shifts logged yet. Click "Start New Shift" to begin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Start Shift -->
<div class="modal fade" id="startShiftModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-clock me-2"></i> Start Work Shift</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('shifts.start') }}">
        @csrf
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="fw-bold text-muted small mb-1">Opening Cash Drawer Balance (TZS) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="opening_balance" class="form-control rounded-pill px-3" placeholder="e.g. 50000" required>
            </div>
        </div>
        <div class="modal-footer border-0 bg-light">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn-laravel-primary rounded-pill px-4">Start Shift</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
