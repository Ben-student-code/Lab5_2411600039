<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notifications - FITNESS TRACKER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
            <i class="bi bi-heart-pulse-fill"></i> FITNESS TRACKER
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item me-3">
                    <a class="nav-link position-relative" href="#" id="notificationBell">
                        <i class="bi bi-bell-fill fs-5 text-white"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                              id="notificationCount" style="display:none;">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <span class="navbar-text text-white me-3">
                        Welcome, {{ auth()->user()->name ?? 'User' }}
                    </span>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

{{-- Notification Dropdown --}}
<div id="notificationPanel" class="card shadow-lg border-0"
     style="position:fixed;top:70px;right:20px;width:400px;max-height:500px;overflow-y:auto;display:none;z-index:1050;">
    <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
        <h6 class="mb-0"><i class="bi bi-bell-fill"></i> Notifications</h6>
        <button class="btn btn-sm btn-light" id="markAllRead">
            <i class="bi bi-check2-all"></i> Mark all read
        </button>
    </div>
    <div class="card-body p-0" id="notificationList">
        <p class="text-center text-muted my-4">Loading...</p>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar vh-100 p-3">
            <div class="position-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link shadow-hover" href="{{ route('dashboard') }}">
                            <i class="bi bi-grid-1x2-fill"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link shadow-hover" href="{{ route('inventory.index') }}">
                            <i class="bi bi-box-seam-fill"></i> Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('notifications.index') }}">
                            <i class="bi bi-bell-fill"></i> Notifications
                            <span class="badge bg-danger ms-2" id="sidebarNotifCount" style="display:none;">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link shadow-hover" href="#">
                            <i class="bi bi-person-fill"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link shadow-hover" href="#">
                            <i class="bi bi-gear-fill"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a class="nav-link text-danger shadow-hover" href="#"
                               onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
                <h2><i class="bi bi-bell-fill"></i> All Notifications</h2>
                <button class="btn btn-sm btn-outline-primary" id="markAllReadPage">
                    <i class="bi bi-check2-all"></i> Mark all as read
                </button>
            </div>

            @php
                $allNotifications = auth()->user()->notifications()->orderByDesc('created_at')->limit(50)->get();
                $unreadCount = auth()->user()->unreadNotifications()->count();
            @endphp

            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <span><i class="bi bi-info-circle-fill"></i> Total: <strong>{{ $allNotifications->count() }}</strong></span>
                <span class="badge bg-danger">Unread: {{ $unreadCount }}</span>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    @forelse($allNotifications as $n)
                        <div class="p-3 border-bottom {{ is_null($n->read_at) ? 'bg-light' : '' }}">
                            <div class="d-flex align-items-start">
                                <i class="bi {{ $n->data['icon'] ?? 'bi-info-circle-fill' }} text-{{ $n->data['color'] ?? 'primary' }} fs-4 me-3"></i>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <strong class="d-block mb-1">{{ $n->data['title'] ?? 'Notification' }}</strong>
                                        @if(is_null($n->read_at))
                                            <span class="badge bg-danger">New</span>
                                        @endif
                                    </div>
                                    <p class="text-muted mb-2">{{ $n->data['message'] ?? '' }}</p>
                                    <small class="text-secondary">
                                        <i class="bi bi-clock"></i>
                                        {{ $n->created_at->diffForHumans() }}
                                        ({{ $n->created_at->format('M j, Y g:i A') }})
                                    </small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-bell-slash fs-1 d-block mb-3"></i>
                            <h5>No notifications</h5>
                            <p>You're all caught up!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/notificationBell.js') }}"></script>
<script>
    // Handle "Mark all as read" on this page
    document.getElementById('markAllReadPage')?.addEventListener('click', async () => {
        await fetch('/api/workouts/notifications/read', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            credentials: 'same-origin',
        });
        location.reload();
    });
</script>
</body>
</html>