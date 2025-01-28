<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Create Event</h5>
    </div>
    <div class="card-body">
        <div id="alertContainer"></div>
        <form id="eventForm" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required
                        value="<?= $_SESSION['old']['title'] ?? '' ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Organizer</label>
                    <select name="organizer_id" class="form-select" required>
                        <option value="">Select Organizer</option>
                        <?php foreach ($organizers as $organizer): ?>
                            <option value="<?= $organizer['id'] ?>"
                                <?= ($_SESSION['old']['organizer_id'] ?? '') == $organizer['id'] ? 'selected' : '' ?>>
                                <?= $organizer['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"
                        required><?= $_SESSION['old']['description'] ?? '' ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="datetime-local" name="start_date" class="form-control" required
                        value="<?= $_SESSION['old']['start_date'] ?? '' ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="datetime-local" name="end_date" class="form-control" required
                        value="<?= $_SESSION['old']['end_date'] ?? '' ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Registration Deadline</label>
                    <input type="datetime-local" name="registration_deadline" class="form-control" required
                        value="<?= $_SESSION['old']['registration_deadline'] ?? '' ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Event Type</label>
                    <select name="event_type" class="form-select" required>
                        <option value="">Select Type</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type ?>"
                                <?= ($_SESSION['old']['event_type'] ?? '') === $type ? 'selected' : '' ?>>
                                <?= ucfirst(strtolower($type)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Max Capacity</label>
                    <input type="number" name="max_capacity" class="form-control" required
                        value="<?= $_SESSION['old']['max_capacity'] ?? '0' ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control"
                        value="<?= $_SESSION['old']['location'] ?? '' ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Ticket Price</label>
                    <input type="number" step="0.01" name="ticket_price" class="form-control"
                        value="<?= $_SESSION['old']['ticket_price'] ?? '0.00' ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">Venue Details</label>
                    <textarea name="venue_details" class="form-control"
                        rows="3"><?= $_SESSION['old']['venue_details'] ?? '' ?></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Thumbnail</label>
                    <input type="file" name="thumbnail" class="form-control" accept="image/*" required>
                </div>

                <div class="col-md-6">
                    <div class="form-check">
                        <input type="checkbox" name="is_featured" class="form-check-input" value="1"
                            <?= ($_SESSION['old']['is_featured'] ?? '') ? 'checked' : '' ?>>
                        <label class="form-check-label">Featured Event</label>
                    </div>
                </div>


                <div class="col-12">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Create Event
                    </button>
                    <a href="/admin/events" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="module">
    import {
        registerEvent,
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
                preview.classList.add('mt-2', 'rounded');

                const container = input.parentNode;
                const oldPreview = container.querySelector('img');
                if (oldPreview) {
                    container.removeChild(oldPreview);
                }
                container.appendChild(preview);
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
        const thumbnailInput = form.querySelector('input[name="thumbnail"]');

        // Validate image before submission
        if (thumbnailInput.files.length === 0) {
            showError('Please select an image', alertContainer);
            thumbnailInput.classList.add('is-invalid');
            return;
        }

        clearFieldErrors();
        alertContainer.innerHTML = '';

        submitBtn.disabled = true;
        spinner.classList.remove('d-none');

        try {
            const formData = new FormData(form);
            const result = await registerEvent(formData);

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