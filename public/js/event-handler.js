const registerEvent = async (formData) => {
    try {
        const response = await fetch('/admin/events/create', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (!response.ok) {
            if (result.errors) {
                throw {
                    validationErrors: result.errors,
                    message: result.error
                };
            }
            throw new Error(result.error || 'Failed to create event');
        }

        return result;
    } catch (error) {
        throw error;
    }
};

const updateEvent = async (formData, eventId) => {
    try {
        const response = await fetch(`/admin/events/update/${eventId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const result = await response.json();

        if (!response.ok) {
            if (result.errors) {
                throw {
                    validationErrors: result.errors,
                    message: result.error
                };
            }
            throw new Error(result.error || 'Failed to update event');
        }

        return result;
    } catch (error) {
        throw error;
    }
};


const showFieldErrors = (errors) => {
    // Clear all existing error messages first
    clearFieldErrors();

    // Add new error messages
    Object.keys(errors).forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        if (input) {
            input.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = errors[field];
            input.parentNode.appendChild(feedback);
        }
    });
};

const clearFieldErrors = () => {
    document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
};

const showSuccess = (message, container) => {
    container.innerHTML = `
        <div class="alert alert-success alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
};

const showError = (message, container) => {
    container.innerHTML = `
        <div class="alert alert-danger alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
};

export {
    registerEvent,
    updateEvent,
    showFieldErrors,
    clearFieldErrors,
    showSuccess,
    showError
};