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
            <ul class="navbar-nav ms-auto">
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
                    <span class="badge bg-success me-2" id="liveIndicator">
                        <i class="bi bi-circle-fill"></i> Live
                    </span>
                    <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#addWorkoutModal">
                        <i class="bi bi-plus-circle"></i> Add Workout
                    </button>
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

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-funnel-fill"></i> Filters & Search</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search workouts by name...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select id="typeFilter" class="form-select">
                                <option value="all">All Types</option>
                                <option value="cardio">Cardio</option>
                                <option value="strength">Strength</option>
                                <option value="flexibility">Flexibility</option>
                                <option value="hiit">HIIT</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="intensityFilter" class="form-select">
                                <option value="all">All Intensity</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="statusFilter" class="form-select">
                                <option value="all">All Status</option>
                                <option value="Completed">Completed</option>
                                <option value="Ongoing">Ongoing</option>
                                <option value="Failed">Failed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="dateRangeFilter" class="form-select">
                                <option value="all">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-secondary" id="resetFiltersBtn">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset All Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

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
                                    <th>Actions</th>
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

<!-- ============================ -->
<!-- Add Workout Modal            -->
<!-- ============================ -->
<div class="modal fade" id="addWorkoutModal" tabindex="-1" aria-labelledby="addWorkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="addWorkoutForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addWorkoutModalLabel">
                        <i class="bi bi-plus-circle"></i> Add New Workout
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="addWorkoutFeedback"></div>

                    <div class="mb-3">
                        <label for="new_activity" class="form-label">Activity Name</label>
                        <input type="text" class="form-control" id="new_activity" name="activity"
                               placeholder="e.g. Morning Jog" required maxlength="100">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_type" class="form-label">Type</label>
                            <select class="form-select" id="new_type" name="type" required>
                                <option value="cardio">Cardio</option>
                                <option value="strength">Strength</option>
                                <option value="flexibility">Flexibility</option>
                                <option value="hiit">HIIT</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_intensity" class="form-label">Intensity</label>
                            <select class="form-select" id="new_intensity" name="intensity" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="new_calories" class="form-label">Calories Burned</label>
                            <input type="number" class="form-control" id="new_calories" name="calories"
                                   min="0" max="5000" value="150" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="new_duration" class="form-label">Duration (min)</label>
                            <input type="number" class="form-control" id="new_duration" name="duration"
                                   min="1" max="600" value="30" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_status" class="form-label">Status</label>
                        <select class="form-select" id="new_status" name="status" required>
                            <option value="Completed" selected>Completed</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Failed">Failed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="new_date" class="form-label">Date & Time</label>
                        <input type="datetime-local" class="form-control" id="new_date" name="date" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_notes" class="form-label">Notes (optional)</label>
                        <textarea class="form-control" id="new_notes" name="notes" rows="2"
                                  placeholder="e.g. 3 sets of 20 reps" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveWorkoutBtn">
                        <i class="bi bi-save"></i> Save Workout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/dataManager.js') }}"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>