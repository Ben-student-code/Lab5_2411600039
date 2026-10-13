/**
 * InventoryManager - Handles the Inventory page (workouts as items).
 * Supports: list, add, edit, delete, filters.
 */

class InventoryManager {
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

    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    async init() {
        await this.loadWorkouts();
        this.applyFilters();
        this.renderTable();
        this.attachEvents();
        this.attachFilterEvents();
    }

    async loadWorkouts() {
        try {
            const res = await fetch('/api/workouts', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) throw new Error('API error ' + res.status);

            const raw = await res.json();
            this.workouts = raw.map(w => ({
                id:        w.id,
                date:      w.date,
                activity:  w.activity,
                type:      w.type,
                intensity: w.intensity,
                status:    w.status,
                calories:  parseInt(w.calories, 10) || 0,
                duration:  parseInt(w.duration, 10) || 0,
                notes:     w.notes || '',
            }));
            this.filteredWorkouts = [...this.workouts];
            this.initialized = true;
        } catch (err) {
            console.error('Failed to load workouts:', err);
            this.workouts = [];
            this.filteredWorkouts = [];
        }
    }

    // ==================== RENDER TABLE ====================
    renderTable() {
        const tbody = document.getElementById('inventoryTableBody');
        const countEl = document.getElementById('inventoryCount');
        if (!tbody) return;

        const list = this.filteredWorkouts || this.workouts;

        if (countEl) {
            countEl.textContent = list.length + ' items';
        }

        if (list.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        No items match your filters.
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = list.map(w => {
            const statusBadge = {
                'Completed': '<span class="badge bg-success">Completed</span>',
                'Ongoing':   '<span class="badge bg-warning text-dark">Ongoing</span>',
                'Failed':    '<span class="badge bg-danger">Failed</span>',
            }[w.status] || '<span class="badge bg-secondary">Unknown</span>';

            const intensityClass = {
                'low':    'text-success',
                'medium': 'text-warning',
                'high':   'text-danger',
            }[w.intensity] || '';

            return `
                <tr>
                    <td>#${String(w.id).padStart(4, '0')}</td>
                    <td>
                        <strong>${w.activity}</strong>
                        ${w.notes ? `<br><small class="text-muted">${w.notes}</small>` : ''}
                    </td>
                    <td><span class="badge bg-secondary">${w.type}</span></td>
                    <td>${w.duration} min</td>
                    <td>${w.calories} kcal</td>
                    <td>${statusBadge} <small class="${intensityClass}">(${w.intensity})</small></td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="inventoryManager.openEditModal(${w.id})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="inventoryManager.deleteWorkout(${w.id})" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>`;
        }).join('');
    }

    // ==================== FILTERS ====================
    applyFilters() {
        let result = [...this.workouts];
        const f = this.filters;

        if (f.search && f.search.trim() !== '') {
            const s = f.search.toLowerCase().trim();
            result = result.filter(w =>
                w.activity.toLowerCase().includes(s) ||
                (w.notes && w.notes.toLowerCase().includes(s))
            );
        }

        if (f.type !== 'all') {
            result = result.filter(w => w.type === f.type);
        }

        if (f.intensity !== 'all') {
            result = result.filter(w => w.intensity === f.intensity);
        }

        if (f.status !== 'all') {
            result = result.filter(w => w.status === f.status);
        }

        if (f.dateRange !== 'all') {
            const now = new Date();
            result = result.filter(w => {
                const workoutDate = new Date(w.date);
                const diffDays = (now - workoutDate) / (1000 * 60 * 60 * 24);
                if (f.dateRange === 'today') return diffDays < 1;
                if (f.dateRange === 'week')  return diffDays < 7;
                if (f.dateRange === 'month') return diffDays < 30;
                return true;
            });
        }

        this.filteredWorkouts = result;
        return result;
    }

    attachFilterEvents() {
        const search  = document.getElementById('invSearchInput');
        const type    = document.getElementById('invTypeFilter');
        const intens  = document.getElementById('invIntensityFilter');
        const status  = document.getElementById('invStatusFilter');
        const dateR   = document.getElementById('invDateRangeFilter');
        const reset   = document.getElementById('invResetFiltersBtn');

        const update = () => {
            this.applyFilters();
            this.renderTable();
        };

        if (search) search.addEventListener('input', (e) => {
            this.filters.search = e.target.value;
            update();
        });
        if (type) type.addEventListener('change', (e) => {
            this.filters.type = e.target.value;
            update();
        });
        if (intens) intens.addEventListener('change', (e) => {
            this.filters.intensity = e.target.value;
            update();
        });
        if (status) status.addEventListener('change', (e) => {
            this.filters.status = e.target.value;
            update();
        });
        if (dateR) dateR.addEventListener('change', (e) => {
            this.filters.dateRange = e.target.value;
            update();
        });
        if (reset) reset.addEventListener('click', () => {
            this.filters = { search: '', type: 'all', intensity: 'all', status: 'all', dateRange: 'all' };
            if (search) search.value = '';
            if (type)   type.value = 'all';
            if (intens) intens.value = 'all';
            if (status) status.value = 'all';
            if (dateR)  dateR.value = 'all';
            update();
        });
    }

    // ==================== EVENTS ====================
    attachEvents() {
        const addForm = document.getElementById('inventoryAddForm');
        const addModal = document.getElementById('inventoryAddModal');
        const editForm = document.getElementById('inventoryEditForm');
        const editModal = document.getElementById('inventoryEditModal');

        if (addModal) {
            addModal.addEventListener('show.bs.modal', () => {
                const dateInput = document.getElementById('inv_new_date');
                if (dateInput && !dateInput.value) {
                    const now = new Date();
                    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                    dateInput.value = now.toISOString().slice(0, 16);
                }
                const feedback = document.getElementById('inv_addFeedback');
                if (feedback) feedback.innerHTML = '';
            });
        }

        if (addForm) {
            addForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.handleAddSubmit(addForm, addModal);
            });
        }

        if (editModal) {
            editModal.addEventListener('show.bs.modal', () => {
                const feedback = document.getElementById('inv_editFeedback');
                if (feedback) feedback.innerHTML = '';
            });
        }

        if (editForm) {
            editForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.handleEditSubmit(editForm, editModal);
            });
        }
    }

    // ==================== ADD ====================
    async handleAddSubmit(form, modal) {
        const feedback = document.getElementById('inv_addFeedback');
        const saveBtn = document.getElementById('inv_saveBtn');

        if (feedback) feedback.innerHTML = '';
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
        }

        const payload = {
            activity:  document.getElementById('inv_new_activity').value.trim(),
            type:      document.getElementById('inv_new_type').value,
            intensity: document.getElementById('inv_new_intensity').value,
            status:    document.getElementById('inv_new_status').value,
            calories:  parseInt(document.getElementById('inv_new_calories').value, 10),
            duration:  parseInt(document.getElementById('inv_new_duration').value, 10),
            date:      document.getElementById('inv_new_date').value.replace('T', ' ') + ':00',
            notes:     document.getElementById('inv_new_notes').value.trim() || null,
        };

        try {
            const res = await fetch('/api/workouts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const created = await res.json();
            this.workouts.unshift({
                id:        created.id,
                date:      created.date,
                activity:  created.activity,
                type:      created.type,
                intensity: created.intensity,
                status:    created.status,
                calories:  parseInt(created.calories, 10) || 0,
                duration:  parseInt(created.duration, 10) || 0,
                notes:     created.notes || '',
            });

            this.applyFilters();
            this.renderTable();
            if (modal) bootstrap.Modal.getInstance(modal)?.hide();
            form.reset();

        } catch (err) {
            console.error('Add failed:', err);
            if (feedback) {
                feedback.innerHTML = `<div class="alert alert-danger mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i> Failed: ${err.message}</div>`;
            }
        } finally {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-save"></i> Save Workout';
            }
        }
    }

    // ==================== OPEN EDIT MODAL ====================
    openEditModal(id) {
        const w = this.workouts.find(x => x.id === id);
        if (!w) {
            alert('Workout not found.');
            return;
        }

        document.getElementById('inv_edit_id').value         = w.id;
        document.getElementById('inv_edit_activity').value   = w.activity;
        document.getElementById('inv_edit_type').value       = w.type;
        document.getElementById('inv_edit_intensity').value  = w.intensity;
        document.getElementById('inv_edit_status').value     = w.status;
        document.getElementById('inv_edit_calories').value   = w.calories;
        document.getElementById('inv_edit_duration').value   = w.duration;
        document.getElementById('inv_edit_notes').value      = w.notes || '';

        if (w.date) {
            const d = new Date(w.date);
            d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
            document.getElementById('inv_edit_date').value = d.toISOString().slice(0, 16);
        }

        const modal = new bootstrap.Modal(document.getElementById('inventoryEditModal'));
        modal.show();
    }

    // ==================== SAVE EDIT ====================
    async handleEditSubmit(form, modal) {
        const id = document.getElementById('inv_edit_id').value;
        const feedback = document.getElementById('inv_editFeedback');
        const saveBtn = document.getElementById('inv_editSaveBtn');

        if (feedback) feedback.innerHTML = '';
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
        }

        const payload = {
            activity:  document.getElementById('inv_edit_activity').value.trim(),
            type:      document.getElementById('inv_edit_type').value,
            intensity: document.getElementById('inv_edit_intensity').value,
            status:    document.getElementById('inv_edit_status').value,
            calories:  parseInt(document.getElementById('inv_edit_calories').value, 10),
            duration:  parseInt(document.getElementById('inv_edit_duration').value, 10),
            date:      document.getElementById('inv_edit_date').value.replace('T', ' ') + ':00',
            notes:     document.getElementById('inv_edit_notes').value.trim() || null,
        };

        try {
            const res = await fetch(`/api/workouts/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const updated = await res.json();
            const index = this.workouts.findIndex(x => x.id === parseInt(id, 10));
            if (index !== -1) {
                this.workouts[index] = {
                    id:        updated.id,
                    date:      updated.date,
                    activity:  updated.activity,
                    type:      updated.type,
                    intensity: updated.intensity,
                    status:    updated.status,
                    calories:  parseInt(updated.calories, 10) || 0,
                    duration:  parseInt(updated.duration, 10) || 0,
                    notes:     updated.notes || '',
                };
            }

            this.applyFilters();
            this.renderTable();
            if (modal) bootstrap.Modal.getInstance(modal)?.hide();

        } catch (err) {
            console.error('Update failed:', err);
            if (feedback) {
                feedback.innerHTML = `<div class="alert alert-danger mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i> Update failed: ${err.message}</div>`;
            }
        } finally {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-save"></i> Update Workout';
            }
        }
    }

    // ==================== DELETE ====================
    async deleteWorkout(id) {
        if (!confirm('Delete this item?')) return;

        try {
            const res = await fetch(`/api/workouts/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                },
                credentials: 'same-origin',
            });

            if (!res.ok) throw new Error('Delete failed');

            this.workouts = this.workouts.filter(w => w.id !== id);
            this.applyFilters();
            this.renderTable();
        } catch (err) {
            console.error('Delete failed:', err);
            alert('Failed to delete. Please try again.');
        }
    }
}

const inventoryManager = new InventoryManager();

document.addEventListener('DOMContentLoaded', () => {
    inventoryManager.init();
});