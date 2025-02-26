<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($drivers as $driver)
                <tr>
                    <td>{{ $driver->name }}</td>
                    <td>{{ $driver->email }}</td>
                    <td>{{ $driver->phone_number }}</td>
                    <td>
                        <span class="badge {{ $driver->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                            {{ ucfirst($driver->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.drivers.show', $driver) }}" class="btn btn-sm btn-info me-1">
                            <i class="mdi mdi-eye"></i>
                        </a>
                        <a href="{{ route('admin.drivers.edit', $driver) }}" class="btn btn-sm btn-primary me-1">
                            <i class="mdi mdi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.drivers.destroy', $driver) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this driver?')">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $drivers->links() }}
</div>
