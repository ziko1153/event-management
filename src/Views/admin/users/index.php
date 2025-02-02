<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">User Management</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="bi bi-plus"></i> Add User
            </button>
        </div>
        <div class="card-body">
            <div id="alertContainer"></div>
            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search users...">
                </div>
                <div class="col-md-3">
                    <select id="roleFilter" class="form-select">
                        <option value="">All Roles</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role ?>"><?= ucfirst($role) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Users Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Organization</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <!-- Users will be loaded here via AJAX -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="d-flex justify-content-center mt-4">
                <!-- Pagination will be loaded here via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createUserForm" enctype="multipart/form-data">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required id="roleSelect">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role ?>"><?= ucfirst($role) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Avatar</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                        </div>
                        <div class="row g-2" id="organizationFields" style="display: none;">
                            <div class="row g-2">
                                <!-- Added row wrapper -->
                                <div class="col-md-6">
                                    <label class="form-label">Organization Name</label>
                                    <input type="text" name="org_name" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Website</label>
                                    <input type="url" name="org_website" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Description</label>
                                    <textarea name="org_description" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Logo</label>
                                    <input type="file" name="org_logo" class="form-control" accept="image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="createUserForm" class="btn btn-primary">Create User</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input readonly disabled type="email" name="email" id="editEmail" class="form-control"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input id="editPassword" autocomplete="off" type="password" name="password"
                                class="form-control">
                            <small class="text-muted">Leave empty to keep current password</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <input readonly id="editRoleSelect" class="form-select">
                            </input>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" id="editPhone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <textarea name="address" id="editAddress" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Avatar</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img id="editAvatarPreview" src="" class="rounded-circle" width="64" height="64">
                                <input type="file" name="avatar" class="form-control" accept="image/*">
                            </div>
                        </div>

                        <!-- Organization Fields -->
                        <div class="row g-2" id="editOrganizationFields" style="display: none;">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Organization Name</label>
                                    <input type="text" name="org_name" id="editOrgName" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Website</label>
                                    <input type="url" name="org_website" id="editOrgWebsite" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Description</label>
                                    <textarea name="org_description" id="editOrgDescription" class="form-control"
                                        rows="2"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Organization Logo</label>
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <img id="editOrgLogoPreview" src="" class="rounded-circle" width="64"
                                            height="64">
                                        <input type="file" name="org_logo" class="form-control" accept="image/*">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="editUserForm" class="btn btn-primary">Update User</button>
            </div>
        </div>
    </div>
</div>

<script src="/js/user-management.js"></script>