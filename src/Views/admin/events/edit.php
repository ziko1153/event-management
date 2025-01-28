<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit Event</h5>
        <a href="/admin/events" class="btn btn-secondary btn-sm">Back to List</a>
    </div>
    <div class="card-body">
        <div id="alertContainer"></div>
        <form id="eventForm" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required
                        value="<?= htmlspecialchars($event['title']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Organizer</label>
                    <select name="organizer_id" class="form-select" required>
                        <option value="">Select Organizer</option>
                        <?php foreach ($organizers as $organizer): ?>
                            <option value="<?= $organizer['id'] ?>"
                                <?= $event['organizer_id'] == $organizer['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($organizer['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"
                        required><?= htmlspecialchars($event['description']) ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="datetime-local" name="start_date" class="form-control" required
                        value="<?= date('Y-m-d\TH:i', strtotime($event['start_date'])) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="datetime-local" name="end_date" class="form-control" required
                        value="<?= date('Y-m-d\TH:i', strtotime($event['end_date'])) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Registration Deadline</label>
                    <input type="datetime-local" name="registration_deadline" class="form-control" required
                        value="<?= date('Y-m-d\TH:i', strtotime($event['registration_deadline'])) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Event Type</label>
                    <select name="event_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type ?>" <?= $event['event_type'] === $type ? 'selected' : '' ?>>
                                <?= ucfirst(strtolower($type)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Event Status</label>
                    <select name="status" class="form-select" required>
                        <option value="">Select Status</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?= $status ?>" <?= $event['status'] === $status ? 'selected' : '' ?>>
                                <?= ucfirst(strtolower($status)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Max Capacity</label>
                    <input type="number" name="max_capacity" class="form-control" required
                        value="<?= $event['max_capacity'] ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control"
                        value="<?= htmlspecialchars($event['location']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Ticket Price</label>
                    <input type="number" step="0.01" name="ticket_price" class="form-control"
                        value="<?= number_format($event['ticket_price'], 2) ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">Venue Details</label>
                    <textarea name="venue_details" class="form-control"
                        rows="3"><?= htmlspecialchars($event['venue_details']) ?></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Thumbnail</label>
                    <div class="thumbnail-container">
                        <?php if ($event['thumbnail']): ?>
                            <div class="current-thumbnail mb-2">
                                <img src="/<?= $event['thumbnail'] ?>" alt="Current thumbnail" class="rounded"
                                    style="max-width: 200px">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-check">
                        <input id="is_featured" type="checkbox" name="is_featured" class="form-check-input" value="1"
                            <?= $event['is_featured'] ? 'checked' : '' ?>>
                        <label for="is_featured" class="form-check-label">Featured Event</label>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Update Event
                    </button>
                    <a href="/admin/events" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="module">
    import {
        updateEvent,
        showFieldErrors,
        clearFieldErrors,
        showSuccess,
        showError
    } from '/js/event-handler.js';

    const handleImagePreview = (input) => {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                const preview = document.createElement('img');
                preview.src = e.target.result;
                preview.style.maxWidth = '200px';
                preview.classList.add('rounded');

                const container = input.closest('.thumbnail-container');
                const currentThumbnail = container.querySelector('.current-thumbnail');
                if (currentThumbnail) {
                    currentThumbnail.style.display = 'none';
                }

                const oldPreview = container.querySelector('img:not(.current-thumbnail img)');
                if (oldPreview) {
                    oldPreview.remove();
                }

                input.parentNode.insertBefore(preview, input.nextSibling);
            };
            reader.readAsDataURL(file);
        }
    };

    document.querySelector('input[name="thumbnail"]').addEventListener('change', function() {
        handleImagePreview(this);
    });

    document.getElementById('eventForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const form = this;
        const submitBtn = form.querySelector('#submitBtn');
        const spinner = submitBtn.querySelector('.spinner-border');
        const alertContainer = document.getElementById('alertContainer');

        clearFieldErrors();
        alertContainer.innerHTML = '';

        submitBtn.disabled = true;
        spinner.classList.remove('d-none');

        try {
            const formData = new FormData(form);
            const result = await updateEvent(formData, <?= $event['id'] ?>);

            showSuccess(result.message, alertContainer);

            window.location.href = '/admin/events';

        } catch (error) {
            if (error.validationErrors) {
                showFieldErrors(error.validationErrors);
                showError('Please correct the errors below.', alertContainer);
            } else {
                showError(error.message, alertContainer);
            }

            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        } finally {
            spinner.classList.add('d-none');
            submitBtn.disabled = false;
        }
    });
</script>