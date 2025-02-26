<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Driver</th>
                <th>Contact</th>
                <th>Status</th>
                <th>Last Active</th>
                <th>Performance (30d)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($drivers as $driver)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-3 bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                <span class="text-primary fw-medium">{{ strtoupper(substr($driver->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $driver->name }}</h6>
                                <small class="text-muted">ID: {{ $driver->id }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>
                            <div class="text-body">{{ $driver->email }}</div>
                            <small class="text-muted">{{ $driver->phone_number }}</small>
                        </div>
                    </td>
                    <td>
                        <div class="badge {{ 
                            $driver->status === 'active' ? 'bg-success' : 
                            ($driver->status === 'suspended' ? 'bg-warning' : 'bg-danger') 
                        }} rounded-pill">
                            <i class="bx {{ 
                                $driver->status === 'active' ? 'bx-check' : 
                                ($driver->status === 'suspended' ? 'bx-pause' : 'bx-x') 
                            }} me-1"></i>
                            {{ ucfirst($driver->status) }}
                        </div>
                    </td>
                    <td>
                        @if($driver->last_location_update)
                            <div data-bs-toggle="tooltip" title="{{ $driver->last_location_update->format('M d, Y H:i') }}">
                                {{ $driver->last_location_update->diffForHumans() }}
                            </div>
                        @else
                            <span class="text-muted">Never</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-center mb-1">
                                <div class="flex-grow-1 me-2" style="width: 100px;">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{ 
                                            $driver->performance['success_rate'] >= 70 ? 'bg-success' : 
                                            ($driver->performance['success_rate'] >= 40 ? 'bg-warning' : 'bg-danger') 
                                        }}" 
                                             role="progressbar" 
                                             style="width: {{ $driver->performance['success_rate'] }}%"
                                             aria-valuenow="{{ $driver->performance['success_rate'] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                                <span class="small {{ 
                                    $driver->performance['success_rate'] >= 70 ? 'text-success' : 
                                    ($driver->performance['success_rate'] >= 40 ? 'text-warning' : 'text-danger') 
                                }}">
                                    {{ number_format($driver->performance['success_rate'], 1) }}%
                                </span>
                            </div>
                            <div class="small text-muted">
                                {{ $driver->performance['completed_count'] }}/{{ $driver->performance['total_count'] }} deliveries
                                @if($driver->performance['on_time_rate'] > 0)
                                    • {{ number_format($driver->performance['on_time_rate'], 1) }}% on time
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.drivers.show', $driver) }}" 
                               class="btn btn-sm btn-info" 
                               data-bs-toggle="tooltip" 
                               title="View Details">
                                <i class="bx bx-show"></i>
                            </a>
                            <a href="{{ route('admin.drivers.edit', $driver) }}" 
                               class="btn btn-sm btn-primary"
                               data-bs-toggle="tooltip" 
                               title="Edit Driver">
                                <i class="bx bx-edit"></i>
                            </a>
                            <button type="button" 
                                    class="btn btn-sm btn-danger" 
                                    data-bs-toggle="tooltip" 
                                    title="Delete Driver"
                                    onclick="confirmDelete('{{ $driver->id }}')">
                                <i class="bx bx-trash"></i>
                            </button>
                            <form id="delete-form-{{ $driver->id }}" 
                                  action="{{ route('admin.drivers.destroy', $driver) }}" 
                                  method="POST" 
                                  class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="text-muted">
                            <i class="bx bx-user-x display-4"></i>
                            <p class="mt-2">No drivers found</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $drivers->links() }}
</div>
