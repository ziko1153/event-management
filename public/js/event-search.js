document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filterForm');
    const sortLinks = document.querySelectorAll('[data-sort]');
    const sortField = document.getElementById('sortField');

    const resetButton = document.createElement('button');
    resetButton.type = 'button';
    resetButton.className = 'btn btn-outline-secondary w-100 mt-2';
    resetButton.textContent = 'Reset Filters';
    filterForm.appendChild(resetButton);

    // Handle reset click
    resetButton.addEventListener('click', function () {
        window.location.href = '/events/search';
    });

    // Handle sort dropdown clicks
    sortLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            applyFilters({ sort: this.dataset.sort });
        });
    });

    // Handle form submission
    filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        applyFilters();
    });

    function applyFilters(additionalParams = {}) {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        const checkboxGroups = {};

        // Process form data
        for (const [key, value] of formData.entries()) {
            if (key.endsWith('[]')) {
                const baseKey = key.slice(0, -2);
                if (!checkboxGroups[baseKey]) {
                    checkboxGroups[baseKey] = [];
                }
                checkboxGroups[baseKey].push(value);
            } else if (value) {
                params.append(key, value);
            }
        }

        // Add checkbox groups to params
        for (const [key, values] of Object.entries(checkboxGroups)) {
            if (values.length > 0) {
                values.forEach(value => params.append(`${key}[]`, value));
            }
        }

        // Preserve keyword from URL if not in form
        const urlParams = new URLSearchParams(window.location.search);
        if (!params.has('keyword') && urlParams.has('keyword')) {
            params.set('keyword', urlParams.get('keyword'));
        }

        // Add additional params (like sort)
        for (const [key, value] of Object.entries(additionalParams)) {
            if (value) {
                params.set(key, value);
            }
        }

        // Redirect to search with filters
        window.location.href = `/events/search?${params.toString()}`;
    }

    const urlParams = new URLSearchParams(window.location.search);

    const keywordInput = filterForm.querySelector('input[name="keyword"]');
    if (keywordInput && urlParams.has('keyword')) {
        keywordInput.value = urlParams.get('keyword');
    }

    for (const [key, value] of urlParams.entries()) {
        if (!key.endsWith('[]')) {
            const input = filterForm.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = value;
            }
        }
    }

    // Handle checkboxes
    const checkboxes = filterForm.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        const name = checkbox.getAttribute('name');
        if (name && name.endsWith('[]')) {
            const values = urlParams.getAll(name);
            checkbox.checked = values.includes(checkbox.value);
        }
    });

    if (urlParams.has('sort')) {
        const activeSort = document.querySelector(`[data-sort="${urlParams.get('sort')}"]`);
        if (activeSort) {
            activeSort.classList.add('active');
            const dropdownButton = activeSort.closest('.dropdown').querySelector('.dropdown-toggle');
            if (dropdownButton) {
                dropdownButton.textContent = activeSort.textContent;
            }
        }
    }
});