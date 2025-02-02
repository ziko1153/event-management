<div class="container py-5">
    <h2 class="mb-4">My Registered Events</h2>

    <?php if (empty($registrations['data'])): ?>
        <div class="alert alert-info">
            You haven't registered for any events yet.
            <a href="/events/search" class="alert-link">Browse available events</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($registrations['data'] as $registration): ?>
                <div class="col-md-6">
                    <a href="/user/my-events/<?= $registration['slug'] ?>" class="text-decoration-none">
                        <div class="card h-100 hover-shadow">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <img src="/<?= $registration['thumbnail'] ?>"
                                        class="img-fluid h-100 object-fit-cover rounded-start"
                                        alt="<?= $registration['title'] ?>">
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <h5 class="card-title text-dark"><?= $registration['title'] ?></h5>
                                        <p class="text-muted small mb-2">
                                            <i class="bi bi-calendar3"></i>
                                            <?= date('M d, Y', strtotime($registration['start_date'])) ?>
                                        </p>
                                        <p class="text-muted small mb-2">
                                            <i class="bi bi-geo-alt"></i>
                                            <?= $registration['location'] ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span
                                                class="badge bg-<?= $registration['payment_status'] === 'completed' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($registration['payment_status']) ?>
                                            </span>
                                            <span class="text-muted small">
                                                #<?= $registration['registration_number'] ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($registrations['total_pages'] > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($registrations['current_page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="/user/my-events?page=<?= $registrations['current_page'] - 1 ?>">
                                Previous
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $registrations['total_pages']; $i++): ?>
                        <li class="page-item <?= $i === $registrations['current_page'] ? 'active' : '' ?>">
                            <a class="page-link" href="/user/my-events?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($registrations['current_page'] < $registrations['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="/user/my-events?page=<?= $registrations['current_page'] + 1 ?>">
                                Next
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    .hover-shadow:hover {
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        transition: box-shadow .3s ease-in-out;
    }
</style>