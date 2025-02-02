<!-- All Events Section -->
<section class="all-events py-5 bg-light">
    <div class="container">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Filter Events</h5>
                        <form id="filterForm" method="GET" action="/events/search">

                            <div class="mb-3">
                                <label class="form-label">Keyword</label>
                                <input class="form-control" name="keyword"
                                    value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">

                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input id="is_featured" type="checkbox" name="is_featured" value="1"
                                        class="form-check-input"
                                        <?= isset($_GET['is_featured']) &&  $_GET['is_featured'] == 1 ? 'checked' : '' ?>>
                                    <label for="is_featured" class="form-check-label">Featured</label>
                                </div>
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Event Type</label>
                                <?php
                                $selectedTypes = $_GET['types'] ?? [];
                                foreach ($types as $type):
                                ?>
                                <div class="form-check">
                                    <input id="<?= $type ?>" type="checkbox" name="types[]" value="<?= $type ?>"
                                        class="form-check-input"
                                        <?= in_array($type, $selectedTypes) ? 'checked' : '' ?>>
                                    <label for="<?= $type ?>" class="form-check-label"><?= ucfirst($type) ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Price Range</label>
                                <div class="d-flex gap-2">
                                    <input type="number" name="min_price" class="form-control form-control-sm"
                                        placeholder="Min" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
                                    <input type="number" name="max_price" class="form-control form-control-sm"
                                        placeholder="Max" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date Range</label>
                                <input type="date" name="start_date" class="form-control form-control-sm mb-2"
                                    value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
                                <input type="date" name="end_date" class="form-control form-control-sm"
                                    value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
                            </div>

                            <!-- Sort field hidden input -->
                            <input type="hidden" name="sort" id="sortField"
                                value="<?= htmlspecialchars($_GET['sort'] ?? '') ?>">

                            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Events Grid -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title">All Events</h2>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                            data-bs-toggle="dropdown">
                            Sort By
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-sort="date_asc">Date (Ascending)</a></li>
                            <li><a class="dropdown-item" href="#" data-sort="date_desc">Date (Descending)</a></li>
                            <li><a class="dropdown-item" href="#" data-sort="price_asc">Price (Low to High)</a></li>
                            <li><a class="dropdown-item" href="#" data-sort="price_desc">Price (High to Low)</a></li>
                        </ul>
                    </div>
                </div>

                <div class="row g-4" id="eventsGrid">
                    <?php if (empty($events)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="alert alert-info">
                            <i class="bi bi-calendar-x fs-4 d-block mb-2"></i>
                            <h4>No Events Found</h4>
                            <p class="mb-0">Try adjusting your search criteria or filters</p>
                        </div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($events as $event): ?>
                    <div class="col-md-6">
                        <div class="card h-100 event-card border-0 shadow-sm">
                            <div class="row g-0">
                                <div class="col-md-4 position-relative">
                                    <img src="/<?= $event['thumbnail'] ?>"
                                        class="img-fluid h-100 object-fit-cover rounded-start"
                                        alt="<?= $event['title'] ?>">
                                    <?php if ($event['is_featured']): ?>
                                    <div class="featured-badge">
                                        <i class="bi bi-star-fill"></i> Featured
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">


                                            <span
                                                class="badge bg-<?= \App\Enums\EventTypeEnum::getTypeColor($event['event_type']) ?>">
                                                <?= ucfirst($event['event_type']) ?>
                                            </span>
                                            <span
                                                class="price-tag <?= $event['ticket_price'] > 0 ? 'bg-primary' : 'bg-success' ?>">
                                                <?= $event['ticket_price'] > 0 ? '৳ ' . number_format($event['ticket_price'], 2) : 'Free' ?>
                                            </span>
                                        </div>

                                        <h5 class="card-title mb-1"><?= $event['title'] ?></h5>
                                        <p class="text-muted small mb-2">
                                            <i class="bi bi-calendar3"></i>
                                            <?= date('M d, Y', strtotime($event['start_date'])) ?>
                                        </p>

                                        <div class="capacity-wrapper mb-2">
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar bg-success"
                                                    style="width: <?= ($event['current_capacity'] / $event['max_capacity']) * 100 ?>%">
                                                </div>
                                            </div>
                                        </div>

                                        <a href="/events/register/<?= $event['slug'] ?>"
                                            class="btn btn-sm btn-outline-primary">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php
                        $queryParams = $_GET;
                        unset($queryParams['page']);
                        $queryString = '';
                        foreach ($queryParams as $key => $value) {
                            if (is_array($value)) {
                                foreach ($value as $v) {
                                    $queryString .= '&' . urlencode($key) . '[]=' . urlencode($v);
                                }
                            } else {
                                $queryString .= '&' . urlencode($key) . '=' . urlencode($value);
                            }
                        }
                        $queryString = ltrim($queryString, '&');

                        for ($i = 1; $i <= $totalPages; $i++):
                            $pageUrl = "?page={$i}" . ($queryString ? "&{$queryString}" : "");
                        ?>
                        <li class="page-item <?= $currentPage == $i ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $pageUrl ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</section>
<script src="/js/event-search.js"></script>