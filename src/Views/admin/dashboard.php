<div class="row">
    <div class="col-md-4">
        <?php if (!isUserOrganizer()): ?>
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <p class="card-text h2"><?= $stats['total_users'] ?></p>
            </div>
        </div>
        <?php endif ?>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Events</h5>
                <p class="card-text h2"><?= $stats['total_events'] ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <h3>Recent Events</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Organizer</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats['recent_events'] as $event): ?>
                <tr>
                    <td><?= $event['title'] ?></td>
                    <td><?= $event['organizer_name'] ?></td>
                    <td><?= date('M d, Y', strtotime($event['created_at'])) ?></td>
                    <td><?= ucfirst($event['status']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>