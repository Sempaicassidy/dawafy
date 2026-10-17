@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-shield-plus me-2 text-info"></i> Health Insurance & Claims Management</h2>
        <p class="text-white-50 mb-0">Manage insurance corporate schemes, NHIF, Jubilee, and track pending claims reimbursement.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('insurance.create_provider') }}" class="btn-laravel-primary shadow text-decoration-none">
            <i class="bi bi-plus-circle me-1"></i> + Add Provider
        </a>
        <a href="{{ route('insurance.create_claim') }}" class="btn-laravel-green text-white shadow text-decoration-none">
            <i class="bi bi-file-earmark-plus me-1"></i> + File Claim
        </a>
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
                    <div class="metric-title">Insurance Providers</div>
                    <div class="metric-value text-primary">{{ count($providers) }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-shield-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-yellow">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Pending Claims</div>
                    <div class="metric-value text-warning">{{ $claims->where('status', 'Pending')->count() }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-hourglass-top"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-green">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Approved Claims</div>
                    <div class="metric-value text-success">{{ $claims->where('status', 'Approved')->count() }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-check2-all"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 animate-fade-in">
    <!-- Providers List -->
    <div class="col-md-4">
        <div class="card-laravel h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-primary fw-bold"><i class="bi bi-building me-1"></i> Insurance Providers</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($providers as $p)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <strong class="d-block text-dark">{{ $p->name }}</strong>
                                <small class="text-muted">{{ $p->contact_person ?: $p->phone ?: 'Corporate Scheme' }}</small>
                            </div>
                            <span class="badge-laravel-blue">Active</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-4 text-muted">No providers added yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Claims Table -->
    <div class="col-md-8">
        <div class="card-laravel h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 text-primary fw-bold"><i class="bi bi-receipt-cutoff me-1"></i> Filed Insurance Claims</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-laravel mb-0">
                        <thead>
                            <tr>
                                <th>Claim #</th>
                                <th>Provider</th>
                                <th>Amount (TZS)</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($claims as $c)
                                <tr>
                                    <td><strong class="font-monospace text-dark">{{ $c->claim_number }}</strong></td>
                                    <td>{{ $c->insuranceProvider?->name ?? 'Insurance' }}</td>
                                    <td class="fw-bold text-primary">{{ number_format($c->amount) }}</td>
                                    <td>
                                        @if($c->status === 'Approved')
                                            <span class="badge-laravel-green"><i class="bi bi-check-circle me-1"></i> Approved</span>
                                        @elseif($c->status === 'Rejected')
                                            <span class="badge-laravel-danger">Rejected</span>
                                        @else
                                            <span class="badge-laravel-yellow"><i class="bi bi-clock me-1"></i> Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($c->status === 'Pending')
                                            <form action="{{ route('insurance.approve_claim', $c->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-laravel-green text-white rounded-pill px-2 me-1">Approve</button>
                                            </form>
                                            <form action="{{ route('insurance.reject_claim', $c->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2">Reject</button>
                                            </form>
                                        @else
                                            <span class="text-muted small">Processed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No insurance claims filed yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
