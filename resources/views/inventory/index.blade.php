<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inventory - FITNESS TRACKER</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

{{-- ==================== NAVBAR ==================== --}}
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
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

{{-- ==================== NOTIFICATION DROPDOWN ==================== --}}
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
            <i class="bi bi-inbox fs-2 d-block mb-2"></i> Loading...
        </p>
    </div>
</div>

<div class="container-fluid">
    <div class="row">

        {{-- ==================== SIDEBAR ==================== --}}
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar vh-100 p-3">
            <div class="position-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link shadow-hover" href="{{ route('dashboard') }}">
                            <i class="bi bi-grid-1x2-fill"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('inventory.index') }}">
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

        {{-- ==================== MAIN CONTENT ==================== --}}
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

            <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
                <h2><i class="bi bi-box-seam-fill"></i> Inventory</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inventoryAddModal">
                    <i class="bi bi-plus-circle"></i> Add Item
                </button>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill"></i>
                Your logged workouts below are treated as <strong>inventory items</strong>
                to demonstrate Lab 6 concepts (Inventory Management meets Fitness Tracker).
            </div>

            {{-- Filters & Search --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-funnel-fill"></i> Filters & Search</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="invSearchInput"
                                       placeholder="Search items by name...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select id="invTypeFilter" class="form-select">
                                <option value="all">All Types</option>
                                <option value="cardio">Cardio</option>
                                <option value="strength">Strength</option>
                                <option value="flexibility">Flexibility</option>
                                <option value="hiit">HIIT</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="invIntensityFilter" class="form-select">
                                <option value="all">All Intensity</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="invStatusFilter" class="form-select">
                                <option value="all">All Status</option>
                                <option value="Completed">Completed</option>
                                <option value="Ongoing">Ongoing</option>
                                <option value="Failed">Failed</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="invDateRangeFilter" class="form-select">
                                <option value="all">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-secondary" id="invResetFiltersBtn">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset All Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-check"></i> Items (Workouts)</h5>
                    <span class="badge bg-info" id="inventoryCount">0 items</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Item (Workout)</th>
                                    <th>Category (Type)</th>
                                    <th>Quantity (Duration)</th>
                                    <th>Unit Value (Calories)</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="inventoryTableBody">
                                <tr><td colspan="7" class="text-center text-muted py-4">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

{{-- ============================ --}}
{{-- Add Workout (Item) Modal     --}}
{{-- ============================ --}}
<div class="modal fade" id="inventoryAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="inventoryAddForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle"></i> Add New Item
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="inv_addFeedback"></div>

                    <div class="mb-3">
                        <label for="inv_new_activity" class="form-label">Item Name (Activity)</label>
                        <input type="text" class="form-control" id="inv_new_activity"
                               placeholder="e.g. Morning Jog" required maxlength="100">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="inv_new_type" class="form-label">Category (Type)</label>
                            <select class="form-select" id="inv_new_type" required>
                                <option value="cardio">Cardio</option>
                                <option value="strength">Strength</option>
                                <option value="flexibility">Flexibility</option>
                                <option value="hiit">HIIT</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="inv_new_intensity" class="form-label">Intensity</label>
                            <select class="form-select" id="inv_new_intensity" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="inv_new_calories" class="form-label">Unit Value (Calories)</label>
                            <input type="number" class="form-control" id="inv_new_calories"
                                   min="0" max="5000" value="150" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="inv_new_duration" class="form-label">Quantity (Duration min)</label>
                            <input type="number" class="form-control" id="inv_new_duration"
                                   min="1" max="600" value="30" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="inv_new_status" class="form-label">Status</label>
                        <select class="form-select" id="inv_new_status" required>
                            <option value="Completed" selected>Completed</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Failed">Failed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="inv_new_date" class="form-label">Date & Time</label>
                        <input type="datetime-local" class="form-control" id="inv_new_date" required>
                    </div>

                    <div class="mb-3">
                        <label for="inv_new_notes" class="form-label">Notes (optional)</label>
                        <textarea class="form-control" id="inv_new_notes" rows="2"
                                  placeholder="e.g. 3 sets of 20 reps" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="inv_saveBtn">
                        <i class="bi bi-save"></i> Save Workout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================ --}}
{{-- Edit Workout (Item) Modal    --}}
{{-- ============================ --}}
<div class="modal fade" id="inventoryEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="inventoryEditForm">
                <input type="hidden" id="inv_edit_id">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil"></i> Edit Item
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="inv_editFeedback"></div>

                    <div class="mb-3">
                        <label for="inv_edit_activity" class="form-label">Item Name (Activity)</label>
                        <input type="text" class="form-control" id="inv_edit_activity" required maxlength="100">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="inv_edit_type" class="form-label">Category (Type)</label>
                            <select class="form-select" id="inv_edit_type" required>
                                <option value="cardio">Cardio</option>
                                <option value="strength">Strength</option>
                                <option value="flexibility">Flexibility</option>
                                <option value="hiit">HIIT</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="inv_edit_intensity" class="form-label">Intensity</label>
                            <select class="form-select" id="inv_edit_intensity" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="inv_edit_calories" class="form-label">Unit Value (Calories)</label>
                            <input type="number" class="form-control" id="inv_edit_calories" min="0" max="5000" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="inv_edit_duration" class="form-label">Quantity (Duration min)</label>
                            <input type="number" class="form-control" id="inv_edit_duration" min="1" max="600" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="inv_edit_status" class="form-label">Status</label>
                        <select class="form-select" id="inv_edit_status" required>
                            <option value="Completed">Completed</option>
                            <option value="Ongoing">Ongoing</option>
                            <option value="Failed">Failed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="inv_edit_date" class="form-label">Date & Time</label>
                        <input type="datetime-local" class="form-control" id="inv_edit_date" required>
                    </div>

                    <div class="mb-3">
                        <label for="inv_edit_notes" class="form-label">Notes (optional)</label>
                        <textarea class="form-control" id="inv_edit_notes" rows="2" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning" id="inv_editSaveBtn">
                        <i class="bi bi-save"></i> Update Workout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/notificationBell.js') }}"></script>
<script src="{{ asset('js/inventory.js') }}"></script>
</body>
</html>