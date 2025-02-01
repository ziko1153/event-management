<header class="hero-section position-relative overflow-hidden">
    <div class="hero-overlay"></div>
    <div class="container position-relative z-3 py-5">
        <div class="row min-vh-50 align-items-center">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h1 class="display-3 fw-bold mb-3 animate__animated animate__fadeInUp">Discover Extraordinary Events
                </h1>
                <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s">Your gateway to unforgettable
                    experiences and memorable moments</p>

                <!-- Enhanced Search Form -->
                <form action="/events/search" method="GET"
                    class="search-form p-2 bg-white rounded-4 shadow-lg animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-0">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="keyword" class="form-control border-0"
                                    placeholder="Search events...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">Search Events</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</header>

<!-- Featured Events Section -->
<section class="featured-events py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title">Featured Events</h2>
            <a href="/events/search?is_featured=1" class="btn btn-outline-primary">View All</a>
        </div>

        <div class="row g-4">
            <?php foreach ($featuredEvents as $event): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 event-card border-0 shadow-sm">
                        <div class="position-relative">
                            <img src="img<?= $event['thumbnail'] ?>" class="card-img-top event-image"
                                alt="<?= $event['title'] ?>">
                            <?php if ($event['is_featured']): ?>
                                <div class="featured-badge">
                                    <i class="bi bi-star-fill"></i> Featured
                                </div>
                            <?php endif; ?>
                            <?php if (strtotime($event['registration_deadline']) > time()): ?>
                                <div class="countdown-timer" data-deadline="<?= $event['registration_deadline'] ?>">
                                    <!-- Countdown will be inserted by JS -->
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-<?= \App\Enums\EventTypeEnum::getTypeColor($event['event_type']) ?>">
                                    <?= ucfirst($event['event_type']) ?>
                                </span>
                                <span class="price-tag <?= $event['ticket_price'] > 0 ? 'bg-primary' : 'bg-success' ?>">
                                    <?= $event['ticket_price'] > 0 ? '$' . number_format($event['ticket_price'], 2) : 'Free' ?>
                                </span>
                            </div>

                            <h5 class="card-title mb-1"><?= $event['title'] ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-calendar3"></i> <?= date('M d, Y', strtotime($event['start_date'])) ?>
                            </p>

                            <div class="capacity-wrapper mb-3">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>Capacity</span>
                                    <span><?= $event['current_capacity'] ?>/<?= $event['max_capacity'] ?></span>
                                </div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-success"
                                        style="width: <?= ($event['current_capacity'] / $event['max_capacity']) * 100 ?>%">
                                    </div>
                                </div>
                            </div>

                            <p class="card-text text-muted mb-3"><?= substr($event['description'], 0, 100) ?>...</p>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="/events/register/<?= $event['slug'] ?>" class="btn btn-outline-primary btn-sm">View
                                    Details</a>
                                <div class="organizer">
                                    <span class="small ms-2"><?= $event['organizer_name'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>