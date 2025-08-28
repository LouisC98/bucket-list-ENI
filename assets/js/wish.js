import {showNotification} from './utils.js';

document.addEventListener('turbo:load', function() {
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');
    const wishesContainer = document.getElementById('wishesContainer');
    const completeCheckbox = document.getElementById('complete');
    const incompleteCheckbox = document.getElementById('incomplete');
    const userIdInput = document.getElementById('userId');
    function fetchWishes() {
        const userId = userIdInput ? userIdInput.value : null;
        const searchValue = searchInput ? searchInput.value : '';
        const sortValue = sortSelect ? sortSelect.value : 'newest';
        const searchUrl = searchInput.dataset.searchUrl;

        let status = null;
        if (completeCheckbox.checked) status = true;
        else if (incompleteCheckbox.checked) status = false;

        console.log(userId)

        fetch(`${searchUrl}?search=${encodeURIComponent(searchValue)}&sort=${sortValue}&status=${status}&userId=${userId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            wishesContainer.innerHTML = html;
        });
    }

    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(fetchWishes, 300);
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', fetchWishes);
    }

    if (completeCheckbox && incompleteCheckbox) {
        completeCheckbox.addEventListener('change', () => {
            incompleteCheckbox.checked = false;
            fetchWishes()
        });
        incompleteCheckbox.addEventListener('change', () => {
            completeCheckbox.checked = false;
            fetchWishes()
        });
    }

    const containers = [
        wishesContainer,
        document.getElementById('changeWishStatus')
    ].filter(Boolean);

    containers.forEach(container => {
        container.addEventListener('click', function(e) {
            if (e.target.hasAttribute('data-wish-url')) {
                const btn = e.target;
                fetch(btn.dataset.wishUrl, { method: 'PATCH' })
                    .then(response => {
                        if (response.ok) {
                            return response.json()
                        } else {
                            throw new Error('Erreur serveur');
                        }
                    })
                    .then(data => {
                        if (data.error) {
                            throw new Error(data.message);
                        } else if (data.success) {
                            if (data.isCompleted) {
                                btn.textContent = '✅';
                                showNotification('Wish réalisé ! ✅', 'success');
                            } else {
                                btn.textContent = '❌';
                                showNotification('Wish annulé ! ❌', 'error');
                            }
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        showNotification(error.message, 'error')
                    });
            }
        });
    });
});