@extends('layouts.app')

@section('content')
<div class="laravel-hero-banner mb-4 d-flex justify-content-between align-items-center animate-fade-in">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-people me-2 text-info"></i> Patient & Customer Records</h2>
        <p class="text-white-50 mb-0">Manage customer contacts, medical histories, and prescription tracking.</p>
    </div>
    <div>
        <a href="{{ route('patients.create') }}" class="btn-laravel-green text-white shadow text-decoration-none">
            <i class="bi bi-person-plus me-1"></i> + Register New Patient
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
                    <div class="metric-title">Total Patients</div>
                    <div class="metric-value">{{ count($patients) }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-people-fill"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-cyan">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Male Patients</div>
                    <div class="metric-value">{{ $patients->where('gender', 'Male')->count() }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-gender-male"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="metric-widget metric-purple">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="metric-title">Female Patients</div>
                    <div class="metric-value">{{ $patients->where('gender', 'Female')->count() }}</div>
                </div>
                <div class="metric-icon"><i class="bi bi-gender-female"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card-laravel animate-fade-in">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary"><i class="bi bi-person-lines-fill me-2"></i> Patients Directory</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-laravel mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Patient Name</th>
                        <th>Phone Number</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Registered Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $idx => $p)
                        <tr>
                            <td class="text-muted">{{ $idx + 1 }}</td>
                            <td><strong class="text-dark">{{ $p->name }}</strong></td>
                            <td><span class="font-monospace text-muted">{{ $p->phone ?: 'N/A' }}</span></td>
                            <td>{{ $p->age ?: '-' }}</td>
                            <td>
                                @if($p->gender === 'Male')
                                    <span class="badge-laravel-blue">Male</span>
                                @elseif($p->gender === 'Female')
                                    <span class="badge-laravel-pink" style="background:#fce7f3;color:#db2777;padding:4px 8px;border-radius:20px;font-size:0.8rem;">Female</span>
                                @else
                                    <span class="badge-laravel-gray">Other</span>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ $p->created_at ? $p->created_at->format('d M Y') : 'N/A' }}</small></td>
                            <td class="text-end">
                                <a href="{{ route('patients.edit', $p->id) }}" class="btn btn-sm btn-laravel-primary rounded-pill px-3 shadow-sm me-1">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                <form action="{{ route('patients.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this patient?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No patients registered yet. Click "+ Register New Patient" to add one.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
