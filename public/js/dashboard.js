/**
 * Dashboard Controller
 * Part 4-5: Interactive Data Visualizations and Dashboard Interactivity
 *
 * Laravel + Breeze version:
 *  - No localStorage auth check (Laravel middleware handles it)
 *  - Logout handled by form POST to route('logout')
 *  - Real-time simulation REMOVED
 */

class Dashboard {
    constructor() {
        this.dataManager = dataManager;
        this.charts = {};
    }

    /**
     * Initialize the dashboard
     */
    async init() {
        // Setup user (username rendered server-side by Blade)
        const username = document.getElementById('userName')?.textContent.trim() || 'User';

        // Load data from API
        await this.dataManager.initializeData();
        this.dataManager.applyFilters();

        // Setup all components
        this.setupGreeting(username);
        this.updateStatistics();
        this.renderAlerts();
        this.renderCharts();
        this.updateTable();
        this.setupEventListeners();
        this.setupAddWorkoutModal();
        this.setupExport();

        // Update live indicator
        this.updateLiveIndicator('Live', true);
    }

    /**
     * Setup greeting based on time of day
     */
    setupGreeting(username) {
        const greetingElement = document.getElementById('greeting');
        if (!greetingElement) return;

        const hour = new Date().getHours();
        let timeOfDay = '';
        if (hour >= 5 && hour < 12) timeOfDay = 'Good Morning';
        else if (hour >= 12 && hour < 17) timeOfDay = 'Good Afternoon';
        else if (hour >= 17 && hour < 21) timeOfDay = 'Good Evening';
        else timeOfDay = 'Good Night';

        greetingElement.textContent = `${timeOfDay}, ${username}!`;
    }

    /**
     * Update live indicator
     */
    updateLiveIndicator(message = 'Live', isActive = true) {
        const indicator = document.getElementById('liveIndicator');
        if (!indicator) return;

        if (isActive) {
            indicator.className = 'badge bg-success me-2';
            indicator.innerHTML = `<i class="bi bi-circle-fill"></i> ${message}`;
        } else {
            indicator.className = 'badge bg-secondary me-2';
            indicator.innerHTML = `<i class="bi bi-circle"></i> ${message}`;
        }
    }

    /**
     * Update statistics cards
     */
    updateStatistics() {
        const stats = this.dataManager.getWorkoutStatistics();

        const statConfigs = [
            { id: 'stat1', value: stats.totalWorkouts, suffix: ' 💪', color: 'text-primary' },
            { id: 'stat2', value: stats.totalCalories.toLocaleString(), suffix: ' 🔥', color: 'text-success' },
            { id: 'stat3', value: stats.completionRate, suffix: '% ✅', color: 'text-info' },
            { id: 'stat4', value: stats.totalDuration, suffix: ' min ⏱️', color: 'text-warning' }
        ];

        statConfigs.forEach((config, index) => {
            const valueEl = document.getElementById(`stat${index + 1}-value`);
            if (valueEl) {
                valueEl.textContent = config.value + config.suffix;
                valueEl.className = `card-text fw-bold ${config.color}`;
            }
        });
    }

    /**
     * Render alerts for failed workouts
     */
    renderAlerts() {
        const container = document.getElementById('alertContainer');
        if (!container) return;

        const failedWorkouts = this.dataManager.getFailedWorkouts();

        if (failedWorkouts.length === 0) {
            container.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i> 
                    All workouts are on track! No failed workouts to report.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            return;
        }

        let alertHtml = `
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Attention Required!</h5>
                <p>The following workouts need review:</p>
                <ul class="mb-0">
        `;

        failedWorkouts.forEach(w => {
            alertHtml += `
                <li>
                    <strong>${w.activity}</strong> - ${w.date} 
                    <span class="badge bg-danger">Failed</span>
                    ${w.notes ? `(${w.notes})` : ''}
                </li>
            `;
        });

        alertHtml += `
                </ul>
                <hr>
                <p class="mb-0">Please review and adjust your workout plan accordingly.</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        container.innerHTML = alertHtml;
    }

    /**
     * Render all charts
     */
    renderCharts() {
        this.renderCategoryChart();
        this.renderStatusChart();
        this.renderTopWorkoutsChart();
        this.renderWeeklyChart();
    }

    /**
     * Chart 1: Workout Value by Category
     */
    renderCategoryChart() {
        const ctx = document.getElementById('categoryChart');
        if (!ctx) return;

        const categoryData = this.dataManager.getCategorySummary();

        const labels = categoryData.map(c => c.name.charAt(0).toUpperCase() + c.name.slice(1));
        const calories = categoryData.map(c => c.calories);
        const durations = categoryData.map(c => c.duration);
        const counts = categoryData.map(c => c.count);

        if (this.charts.category) {
            this.charts.category.destroy();
        }

        this.charts.category = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Calories Burned',
                        data: calories,
                        backgroundColor: [
                            'rgba(84, 9, 218, 0.7)',
                            'rgba(78, 113, 255, 0.7)',
                            'rgba(141, 216, 255, 0.7)',
                            'rgba(248, 114, 18, 0.7)'
                        ],
                        borderColor: ['#5409DA', '#4E71FF', '#8DD8FF', '#f87212'],
                        borderWidth: 2,
                        borderRadius: 4
                    },
                    {
                        label: 'Duration (min)',
                        data: durations,
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.5)',
                            'rgba(23, 162, 184, 0.5)',
                            'rgba(255, 193, 7, 0.5)',
                            'rgba(220, 53, 69, 0.5)'
                        ],
                        borderColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545'],
                        borderWidth: 2,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 11 }, boxWidth: 15, padding: 10 }
                    },
                    tooltip: {
                        callbacks: {
                            afterBody: function (context) {
                                const index = context[0].dataIndex;
                                return `Workouts: ${counts[index]}`;
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    /**
     * Chart 2: Workout Status Distribution
     */
    renderStatusChart() {
        const ctx = document.getElementById('statusChart');
        if (!ctx) return;

        const distribution = this.dataManager.getStatusDistribution();

        const colors = {
            Completed: '#28a745',
            Ongoing: '#17a2b8',
            Failed: '#dc3545'
        };

        const labels = Object.keys(distribution);
        const data = Object.values(distribution);

        if (this.charts.status) {
            this.charts.status.destroy();
        }

        this.charts.status = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: labels.map(label => colors[label] || '#6c757d'),
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15, usePointStyle: true, font: { size: 12 } }
                    }
                },
                cutout: '55%'
            }
        });
    }

    /**
     * Chart 3: Top Workouts by Value
     */
    renderTopWorkoutsChart() {
        const ctx = document.getElementById('topWorkoutsChart');
        if (!ctx) return;

        const topWorkouts = this.dataManager.getTopWorkouts(5);

        const labels = topWorkouts.map(w => w.activity);
        const values = topWorkouts.map(w => w.value);
        const colors = ['#5409DA', '#4E71FF', '#8DD8FF', '#f87212', '#28a745'];

        if (this.charts.topWorkouts) {
            this.charts.topWorkouts.destroy();
        }

        this.charts.topWorkouts = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Value Score',
                    data: values,
                    backgroundColor: colors.slice(0, labels.length),
                    borderColor: colors.slice(0, labels.length),
                    borderWidth: 2,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterLabel: function (context) {
                                const workout = topWorkouts[context.dataIndex];
                                return [
                                    `Calories: ${workout.calories}`,
                                    `Duration: ${workout.duration} min`,
                                    `Type: ${workout.type}`,
                                    `Intensity: ${workout.intensity}`
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    /**
     * Chart 4: Weekly Progress
     */
    renderWeeklyChart() {
        const ctx = document.getElementById('weeklyChart');
        if (!ctx) return;

        const weeklyData = this.generateWeeklyData();

        if (this.charts.weekly) {
            this.charts.weekly.destroy();
        }

        this.charts.weekly = new Chart(ctx, {
            type: 'line',
            data: {
                labels: weeklyData.labels,
                datasets: [
                    {
                        label: 'Steps',
                        data: weeklyData.steps,
                        borderColor: '#5409DA',
                        backgroundColor: 'rgba(84, 9, 218, 0.1)',
                        borderWidth: 3,
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: '#5409DA',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    },
                    {
                        label: 'Calories (x10)',
                        data: weeklyData.calories.map(c => c / 10),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 3,
                        tension: 0.3,
                        fill: true,
                        pointBackgroundColor: '#28a745',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 11 }, boxWidth: 15, padding: 10 }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    /**
     * Generate weekly data
     */
    generateWeeklyData() {
        const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        const today = new Date();
        const dayOfWeek = today.getDay();

        const weeklyWorkouts = this.dataManager.workouts.filter(w => {
            const workoutDate = new Date(w.date);
            const diffDays = (today - workoutDate) / (1000 * 60 * 60 * 24);
            return diffDays < 7;
        });

        const dayData = {};
        days.forEach((day, index) => {
            const date = new Date(today);
            date.setDate(date.getDate() - (dayOfWeek === 0 ? 6 : dayOfWeek - 1) + index);
            dayData[day] = { date: date, steps: 0, calories: 0, workouts: 0 };
        });

        weeklyWorkouts.forEach(w => {
            const workoutDate = new Date(w.date);
            const dayName = days[workoutDate.getDay() === 0 ? 6 : workoutDate.getDay() - 1];

            if (dayData[dayName]) {
                dayData[dayName].calories += w.calories;
                dayData[dayName].workouts += 1;
            }
        });

        const steps = days.map(day => 8000 + Math.floor(Math.random() * 4000) + dayData[day].workouts * 500);
        const calories = days.map(day => dayData[day].calories || 0);
        const maxCalories = Math.max(...calories);

        if (maxCalories === 0) {
            return {
                labels: days,
                steps: [8500, 10200, 7600, 9300, 11500, 14000, 9800],
                calories: [2100, 2450, 1800, 2200, 2700, 3200, 2350]
            };
        }

        return { labels: days, steps: steps, calories: calories };
    }

    /**
     * Update the activity table
     */
    updateTable() {
        const tableBody = document.getElementById('activityTableBody');
        const countEl = document.getElementById('activityCount');
        if (!tableBody) return;

        const workouts = this.dataManager.filteredWorkouts;

        if (countEl) {
            countEl.textContent = workouts.length + ' activities';
        }

        tableBody.innerHTML = '';

        if (workouts.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        No workouts found matching your filters
                    </td>
                </tr>
            `;
            return;
        }

        const sortedWorkouts = workouts
            .slice()
            .sort((a, b) => new Date(b.date) - new Date(a.date));

        sortedWorkouts.forEach((activity) => {
            const row = document.createElement('tr');

            const statusConfig = {
                'Completed': { class: 'bg-success', text: 'Completed' },
                'Ongoing': { class: 'bg-info text-dark', text: 'Ongoing' },
                'Failed': { class: 'bg-danger', text: 'Failed' }
            };

            const status = statusConfig[activity.status] || { class: 'bg-secondary', text: 'Pending' };
            const intensityClass = {
                'low': 'text-success',
                'medium': 'text-warning',
                'high': 'text-danger'
            }[activity.intensity] || '';

            const rowClass = activity.status === 'Failed' ? 'table-danger' : '';
            row.className = rowClass;
            row.innerHTML = `
                <td>${activity.date}</td>
                <td><strong>${activity.activity}</strong></td>
                <td><span class="badge bg-secondary">${activity.type}</span></td>
                <td class="${intensityClass}">${activity.intensity}</td>
                <td><span class="badge ${status.class}">${status.text}</span></td>
                <td>${activity.calories}</td>
                <td>${activity.duration} min</td>
                <td>
                    <button class="btn btn-sm btn-outline-danger"
                            onclick="dashboard.deleteWorkout(${activity.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            tableBody.appendChild(row);
        });
    }

    /**
     * Setup event listeners for filters and interactions
     */
    setupEventListeners() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.dataManager.filters.search = e.target.value;
                this.applyFiltersAndUpdate();
            });
        }

        const typeFilter = document.getElementById('typeFilter');
        if (typeFilter) {
            typeFilter.addEventListener('change', (e) => {
                this.dataManager.filters.type = e.target.value;
                this.applyFiltersAndUpdate();
            });
        }

        const intensityFilter = document.getElementById('intensityFilter');
        if (intensityFilter) {
            intensityFilter.addEventListener('change', (e) => {
                this.dataManager.filters.intensity = e.target.value;
                this.applyFiltersAndUpdate();
            });
        }

        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.dataManager.filters.status = e.target.value;
                this.applyFiltersAndUpdate();
            });
        }

        const dateRangeFilter = document.getElementById('dateRangeFilter');
        if (dateRangeFilter) {
            dateRangeFilter.addEventListener('change', (e) => {
                this.dataManager.filters.dateRange = e.target.value;
                this.applyFiltersAndUpdate();
            });
        }

        const resetBtn = document.getElementById('resetFiltersBtn');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                document.getElementById('searchInput').value = '';
                document.getElementById('typeFilter').value = 'all';
                document.getElementById('intensityFilter').value = 'all';
                document.getElementById('statusFilter').value = 'all';
                document.getElementById('dateRangeFilter').value = 'all';

                this.dataManager.filters = {
                    search: '',
                    type: 'all',
                    intensity: 'all',
                    status: 'all',
                    dateRange: 'all'
                };

                this.applyFiltersAndUpdate();
            });
        }
    }

    /**
     * Setup the Add Workout modal
     */
    setupAddWorkoutModal() {
        const addWorkoutForm = document.getElementById('addWorkoutForm');
        const addWorkoutModal = document.getElementById('addWorkoutModal');
        if (!addWorkoutForm || !addWorkoutModal) return;

        // Set default date/time when modal opens
        addWorkoutModal.addEventListener('show.bs.modal', () => {
            const dateInput = document.getElementById('new_date');
            if (dateInput && !dateInput.value) {
                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                dateInput.value = now.toISOString().slice(0, 16);
            }
            document.getElementById('addWorkoutFeedback').innerHTML = '';
        });

        addWorkoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const feedback = document.getElementById('addWorkoutFeedback');
            const saveBtn = document.getElementById('saveWorkoutBtn');
            feedback.innerHTML = '';

            const payload = {
                activity:  document.getElementById('new_activity').value.trim(),
                type:      document.getElementById('new_type').value,
                intensity: document.getElementById('new_intensity').value,
                status:    document.getElementById('new_status').value,
                calories:  parseInt(document.getElementById('new_calories').value, 10),
                duration:  parseInt(document.getElementById('new_duration').value, 10),
                date:      document.getElementById('new_date').value.replace('T', ' ') + ':00',
                notes:     document.getElementById('new_notes').value.trim() || null
            };

            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

            const created = await this.dataManager.addWorkout(payload);

            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-save"></i> Save Workout';

            if (created) {
                const modal = bootstrap.Modal.getInstance(addWorkoutModal);
                modal?.hide();
                addWorkoutForm.reset();

                this.applyFiltersAndUpdate();
                this.updateLiveIndicator('Workout Added!', true);
                setTimeout(() => this.updateLiveIndicator('Live', true), 2000);
            } else {
                feedback.innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        Failed to save workout. Please check your inputs and try again.
                    </div>
                `;
            }
        });
    }

    /**
     * Delete a workout
     */
    async deleteWorkout(id) {
        if (!confirm('Delete this workout?')) return;

        const ok = await this.dataManager.deleteWorkout(id);
        if (ok) {
            this.applyFiltersAndUpdate();
            this.updateLiveIndicator('Deleted', true);
            setTimeout(() => this.updateLiveIndicator('Live', true), 1500);
        } else {
            alert('Failed to delete workout.');
        }
    }

    /**
     * Apply filters and update all UI components
     */
    applyFiltersAndUpdate() {
        this.dataManager.applyFilters();
        this.updateStatistics();
        this.renderAlerts();
        this.renderCharts();
        this.updateTable();
        this.updateLiveIndicator('Updated', true);

        setTimeout(() => {
            this.updateLiveIndicator('Live', true);
        }, 1500);
    }

    /**
     * Setup export functionality
     */
    setupExport() {
        const exportBtn = document.getElementById('exportBtn');
        if (!exportBtn) return;

        exportBtn.addEventListener('click', () => {
            const data = this.dataManager.filteredWorkouts.length > 0
                ? this.dataManager.filteredWorkouts
                : this.dataManager.workouts;

            if (data.length === 0) {
                alert('No data to export. Please add some workouts first.');
                return;
            }

            const csv = this.dataManager.exportToCSV(data);
            if (csv) {
                this.dataManager.downloadCSV(csv);
                this.updateLiveIndicator('Exported!', true);
                setTimeout(() => {
                    this.updateLiveIndicator('Live', true);
                }, 2000);
            }
        });
    }

    /**
     * Clean up charts (no intervals since real-time is removed)
     */
    destroy() {
        Object.keys(this.charts).forEach(key => {
            if (this.charts[key]) this.charts[key].destroy();
        });
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', async () => {
    window.dashboard = new Dashboard();
    await window.dashboard.init();

    window.addEventListener('beforeunload', () => {
        window.dashboard.destroy();
    });
});