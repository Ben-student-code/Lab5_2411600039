<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - FITNESS TRACKER</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">
            <i class="bi bi-heart-pulse-fill"></i> FITNESS TRACKER
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
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
                        Welcome, <span id="userName">{{ auth()->user()->name ?? 'User' }}</span>
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

{{-- Notification Dropdown Panel --}}
<div id="notificationPanel" class="card shadow-lg border-0"
     style="position:fixed;top:70px;right:20px;width:400px;max-height:500px;overflow-y:auto;display:none;z-index:1050;">
    <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
        <h6 class="mb-0"><i class="bi bi-bell-fill"></i> Notifications</h6>
        <button class="btn btn-sm btn-light" id="markAllRead">
            <i class="bi bi-check2-all"></i> Mark all read
        </button>
    </div>
    <div class="card-body p-0" id="notificationList">
        <p class="text-center text-muted my-4">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
            Loading...
        </p>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar vh-100 p-3">
            <div class="position-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('dashboard') }}">
                            <i class="bi bi-grid-1x2-fill"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link shadow-hover" href="{{ route('inventory.index') }}">
                            <i class="bi bi-box-seam-fill"></i> Inventory
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link shadow-hover" href="{{ route('notifications.index') }}">
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
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h2 id="greeting">Good Morning, User!</h2>
                 <div>
                    <button class="btn btn-outline-secondary btn-sm" id="exportBtn">
                        <i class="bi bi-download"></i> Export CSV
                    </button>
                </div>
            </div>

            <div id="alertContainer" class="mb-4"></div>

            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card stat-card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-muted">Total Workouts</h5>
                            <h2 class="card-text fw-bold text-primary" id="stat1-value">0</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-muted">Total Calories</h5>
                            <h2 class="card-text fw-bold text-success" id="stat2-value">0</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-muted">Completion Rate</h5>
                            <h2 class="card-text fw-bold text-info" id="stat3-value">0%</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-muted">Active Minutes</h5>
                            <h2 class="card-text fw-bold text-warning" id="stat4-value">0</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-bar-chart-fill"></i> Workout Value by Category</h5>
                        </div>
                        <div class="card-body"><canvas id="categoryChart" height="200"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-pie-chart-fill"></i> Workout Status Distribution</h5>
                        </div>
                        <div class="card-body"><canvas id="statusChart" height="200"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-trophy-fill"></i> Top Workouts by Value</h5>
                        </div>
                        <div class="card-body"><canvas id="topWorkoutsChart" height="200"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-graph-up-arrow"></i> Weekly Progress</h5>
                        </div>
                        <div class="card-body"><canvas id="weeklyChart" height="200"></canvas></div>
                    </div>
                </div>
            </div>

            {{-- Filters & Search REMOVED — now on /inventory --}}

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Activity</h5>
                    <span class="badge bg-info" id="activityCount">0 activities</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Activity</th>
                                    <th>Type</th>
                                    <th>Intensity</th>
                                    <th>Status</th>
                                    <th>Calories</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody id="activityTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/dataManager.js') }}"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
<script src="{{ asset('js/notificationBell.js') }}"></script>
</body>
</html>