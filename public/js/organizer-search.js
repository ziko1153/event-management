const initOrganizerSearch = () => {
    const organizerSearch = document.getElementById('organizerSearch');
    const organizerResults = document.getElementById('organizerResults');
    const organizerId = document.getElementById('organizerId');

    const searchOrganizers = debounce(async (value) => {
        if (value.length < 3) {
            organizerResults.classList.add('d-none');
            return;
        }

        try {
            const response = await fetch(`/admin/events/search-organizers?search=${encodeURIComponent(value)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message);
            }

            if (data.organizers.length === 0) {
                organizerResults.innerHTML = `
                    <div class="list-group shadow">
                        <div class="list-group-item text-muted">No organizers found</div>
                    </div>
                `;
            } else {
                organizerResults.innerHTML = `
                    <div class="list-group shadow">
                        ${data.organizers.map(org => `
                            <button type="button" 
                                    class="list-group-item list-group-item-action"
                                    data-id="${org.id}"
                                    data-name="${org.name}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>${org.name}</strong>
                                    <small class="text-muted">${org.email}</small>
                                </div>
                            </button>
                        `).join('')}
                    </div>
                `;
            }

            organizerResults.classList.remove('d-none');

            organizerResults.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    organizerId.value = btn.dataset.id;
                    organizerSearch.value = btn.dataset.name;
                    organizerResults.classList.add('d-none');
                    clearButton.classList.remove('d-none');
                });
            });

            // Update clear button handler
            clearButton.onclick = () => {
                organizerSearch.value = '';
                organizerId.value = '';
                clearButton.classList.add('d-none');
                organizerResults.classList.add('d-none');
            };
        } catch (error) {
            console.error('Organizer search error:', error);
            organizerResults.innerHTML = `
                <div class="list-group shadow">
                    <div class="list-group-item text-danger">Failed to fetch organizers</div>
                </div>
            `;
            organizerResults.classList.remove('d-none');
        }
    }, 300);

    const clearButton = document.createElement('button');
    clearButton.type = 'button';
    clearButton.className = 'btn btn-sm btn-link position-absolute end-0 top-50 translate-middle-y text-secondary';
    clearButton.style.zIndex = '5';
    clearButton.innerHTML = '<i class="bi bi-x-lg"></i>';
    clearButton.onclick = () => {
        organizerSearch.value = '';
        organizerId.value = '';
        clearButton.classList.add('d-none');
        organizerResults.classList.add('d-none');
    };

    // Add clear button only once
    organizerSearch.parentNode.appendChild(clearButton);

    // Show/hide clear button based on input value
    organizerSearch.addEventListener('input', (e) => {
        organizerId.value = '';
        searchOrganizers(e.target.value);
        clearButton.classList.toggle('d-none', !e.target.value);
    });

    // Clear button handler
    organizerSearch.addEventListener('search', () => {
        if (!organizerSearch.value) {
            organizerId.value = '';
            organizerResults.classList.add('d-none');
            // Trigger form submission to reset organizer filter
            // document.getElementById('eventFilterForm').dispatchEvent(new Event('submit'));
        }
    });

    // Close results when clicking outside
    document.addEventListener('click', (e) => {
        if (!organizerSearch.contains(e.target) && !organizerResults.contains(e.target)) {
            organizerResults.classList.add('d-none');
        }
    });

    // Add clear button to search input
    organizerSearch.setAttribute('type', 'search');
};

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export the initialization function
export { initOrganizerSearch };