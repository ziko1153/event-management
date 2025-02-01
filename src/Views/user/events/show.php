<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Event Registration Details</h4>
                    <a href="/user/my-events" class="btn btn-light btn-sm">Back to My Events</a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <img src="/img<?= $registration['thumbnail'] ?>" class="img-fluid rounded"
                                alt="<?= $registration['title'] ?>">
                        </div>
                        <div class="col-md-8">
                            <h5><?= $registration['title'] ?></h5>
                            <p class="text-muted mb-2">
                                <i class="bi bi-calendar3"></i>
                                <?= date('F j, Y g:i A', strtotime($registration['start_date'])) ?>
                            </p>
                            <p class="text-muted mb-2">
                                <i class="bi bi-geo-alt"></i>
                                <?= $registration['location'] ?>
                            </p>
                            <p class="text-muted mb-0">
                                <i class="bi bi-person"></i>
                                Organized by: <?= $registration['organizer_name'] ?>
                            </p>
                        </div>
                    </div>

                    <div class="registration-info">
                        <h6 class="border-bottom pb-2">Registration Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Registration Number:</strong></p>
                                <p class="text-muted">#<?= $registration['registration_number'] ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Registration Date:</strong></p>
                                <p class="text-muted">
                                    <?= date('F j, Y g:i A', strtotime($registration['registered_at'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Payment Status:</strong></p>
                                <span
                                    class="badge bg-<?= $registration['payment_status'] === 'completed' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($registration['payment_status']) ?>
                                </span>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Amount Paid:</strong></p>
                                <p class="text-muted">৳<?= number_format($registration['amount'], 2) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>