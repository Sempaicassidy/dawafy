@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-shield-slash me-2 text-warning"></i> Inventory Quarantine & Defect Control</h2>
        <p class="text-white-50 mb-0">Isolate damaged, contaminated, recalled, or expired pharmaceutical batches.</p>
    </div>
    <div>
        <a href="{{ route('quarantine.create') }}" class="btn-laravel-danger text-white shadow text-decoration-none">
            <i class="bi bi-exclamation-diamond me-1"></i> + Quarantine Batch
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-pill px-4 mb-4 animate-fade-in">
        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
    </div>
@endif

<div class="card-laravel animate-fade-in">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0 text-danger"><i class="bi bi-shield-x me-2"></i> Quarantined Medicine Batches</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Medicine & Batch</th>
                        <th>Reason for Quarantine</th>
                        <th>Quarantined Date</th>
                        <th>Action By</th>
                        <th>Notes</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quarantined as $idx => $q)
                        <tr>
                            <td class="text-muted">{{ $idx + 1 }}</td>
                            <td>
                                <strong class="text-dark d-block">{{ $q->batch?->drug?->name ?? 'Medicine' }}</strong>
                                <small class="text-muted font-monospace">Batch: {{ $q->batch?->batch_number ?? 'N/A' }}</small>
                            </td>
                            <td><span class="badge-laravel-danger">{{ $q->reason }}</span></td>
                            <td>{{ $q->quarantine_date ? \Carbon\Carbon::parse($q->quarantine_date)->format('d M Y') : 'N/A' }}</td>
                            <td><small class="text-muted">{{ $q->actionBy?->name ?? 'Staff' }}</small></td>
                            <td><small class="text-muted">{{ $q->notes ?: '-' }}</small></td>
                            <td class="text-end">
                                <form action="{{ route('quarantine.release', $q->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Release this batch back to stock?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-laravel-green text-white rounded-pill px-3 shadow-sm">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Release to Stock
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No medicines currently quarantined. All stock verified.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
