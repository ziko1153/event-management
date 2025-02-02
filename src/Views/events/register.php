<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Event Registration</h4>
                </div>
                <div class="card-body">
                    <div class="event-summary mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <img src="/<?= $event['thumbnail'] ?>" class="img-fluid rounded"
                                    alt="<?= $event['title'] ?>">
                            </div>
                            <div class="col-md-8">
                                <h5><?= $event['title'] ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-calendar3"></i>
                                    <?= date('F j, Y g:i A', strtotime($event['start_date'])) ?>
                                </p>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-geo-alt"></i>
                                    <?= $event['location'] ?>
                                </p>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-people"></i>
                                    Available Spots: <?= $event['max_capacity'] - $event['current_capacity'] ?> /
                                    <?= $event['max_capacity'] ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="registration-details">
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th>Registration Fee:</th>
                                    <td>
                                        <?php if ($event['ticket_price'] > 0): ?>
                                            <span class="text-primary fw-bold">৳
                                                <?= number_format($event['ticket_price'], 2) ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Free</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Registration Deadline:</th>
                                    <td><?= date('F j, Y g:i A', strtotime($event['registration_deadline'])) ?></td>
                                </tr>

                                <tr>
                                    <th>Details:</th>
                                    <td><?= $event['description'] ?></td>
                                </tr>
                                <tr>
                                    <th>Venue Details:</th>
                                    <td><?= $event['venue_details'] ?></td>
                                </tr>
                                <tr>
                                    <th>Organizer:</th>
                                    <td><?= $event['organizer_name'] ?></td>
                                </tr>
                            </table>
                        </div>
                        <?php if (!isset($_SESSION['user'])): ?>
                            <div class="alert alert-info mb-4">
                                <i class="bi bi-info-circle"></i> Please login to register for this event.
                            </div>
                            <div class="d-grid gap-2">
                                <a href="/login?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right"></i> Login to Register
                                </a>
                                <a href="/events/search" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        <?php elseif ($isRegistered): ?>
                            <div class="alert alert-info mb-4">
                                <i class="bi bi-info-circle"></i> You are already registered for this event.
                                <a href="/user/my-events/<?= $event['slug'] ?>" class="alert-link">View your
                                    registrations</a>
                            </div>
                        <?php elseif ($isCapacityFull): ?>
                            <div class="alert alert-warning mb-4">
                                <i class="bi bi-exclamation-triangle"></i> This event is fully booked.
                            </div>
                        <?php elseif ($isDeadlinePassed): ?>
                            <div class="alert alert-warning mb-4">
                                <i class="bi bi-clock"></i> Registration deadline has passed.
                            </div>
                        <?php elseif ($isEventEnd): ?>
                            <div class="alert alert-warning mb-4">
                                <i class="bi bi-exclamation-triangle"></i> Sorry Registration End.
                            </div>
                        <?php else: ?>
                            <?php if ($event['ticket_price'] > 0): ?>
                                <div class="payment-options mb-4">
                                    <h6 class="mb-3">Payment Method</h6>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="payment_method" id="bkash"
                                            value="bkash" checked>
                                        <label class="form-check-label" for="bkash">
                                            bKash
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="payment_method" id="nagad"
                                            value="nagad">
                                        <label class="form-check-label" for="nagad">
                                            Nagad
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="rocket"
                                            value="rocket">
                                        <label class="form-check-label" for="rocket">
                                            Rocket
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="terms-conditions mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agree_terms" required>
                                    <label class="form-check-label" for="agree_terms">
                                        I agree to the event terms and conditions
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-primary" id="confirmRegistration" disabled>
                                    <?= $event['ticket_price'] > 0 ? 'Proceed to Payment' : 'Confirm Registration' ?>
                                </button>
                                <a href="/events/search" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Keep the confirmation modal only if registration is allowed -->
<?php if (!$isRegistered && !$isCapacityFull && !$isDeadlinePassed && !$isEventEnd && isset($_SESSION['user'])  && $_SESSION['user']['role'] == 'user'): ?>
    <!-- Confirmation Modal -->
    <div class="modal fade" id="registrationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Registration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to register for this event?</p>
                    <?php if ($event['ticket_price'] > 0): ?>
                        <p class="mb-0">You will be redirected to the payment gateway to complete your payment of
                            <strong>৳ <?= number_format($event['ticket_price'], 2) ?></strong>
                        </p>
                    <?php else: ?>
                        <p class="mb-0">This is a free event. Click confirm to complete your registration.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="/events/register/<?= $event['slug'] ?>" method="POST" id="registrationForm">
                        <?php if ($event['ticket_price'] > 0): ?>
                            <input type="hidden" name="payment_method" id="selected_payment_method" value="bkash">
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (!$isRegistered && !$isCapacityFull && !$isDeadlinePassed): ?>
            const agreeCheckbox = document.getElementById('agree_terms');
            const confirmButton = document.getElementById('confirmRegistration');
            const registrationModal = new bootstrap.Modal(document.getElementById('registrationModal'));
            const registrationForm = document.getElementById('registrationForm');

            <?php if ($event['ticket_price'] > 0): ?>
                const paymentMethods = document.getElementsByName('payment_method');
                const selectedPaymentMethod = document.getElementById('selected_payment_method');

                paymentMethods.forEach(method => {
                    method.addEventListener('change', function() {
                        selectedPaymentMethod.value = this.value;
                    });
                });
            <?php endif; ?>


            agreeCheckbox.addEventListener('change', function() {
                confirmButton.disabled = !this.checked;
            });

            confirmButton.addEventListener('click', function() {
                registrationModal.show();
            });

            registrationForm.addEventListener('submit', function(e) {
                confirmButton.disabled = true;
                confirmButton.innerHTML =
                    '<span class="spinner-border spinner-border-sm"></span> Processing...';
            });
        <?php endif; ?>
    });
</script>