@extends('layouts.app')
@section('title', 'Children')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Children</h3>
        @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'staff']))
            <a href="{{ route('children.create') }}" class="btn btn-primary btn-sm">
                + Enroll Child
            </a>
        @endif
    </div>

    <form method="GET" action="{{ route('children.index') }}" class="mb-3">
        <div class="input-group" style="max-width: 400px;">
            <input type="text" name="search" class="form-control"
                   placeholder="Search by child name..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary">Search</button>
            @if(request('search'))
                <a href="{{ route('children.index') }}" class="btn btn-outline-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Parent/Employee</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($children as $child)
                    <tr>
                        <td>{{ $children->firstItem() + $loop->index }}</td>
                        <td>
                            @if($child->photo_url)
                                <img src="{{ $child->photo_url }}" alt=""
                                     class="rounded-circle me-2" loading="lazy"
                                     style="width:32px;height:32px;object-fit:cover;">
                            @endif
                            {{ $child->name }}
                        </td>
                        <td>{{ $child->age ?? '-' }}</td>
                        <td>{{ $child->employee->name ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $child->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($child->status ?? 'unknown') }}
                            </span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('children.show', $child) }}"
                               class="btn btn-sm btn-outline-secondary">View</a>

                            @if(in_array(auth()->user()->role, ['admin', 'staff']))
                                <a href="{{ route('children.edit', $child) }}"
                                   class="btn btn-sm btn-outline-primary">Edit</a>

                                @if(auth()->user()->role === 'admin')
                                    <form action="{{ route('children.destroy', $child) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No children found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $children->withQueryString()->links() }}
    </div>
@endsection
