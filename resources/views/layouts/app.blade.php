<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield("title", "Day Care Centre") | Day Care Centre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background: #f5f7fa; }
        .sidebar { min-height: 100vh; background: #1f2937; }
        .sidebar a { color: #cbd5e1; }
        .sidebar a.active, .sidebar a:hover { color: #fff; background: #374151; }
        .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    </style>
</head>
<body>
<div class="d-flex">
    <nav class="sidebar p-3" style="width: 240px;">
        <h5 class="text-white mb-4"><i class="bi bi-emoji-smile"></i> Day Care Centre</h5>
        <div class="nav flex-column gap-1">
            <a href="{{ route("dashboard") }}" class="nav-link rounded px-3 py-2 {{ request()->routeIs("dashboard") ? "active" : "" }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a href="{{ route("children.index") }}" class="nav-link rounded px-3 py-2 {{ request()->routeIs("children.*") ? "active" : "" }}"><i class="bi bi-people me-2"></i>Children</a>
            <a href="{{ route("attendance.index") }}" class="nav-link rounded px-3 py-2 {{ request()->routeIs("attendance.*") ? "active" : "" }}"><i class="bi bi-calendar-check me-2"></i>Attendance</a>
            <a href="{{ route("billing.index") }}" class="nav-link rounded px-3 py-2 {{ request()->routeIs("billing.*") ? "active" : "" }}"><i class="bi bi-receipt me-2"></i>Billing</a>
            @if(in_array(auth()->user()->role, ["admin","staff"]))
                <a href="{{ route("instruments.index") }}" class="nav-link rounded px-3 py-2 {{ request()->routeIs("instruments.*") ? "active" : "" }}"><i class="bi bi-box-seam me-2"></i>Instruments &amp; Toys</a>
                <a href="{{ route("reports.index") }}" class="nav-link rounded px-3 py-2 {{ request()->routeIs("reports.*") ? "active" : "" }}"><i class="bi bi-file-earmark-pdf me-2"></i>Reports</a>
            @endif
            @if(auth()->user()->role === "admin")
                <a href="{{ route("employees.index") }}" class="nav-link rounded px-3 py-2 {{ request()->routeIs("employees.*") ? "active" : "" }}"><i class="bi bi-person-badge me-2"></i>Employees</a>
                <a href="{{ route("staff.index") }}" class="nav-link rounded px-3 py-2 {{ request()->routeIs("staff.*") ? "active" : "" }}"><i class="bi bi-person-workspace me-2"></i>Staff</a>
            @endif
        </div>
        <hr class="text-secondary">
        <form method="POST" action="{{ route("logout") }}">
            @csrf
            <button class="btn btn-outline-light btn-sm w-100"><i class="bi bi-box-arrow-left me-1"></i>Logout ({{ auth()->user()->name }})</button>
        </form>
    </nav>

    <main class="flex-grow-1 p-4">
        @if(session("success"))
            <div class="alert alert-success">{{ session("success") }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield("content")
    </main>
</div>
</body>
</html>