<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Attendees for: <?= $event['title'] ?></h2>
        <div>
            <a href="/admin/events/<?= $event['slug'] ?>/attendees/download" class="btn btn-success">
                <i class="bi bi-download"></i> Download CSV
            </a>
            <a href="/admin/events" class="btn btn-secondary">Back to Events</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attendees)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No attendees found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($attendees as $attendee): ?>
                                <tr>
                                    <td><?= $attendee['name'] ?></td>
                                    <td><?= $attendee['email'] ?></td>
                                    <td><?= $attendee['phone'] ?></td>
                                    <td><?= $attendee['address'] ?></td>
                                    <td><?= date('M d, Y H:i:s', strtotime($attendee['registered_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>