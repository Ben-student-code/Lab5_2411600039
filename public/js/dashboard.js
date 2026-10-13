/**
 * Dashboard Controller
 * Part 4-5: Interactive Data Visualizations and Dashboard Interactivity
 *
 * Laravel + Breeze version:
 *  - No localStorage auth check (Laravel middleware handles it)
 *  - Logout handled by form POST to route('logout')
 *  - Real-time simulation REMOVED
 *  - Add Workout modal REMOVED (moved to /inventory)
 *  - Filters moved to /inventory
 *  - Live indicator REMOVED
 *  - Failed workout alert changed to compact clickable chip
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
        this.setupExport();
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
     * Render a compact clickable chip for failed workouts.
     * Clicking → navigates to /notifications for full details.
     */
    renderAlerts() {
        const container = document.getElementById('alertContainer');
        if (!container) return;

        const failedWorkouts = this.dataManager.getFailedWorkouts();

        // No failures → hide the alert area completely
        if (failedWorkouts.length === 0) {
            container.innerHTML = '';
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';

        const count = failedWorkouts.length;
        const latest = failedWorkouts[0];

        container.innerHTML = `
            <a href="/notifications"
               class="d-inline-flex align-items-center gap-2 text-decoration-none px-3 py-2 rounded-pill shadow-sm"
               style="background:#fff4e5;border:1px solid #ffd699;color:#8a6100;transition:all 0.2s ease;"
               onmouseover="this.style.background='#ffe4b3'"
               onmouseout="this.style.background='#fff4e5'">
                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                <span class="fw-semibold">
                    ${count} Failed Workout${count > 1 ? 's' : ''}
                </span>
                <span class="text-muted small">· ${latest.activity}</span>
                <i class="bi bi-arrow-right-short fs-5"></i>
                <small class="text-muted ms-1">View All</small>
            </a>
        `;
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
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        No workouts found
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
            `;

            tableBody.appendChild(row);
        });
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
            }
        });
    }

    /**
     * Clean up charts
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