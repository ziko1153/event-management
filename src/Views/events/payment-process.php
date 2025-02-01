<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Payment Simulation</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h4>Registration #<?= $registration['registration_number'] ?></h4>
                        <p class="text-muted">Payment Method: <?= strtoupper($registration['payment_method']) ?></p>
                        <p class="text-muted">Amount: ৳<?= number_format($registration['amount'], 2) ?></p>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> This is a payment simulation. Choose an outcome:
                    </div>

                    <div class="d-grid gap-3">
                        <form action="/events/payment/complete/<?= $registration['registration_number'] ?>"
                            method="POST">
                            <input type="hidden" name="status" value="success">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle"></i> Simulate Successful Payment
                            </button>
                        </form>

                        <form action="/events/payment/complete/<?= $registration['registration_number'] ?>"
                            method="POST">
                            <input type="hidden" name="status" value="failed">
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-x-circle"></i> Simulate Failed Payment
                            </button>
                        </form>

                        <a href="/events/search" class="btn btn-outline-secondary">Cancel Payment</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>