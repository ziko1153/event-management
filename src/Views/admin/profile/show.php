<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">My Profile</h4>
                </div>
                <div class="card-body">
                    <form action="/admin/profile" method="POST" enctype="multipart/form-data">
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img src="/<?= $user['avatar'] ?>" class="rounded-circle" width="100" height="100"
                                    alt="Profile Avatar" id="avatarPreview" style="object-fit: cover;">
                                <label for="avatar"
                                    class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-1"
                                    style="cursor: pointer;">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                            </div>
                            <input type="file" name="avatar" id="avatar" class="d-none" accept="image/*">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control"
                                    value="<?= oldData('name', $user['name']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?= $user['email'] ?>" disabled>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control"
                                    value="<?= oldData('phone', $user['phone']) ?>">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control"
                                    rows="2"><?= oldData('address', $user['address']) ?></textarea>
                            </div>
                        </div>

                        <?php if (isset($user['organizer'])): ?>
                        <hr>
                        <h5 class="mb-3">Organization Details</h5>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="text-center mb-3">
                                    <div class="position-relative d-inline-block">
                                        <img src="/<?= $user['organizer']['logo'] ?>" class="rounded" width="150"
                                            height="150" alt="Organization Logo" id="orgLogoPreview"
                                            style="object-fit: cover;">
                                        <label for="org_logo"
                                            class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-1"
                                            style="cursor: pointer;">
                                            <i class="bi bi-camera-fill"></i>
                                        </label>
                                    </div>
                                    <input type="file" name="org_logo" id="org_logo" class="d-none" accept="image/*">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Organization Name</label>
                                <input type="text" name="org_name" class="form-control"
                                    value="<?= oldData('org_name', $user['organizer']['name']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Website</label>
                                <input type="url" name="org_website" class="form-control"
                                    value="<?= oldData('org_website', $user['organizer']['website']) ?>">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="org_description" class="form-control"
                                    rows="3"><?= oldData('org_description', $user['organizer']['description']) ?></textarea>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                            <a href="/admin/profile/password" class="btn btn-outline-secondary">Change Password</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

const avatarInput = document.getElementById('avatar');
if (avatarInput) {
    avatarInput.addEventListener('change', function() {
        previewImage(this, 'avatarPreview');
    });
}

const orgLogoInput = document.getElementById('org_logo');
if (orgLogoInput) {
    orgLogoInput.addEventListener('change', function() {
        previewImage(this, 'orgLogoPreview');
    });
}
</script>