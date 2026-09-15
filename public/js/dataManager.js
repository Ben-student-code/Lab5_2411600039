/**
 * DataManager - Handles all data operations for the Fitness Tracker
 * Part 3: Creating the JavaScript Data Management System
 *
 * Connected to Laravel API at /api/workouts
 *  - GET    /api/workouts       -> list
 *  - POST   /api/workouts       -> create
 *  - PUT    /api/workouts/{id}  -> update
 *  - DELETE /api/workouts/{id}  -> delete
 */

class DataManager {
    constructor() {
        this.workouts = [];
        this.filteredWorkouts = [];
        this.filters = {
            search: '',
            type: 'all',
            intensity: 'all',
            status: 'all',
            dateRange: 'all'
        };
        this.initialized = false;
    }

    /**
     * CSRF token helper for write requests
     */
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    /**
     * Initialize data by fetching from the Laravel API
     */
    async initializeData() {
        if (this.initialized) return this.workouts;

        try {
            const res = await fetch('/api/workouts', {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) throw new Error('API error: ' + res.status);

            const raw = await res.json();

            this.workouts = raw.map(w => ({
                id:        w.id,
                date:      w.date ? w.date.replace('T', ' ').slice(0, 16) : '',
                activity:  w.activity,
                type:      w.type,
                intensity: w.intensity,
                status:    w.status,
                calories:  parseInt(w.calories, 10) || 0,
                duration:  parseInt(w.duration, 10) || 0,
                notes:     w.notes || ''
            }));

            this.filteredWorkouts = [...this.workouts];
            this.initialized = true;
            return this.workouts;
        } catch (err) {
            console.error('Failed to load workouts:', err);
            this.workouts = [];
            this.filteredWorkouts = [];
            this.initialized = true;
            return [];
        }
    }

    /**
     * Get all workouts
     */
    getWorkouts() {
        return this.workouts;
    }

    /**
     * Get a single workout by ID
     */
    getWorkoutById(id) {
        return this.workouts.find(w => w.id === id);
    }

    /**
     * Get workouts filtered by type
     */
    getWorkoutsByType(type) {
        return this.workouts.filter(w => w.type === type);
    }

    /**
     * Get workouts by status
     */
    getWorkoutsByStatus(status) {
        return this.workouts.filter(w => w.status === status);
    }

    /**
     * Get workouts that need attention (Failed status)
     */
    getFailedWorkouts() {
        return this.workouts.filter(w => w.status === 'Failed');
    }

    /**
     * Get workouts by intensity
     */
    getWorkoutsByIntensity(intensity) {
        return this.workouts.filter(w => w.intensity === intensity);
    }

    /**
     * Get statistics about workouts
     */
    getWorkoutStatistics() {
        const total = this.workouts.length;
        const completed = this.workouts.filter(w => w.status === 'Completed').length;
        const ongoing = this.workouts.filter(w => w.status === 'Ongoing').length;
        const failed = this.workouts.filter(w => w.status === 'Failed').length;
        const totalCalories = this.workouts.reduce((sum, w) => sum + w.calories, 0);
        const totalDuration = this.workouts.reduce((sum, w) => sum + w.duration, 0);
        const completionRate = total > 0 ? (completed / total) * 100 : 0;

        return {
            totalWorkouts: total,
            completed,
            ongoing,
            failed,
            totalCalories,
            totalDuration,
            completionRate: Math.round(completionRate)
        };
    }

    /**
     * Get category summary (by type)
     */
    getCategorySummary() {
        const categories = {};

        this.workouts.forEach(w => {
            if (!categories[w.type]) {
                categories[w.type] = {
                    name: w.type,
                    count: 0,
                    calories: 0,
                    duration: 0
                };
            }
            categories[w.type].count += 1;
            categories[w.type].calories += w.calories;
            categories[w.type].duration += w.duration;
        });

        return Object.values(categories);
    }

    /**
     * Filter workouts by category (type)
     */
    filterByCategory(category) {
        if (category === 'all') return this.workouts;
        return this.workouts.filter(w => w.type === category);
    }

    /**
     * Filter by status
     */
    filterByStatus(status) {
        if (status === 'all') return this.workouts;
        return this.workouts.filter(w => w.status === status);
    }

    /**
     * Filter by intensity
     */
    filterByIntensity(intensity) {
        if (intensity === 'all') return this.workouts;
        return this.workouts.filter(w => w.intensity === intensity);
    }

    /**
     * Filter by date range
     */
    filterByDateRange(range) {
        if (range === 'all') return this.workouts;

        const now = new Date();
        return this.workouts.filter(w => {
            const workoutDate = new Date(w.date);
            const diffDays = (now - workoutDate) / (1000 * 60 * 60 * 24);

            if (range === 'today') return diffDays < 1;
            if (range === 'week') return diffDays < 7;
            if (range === 'month') return diffDays < 30;
            return true;
        });
    }

    /**
     * Search workouts by activity name
     */
    searchWorkouts(query) {
        if (!query || query.trim() === '') return this.workouts;

        const searchTerm = query.toLowerCase().trim();
        return this.workouts.filter(w =>
            w.activity.toLowerCase().includes(searchTerm) ||
            (w.notes && w.notes.toLowerCase().includes(searchTerm))
        );
    }

    /**
     * Apply all active filters
     */
    applyFilters(filters = null) {
        if (filters) {
            this.filters = { ...this.filters, ...filters };
        }

        let result = [...this.workouts];

        if (this.filters.search && this.filters.search.trim() !== '') {
            const searchTerm = this.filters.search.toLowerCase().trim();
            result = result.filter(w =>
                w.activity.toLowerCase().includes(searchTerm) ||
                (w.notes && w.notes.toLowerCase().includes(searchTerm))
            );
        }

        if (this.filters.type !== 'all') {
            result = result.filter(w => w.type === this.filters.type);
        }

        if (this.filters.intensity !== 'all') {
            result = result.filter(w => w.intensity === this.filters.intensity);
        }

        if (this.filters.status !== 'all') {
            result = result.filter(w => w.status === this.filters.status);
        }

        if (this.filters.dateRange !== 'all') {
            const now = new Date();
            result = result.filter(w => {
                const workoutDate = new Date(w.date);
                const diffDays = (now - workoutDate) / (1000 * 60 * 60 * 24);

                if (this.filters.dateRange === 'today') return diffDays < 1;
                if (this.filters.dateRange === 'week') return diffDays < 7;
                if (this.filters.dateRange === 'month') return diffDays < 30;
                return true;
            });
        }

        this.filteredWorkouts = result;
        return result;
    }

    /**
     * Update search results
     */
    updateSearchResults(query) {
        this.filters.search = query;
        return this.applyFilters();
    }

    /**
     * Add a new workout via API
     * @param {Object} workout
     * @returns {Object|null} the created workout or null on failure
     */
    async addWorkout(workout) {
        try {
            const res = await fetch('/api/workouts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify(workout)
            });

            if (!res.ok) {
                const errText = await res.text();
                console.error('addWorkout failed:', res.status, errText);
                return null;
            }

            const created = await res.json();
            created.date = created.date ? created.date.replace('T', ' ').slice(0, 16) : '';

            this.workouts.unshift(created);
            this.applyFilters();
            return created;
        } catch (err) {
            console.error('addWorkout error:', err);
            return null;
        }
    }

    /**
     * Update a workout via API
     */
    async updateWorkout(id, updates) {
        try {
            const res = await fetch(`/api/workouts/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                },
                body: JSON.stringify(updates)
            });

            if (!res.ok) throw new Error('Update failed');
            const updated = await res.json();
            updated.date = updated.date ? updated.date.replace('T', ' ').slice(0, 16) : '';

            const index = this.workouts.findIndex(w => w.id === id);
            if (index !== -1) {
                this.workouts[index] = { ...this.workouts[index], ...updated };
                this.applyFilters();
            }
            return updated;
        } catch (err) {
            console.error('updateWorkout error:', err);
            return null;
        }
    }

    /**
     * Delete a workout via API
     */
    async deleteWorkout(id) {
        try {
            const res = await fetch(`/api/workouts/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken()
                }
            });

            if (!res.ok) throw new Error('Delete failed');

            this.workouts = this.workouts.filter(w => w.id !== id);
            this.applyFilters();
            return true;
        } catch (err) {
            console.error('deleteWorkout error:', err);
            return false;
        }
    }

    /**
     * Export to CSV
     */
    exportToCSV(data = null) {
        const exportData = data || this.filteredWorkouts;

        if (exportData.length === 0) {
            return null;
        }

        const headers = ['Date', 'Activity', 'Type', 'Intensity', 'Status', 'Calories', 'Duration (min)', 'Notes'];
        const rows = exportData.map(w => [
            w.date,
            w.activity,
            w.type,
            w.intensity,
            w.status,
            w.calories,
            w.duration,
            w.notes || ''
        ]);

        let csv = headers.join(',') + '\n';
        rows.forEach(row => {
            const escapedRow = row.map(field =>
                typeof field === 'string' && field.includes(',') ? `"${field}"` : field
            );
            csv += escapedRow.join(',') + '\n';
        });

        return csv;
    }

    /**
     * Download CSV
     */
    downloadCSV(csvContent, filename = null) {
        if (!csvContent) return false;

        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;

        const date = new Date().toISOString().slice(0, 10);
        a.download = filename || `workout_export_${date}.csv`;

        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);

        return true;
    }

    /**
     * Simulate real-time updates (writes to DB via API)
     */
    async simulateRealTimeUpdate() {
        const newWorkout = {
            date: new Date().toISOString().replace('T', ' ').slice(0, 16) + ':00',
            activity: ['Burpees', 'Jump Squats', 'Mountain Climbers', 'Lunges', 'Plank', 'Kettlebell Swings'][Math.floor(Math.random() * 6)],
            type: ['cardio', 'strength', 'hiit', 'flexibility'][Math.floor(Math.random() * 4)],
            intensity: ['low', 'medium', 'high'][Math.floor(Math.random() * 3)],
            status: Math.random() > 0.2 ? 'Completed' : (Math.random() > 0.5 ? 'Ongoing' : 'Failed'),
            calories: Math.floor(Math.random() * 250) + 50,
            duration: Math.floor(Math.random() * 35) + 10,
            notes: 'Real-time update'
        };

        return await this.addWorkout(newWorkout);
    }

    /**
     * Get recent workouts
     */
    getRecentWorkouts(limit = 10) {
        return this.workouts
            .slice()
            .sort((a, b) => new Date(b.date) - new Date(a.date))
            .slice(0, limit);
    }

    /**
     * Get top workouts by value (calories * intensity multiplier)
     */
    getTopWorkouts(limit = 5) {
        const intensityMultiplier = {
            low: 1,
            medium: 1.5,
            high: 2
        };

        const scored = this.workouts.map(w => ({
            ...w,
            value: w.calories * (intensityMultiplier[w.intensity] || 1)
        }));

        scored.sort((a, b) => b.value - a.value);
        return scored.slice(0, limit);
    }

    /**
     * Get status distribution for charts
     */
    getStatusDistribution() {
        const distribution = {
            Completed: 0,
            Ongoing: 0,
            Failed: 0
        };

        this.workouts.forEach(w => {
            if (distribution[w.status] !== undefined) {
                distribution[w.status] += 1;
            }
        });

        return distribution;
    }
}

// Export for use in other files
const dataManager = new DataManager();