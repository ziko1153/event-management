<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Reset Password</h3>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="/reset-password" method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="password_confirmation"
                            name="password_confirmation" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <p class="mb-0">Remember your password? <a href="/login" class="text-decoration-none">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>