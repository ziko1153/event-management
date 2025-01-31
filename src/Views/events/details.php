<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <img src="/<?= $event['thumbnail'] ?>" class="card-img-top" alt="<?= $event['title'] ?>">
                <div class="card-body">
                    <h2 class="card-title"><?= $event['title'] ?></h2>
                    <p class="text-muted">Organized by: <?= $event['organizer_name'] ?></p>

                    <div class="mb-4">
                        <h5>Event Details</h5>
                        <p><?= nl2br($event['description']) ?></p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Date & Time</h5>
                            <p>
                                <strong>Starts:</strong>
                                <?= date('F j, Y g:i A', strtotime($event['start_date'])) ?><br>
                                <strong>Ends:</strong> <?= date('F j, Y g:i A', strtotime($event['end_date'])) ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>Location</h5>
                            <p><?= $event['location'] ?><br><?= $event['venue_details'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Registration Details</h5>
                    <p class="card-text">
                        <strong>Price:</strong>
                        <?= $event['ticket_price'] ? '$' . number_format($event['ticket_price'], 2) : 'Free' ?>
                    </p>
                    <p class="card-text">
                        <strong>Available Spots:</strong>
                        <?= $event['max_capacity'] - $event['current_capacity'] ?> / <?= $event['max_capacity'] ?>
                    </p>
                    <p class="card-text">
                        <strong>Registration Deadline:</strong><br>
                        <?= date('F j, Y g:i A', strtotime($event['registration_deadline'])) ?>
                    </p>

                    <?php if (isset($_SESSION['user'])): ?>
                        <?php if ($isRegistered): ?>
                            <div class="alert alert-info">You are already registered for this event.</div>
                        <?php elseif ($event['current_capacity'] >= $event['max_capacity']): ?>
                            <div class="alert alert-warning">This event is fully booked.</div>
                        <?php elseif (strtotime($event['registration_deadline']) < time()): ?>
                            <div class="alert alert-warning">Registration deadline has passed.</div>
                        <?php else: ?>
                            <form action="/events/register/<?= $event['id'] ?>" method="POST">
                                <button type="submit" class="btn btn-primary w-100">Register Now</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="/login" class="btn btn-primary w-100">Login to Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>