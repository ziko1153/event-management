<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Forgot Password</h3>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success'];
                        unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <form action="/forgot-password" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Send Reset Link</button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <p class="mb-0">Remember your password? <a href="/login" class="text-decoration-none">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>