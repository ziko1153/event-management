<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Events List</h5>
        <a href="/admin/events/create" class="btn btn-primary">Create Event</a>
    </div>
    <div class="card-body">
        <div id="alertContainer"></div>

        <!-- Filters -->
        <form id="eventFilterForm" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="eventSearch" class="form-label">Search Title/Description</label>
                <input type="text" class="form-control" id="eventSearch" name="search" placeholder="Search events...">
            </div>
            <div class="col-md-2">
                <label for="eventStatus" class="form-label">Event Status</label>
                <select class="form-select" name="status" id="eventStatus">
                    <option value="">All Status</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= $status ?>">
                            <?= ucfirst(strtolower($status)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="eventType" class="form-label">Event Type</label>
                <select class="form-select" name="type" id="eventType">
                    <option value="">All Types</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= $type ?>">
                            <?= ucfirst(strtolower($type)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="dateType" class="form-label">Date Type</label>
                <select class="form-select" name="date_type" id="dateType">
                    <option value="start">Event Start Date</option>
                    <option value="end">Event End Date</option>
                    <option value="registration">Registration Deadline</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="startDate" class="form-label">From Date</label>
                <input type="date" class="form-control" name="start_date" id="startDate">
            </div>
            <div class="col-md-2">
                <label for="endDate" class="form-label">To Date</label>
                <input type="date" class="form-control" name="end_date" id="endDate">
            </div>
            <div class="col-md-3">
                <label for="organizerSearch" class="form-label">Search Organizer</label>
                <div class="position-relative">
                    <input type="text" class="form-control" id="organizerSearch" placeholder="write minimum 3 letter"
                        autocomplete="off">
                    <input type="hidden" name="organizer_id" id="organizerId">
                    <div id="organizerResults" class="position-absolute w-100 mt-1 d-none" style="z-index: 1000;">
                    </div>
                </div>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    <button type="reset" class="btn btn-outline-secondary">Reset</button>
                </div>
            </div>
        </form>

        <!-- Events Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 80px">Thumbnail</th>
                        <th>
                            <a href="#" class="sort-header text-decoration-none text-dark" data-sort="title">
                                Title <i class="bi bi-arrow-down-up"></i>
                            </a>
                        </th>
                        <th>Organizer</th>
                        <th>
                            <a href="#" class="sort-header text-decoration-none text-dark"
                                data-sort="registration_deadline">
                                Registration Deadline <i class="bi bi-arrow-down-up"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-header text-decoration-none text-dark" data-sort="start_date">
                                Start Date <i class="bi bi-arrow-down-up"></i>
                            </a>
                        </th>
                        <th>
                            <a href="#" class="sort-header text-decoration-none text-dark" data-sort="end_date">
                                End Date <i class="bi bi-arrow-down-up"></i>
                            </a>
                        </th>
                        <th>Price</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="eventsTableBody">
                    <!-- Data will be loaded via AJAX -->
                </tbody>
            </table>

            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="text-center d-none">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center" id="eventsPagination"></ul>
        </nav>
    </div>
</div>

<script type="module">
    import {
        initOrganizerSearch
    } from '/js/organizer-search.js';
    document.addEventListener('DOMContentLoaded', function() {
        let currentPage = 1;
        let currentSort = 'created_at';
        let currentOrder = 'DESC';
        initOrganizerSearch();
        loadEvents();
        document.getElementById('eventFilterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            currentPage = 1;
            loadEvents();
        });

        document.getElementById('eventFilterForm').addEventListener('reset', function(e) {
            e.preventDefault();
            // Reset all form inputs
            const form = this;
            form.querySelectorAll('input, select').forEach(element => {
                if (element.type === 'hidden') {
                    element.value = '';
                } else if (element.type === 'date') {
                    element.value = '';
                } else if (element.tagName === 'SELECT') {
                    element.selectedIndex = 0;
                } else {
                    element.value = '';
                }
            });

            document.getElementById('organizerId').value = '';
            document.getElementById('organizerSearch').value = '';

            currentPage = 1;
            currentSort = 'created_at';
            currentOrder = 'DESC';

            loadEvents();
        });

        document.querySelectorAll('.sort-header').forEach(header => {
            header.addEventListener('click', function(e) {
                e.preventDefault();
                const sort = this.dataset.sort;
                currentOrder = sort === currentSort && currentOrder === 'ASC' ? 'DESC' : 'ASC';
                currentSort = sort;
                loadEvents();
            });
        });

        async function loadEvents() {
            showLoading(true);

            try {
                const formData = new FormData(document.getElementById('eventFilterForm'));
                const params = new URLSearchParams({
                    page: currentPage,
                    sort: currentSort,
                    order: currentOrder,
                    ...Object.fromEntries(formData)
                });

                const response = await fetch(`/admin/events?${params}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Failed to load events');
                }

                renderEvents(data.events);
                renderPagination(data.pagination);
                updateUrl(params);

            } catch (error) {
                showError(error.message);
            } finally {
                showLoading(false);
            }
        }

        function renderEvents(events) {
            const tbody = document.getElementById('eventsTableBody');
            tbody.innerHTML = events.map(event => `
                    <tr>
                        <td>
                            <img src="/${event.thumbnail}" 
                                 alt="${event.title}" 
                                 class="img-thumbnail" 
                                 style="width: 50px; height: 50px; object-fit: cover;">
                        </td>
                        <td>${event.title}</td>
                        <td>${event.organizer_name}</td>
                        <td>${new Date(event.registration_deadline).toLocaleDateString()}</td>
                        <td>${new Date(event.start_date).toLocaleDateString()}</td>
                        <td>${new Date(event.end_date).toLocaleDateString()}</td>
                        <td>${event.price ? '$' + parseFloat(event.price).toFixed(2) : 'Free'}</td>
                        <td>${capitalize(event.event_type)}</td>
                        <td>
                            <span class="badge bg-${getStatusColor(event.status)}">
                                ${capitalize(event.status)}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/events/update/${event.slug}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a type="button" class="btn btn-sm btn-outline-info"
                                        href="/admin/events/${event.slug}/attendees">
                                    <i class="bi bi-people"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="window.deleteEvent('${event.slug}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
        }

        function renderPagination(pagination) {
            const container = document.getElementById('eventsPagination');
            if (!pagination.total) {
                container.innerHTML = '';
                return;
            }

            let html = '';
            if (pagination.current > 1) {
                html += `<li class="page-item">
                        <a class="page-link" href="#" data-page="${pagination.current - 1}">Previous</a>
                    </li>`;
            }

            for (let i = 1; i <= pagination.total; i++) {
                html += `<li class="page-item ${i === pagination.current ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>`;
            }

            if (pagination.current < pagination.total) {
                html += `<li class="page-item">
                        <a class="page-link" href="#" data-page="${pagination.current + 1}">Next</a>
                    </li>`;
            }

            container.innerHTML = html;

            container.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentPage = parseInt(this.dataset.page);
                    loadEvents();
                });
            });
        }

        window.deleteEvent = async function(slug) {
            if (!confirm('Are you sure you want to delete this event?')) {
                return;
            }

            try {
                const response = await fetch(`/admin/events/delete/${slug}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error || 'Failed to delete event');
                }

                showSuccess(data.message);
                loadEvents();

            } catch (error) {
                showError(error.message);
            }
        };


        function showLoading(show) {
            document.getElementById('loadingIndicator').classList.toggle('d-none', !show);
        }

        function showError(message) {
            const alert = `<div class="alert alert-danger alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
            document.getElementById('alertContainer').innerHTML = alert;
        }

        function showSuccess(message) {
            const alert = `<div class="alert alert-success alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
            document.getElementById('alertContainer').innerHTML = alert;
        }

        function getStatusColor(status) {
            const colors = {
                'published': 'success',
                'draft': 'warning',
                'cancelled': 'danger',
                'completed': 'info'
            };
            return colors[status.toLowerCase()] || 'secondary';
        }

        function capitalize(str) {
            return str.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
        }

        function updateUrl(params) {
            const url = new URL(window.location);
            for (const [key, value] of params) {
                if (value) {
                    url.searchParams.set(key, value);
                } else {
                    url.searchParams.delete(key);
                }
            }
            window.history.pushState({}, '', url);
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    });
</script>