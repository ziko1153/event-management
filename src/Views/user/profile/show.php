<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">My Profile</h4>
                </div>
                <div class="card-body">
                    <form action="/user/profile" method="POST" enctype="multipart/form-data">
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img src="/<?= $user['avatar'] ?>" class="rounded-circle" width="100" height="100"
                                    alt="Profile Avatar" id="avatarPreview">
                                <label for="avatar"
                                    class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-1"
                                    style="cursor: pointer;">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                            </div>
                            <input type="file" name="avatar" id="avatar" class="d-none" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control"
                                value="<?= oldData('name', $user['name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control"
                                value="<?= oldData('phone', $user['phone']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control"
                                rows="3"><?= oldData('address', $user['address']) ?></textarea>
                        </div>

                        <div class="mb-4">
                            <h6 class="mb-3">Change Password (optional)</h6>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('avatar').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
</script>