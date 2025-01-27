<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Register</h3>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="/register" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input value="<?= $_SESSION['old']['name'] ?? "" ?>" type="text" class="form-control" id="name"
                            name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input value="<?= $_SESSION['old']['email'] ?? "" ?>" type="email" class="form-control"
                            id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirmation"
                            name="password_confirmation" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <p class="mb-0">Already have an account? <a href="/login" class="text-decoration-none">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>