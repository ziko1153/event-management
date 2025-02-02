document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const roleSelect = document.getElementById('roleSelect');
    const organizationFields = document.getElementById('organizationFields');
    let currentPage = 1;
    loadUsers();

    // Event listeners for filters
    searchInput.addEventListener('input', debounce(() => {
        currentPage = 1;
        loadUsers();
    }, 500));

    roleFilter.addEventListener('change', () => {
        currentPage = 1;
        loadUsers();
    });

    statusFilter.addEventListener('change', () => {
        currentPage = 1;
        loadUsers();
    });

    roleSelect.addEventListener('change', function () {
        organizationFields.style.display = this.value === 'organizer' ? 'block' : 'none';
    });

    // Handle user creation
    document.getElementById('createUserForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        clearFieldErrors();

        try {
            const response = await fetch('/admin/users', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                if (result.errors) {
                    showFieldErrors(result.errors);
                }
                throw new Error(result.message || 'Failed to create user');
            }

            showAlert('success', result.message);
            this.reset();
            loadUsers();
            bootstrap.Modal.getInstance(document.getElementById('createUserModal')).hide();
        } catch (error) {
            showAlert('danger', error.message);
        }
    });

    // Handle user edit form submission
    document.getElementById('editUserForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const userId = formData.get('user_id');
        clearFieldErrors();

        try {
            const response = await fetch(`/admin/users/${userId}/update`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                if (result.errors) {
                    showFieldErrors(result.errors);
                }
                throw new Error(result.message || 'Failed to update user');
            }

            showAlert('success', result.message);
            loadUsers();
            bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
        } catch (error) {
            showAlert('danger', error.message);
        }
    });

    // Add these new functions for field error handling
    function showFieldErrors(errors) {
        clearFieldErrors();
        const activeModal = document.querySelector('.modal.show');
        Object.keys(errors).forEach(field => {
            const input = activeModal.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = errors[field];
                input.parentNode.appendChild(feedback);
            }
        });
    }

    function clearFieldErrors(modalElement = null) {
        const targetModal = modalElement || document.querySelector('.modal.show');
        if (targetModal) {
            targetModal.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
            targetModal.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        }
    }

    // Add modal reset handlers to clear errors when modals are closed
    document.getElementById('createUserModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('createUserForm').reset();
        clearFieldErrors(document.getElementById('createUserModal'))
    });

    document.getElementById('editUserModal').addEventListener('hidden.bs.modal', function () {
        clearFieldErrors(document.getElementById('editUserModal'))
    });

    // Handle user status toggle
    document.addEventListener('click', async function (e) {
        if (e.target.classList.contains('status-toggle')) {
            const userId = e.target.dataset.userId;
            const status = e.target.checked;

            try {
                const response = await fetch('/admin/users/status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ user_id: userId, status: status })
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.message || 'Failed to update status');
                }

                showAlert('success', result.message);
            } catch (error) {
                e.target.checked = !status;
                showAlert('danger', error.message);
            }
        }
    });

    // Load users function
    async function loadUsers() {
        try {
            const params = new URLSearchParams({
                page: currentPage,
                search: searchInput.value,
                role: roleFilter.value,
                status: statusFilter.value
            });

            const response = await fetch(`/admin/users?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to load users');
            }




            renderUsers(result.users);
            renderPagination(result.pagination);
        } catch (error) {
            console.log(error);

            showAlert('danger', error.message);
        }
    }

    // Render users table
    function renderUsers(users) {
        const tbody = document.getElementById('usersTableBody');
        tbody.innerHTML = users.map(user => `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="/${user.avatar}" class="rounded-circle me-2" width="40" height="40">
                        ${user.name}
                    </div>
                </td>
                <td>${user.email}</td>
                <td><span class="badge bg-info">${user.role}</span></td>
                <td>${user.organization_name || '-'}</td>
                <td>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input status-toggle" 
                            data-user-id="${user.id}" 
                            ${user.status ? 'checked' : ''}>
                    </div>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editUser(${user.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${user.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    // Render pagination
    function renderPagination({ current, total }) {
        const pagination = document.getElementById('pagination');
        let html = '';

        for (let i = 1; i <= total; i++) {
            html += `
                <button class="btn btn-${current === i ? 'primary' : 'outline-primary'} mx-1"
                    onclick="changePage(${i})">${i}</button>
            `;
        }

        pagination.innerHTML = html;
    }

    // Edit user function
    window.editUser = async function (userId) {
        try {
            const response = await fetch(`/admin/users/${userId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to load user');
            }


            document.getElementById('editUserId').value = result.user.id;
            document.getElementById('editPassword').value = '';
            document.getElementById('editName').value = result.user.name;
            document.getElementById('editEmail').value = result.user.email;
            document.getElementById('editRoleSelect').value = result.user.role;
            document.getElementById('editAvatarPreview').src = '/' + result.user.avatar;
            document.getElementById('editPhone').value = result.user.phone;
            document.getElementById('editAddress').value = result.user.address;

            // Handle organization fields
            const editOrgFields = document.getElementById('editOrganizationFields');
            if (result.user.role === 'organizer') {
                editOrgFields.style.display = 'block';
                document.getElementById('editOrgName').value = result.organization?.name || '';
                document.getElementById('editOrgWebsite').value = result.organization?.website || '';
                document.getElementById('editOrgLogoPreview').src = '/' + result.organization.logo;
                document.getElementById('editOrgDescription').value = result.organization.description;
            } else {
                editOrgFields.style.display = 'none';
            }

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        } catch (error) {
            showAlert('danger', error.message);
        }
    };

    // Delete user function
    window.deleteUser = async function (userId) {
        if (!confirm('Are you sure you want to delete this user?')) {
            return;
        }

        try {
            const response = await fetch(`/admin/users/${userId}/delete`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to delete user');
            }

            showAlert('success', result.message);
            loadUsers();
        } catch (error) {
            showAlert('danger', error.message);
        }
    };

    // Handle role change in edit form
    document.getElementById('editRoleSelect').addEventListener('change', function () {
        document.getElementById('editOrganizationFields').style.display =
            this.value === 'organizer' ? 'block' : 'none';
    });



    // Render pagination
    function renderPagination({ current, total }) {
        const pagination = document.getElementById('pagination');
        let html = '';

        for (let i = 1; i <= total; i++) {
            html += `
                <button class="btn btn-${current === i ? 'primary' : 'outline-primary'} mx-1"
                    onclick="changePage(${i})">${i}</button>
            `;
        }

        pagination.innerHTML = html;
    }

    // Change page
    window.changePage = function (page) {
        currentPage = page;
        loadUsers();
    };

    // Debounce function
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

    // Show alert message
    function showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer');
        const alert = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        alertContainer.innerHTML = alert;
    }

});