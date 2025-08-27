import {showNotification} from './utils.js';

document.addEventListener('turbo:load', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value;
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchUrl = searchInput.dataset.searchUrl;
                fetch(`${searchUrl}?search=${encodeURIComponent(searchTerm)}`,  {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('wishesContainer').innerHTML = html;
                    });
            }, 300);
        });
    }

    const containers = [
        document.getElementById('wishesContainer'),
        document.getElementById('changeWishStatus')
    ].filter(Boolean);

    containers.forEach(container => {
        container.addEventListener('click', function(e) {
            if (e.target.hasAttribute('data-wish-url')) {
                const btn = e.target;
                fetch(btn.dataset.wishUrl, { method: 'PATCH' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.isCompleted) {
                            btn.textContent = '✅';
                            showNotification('Wish réalisé ! ✅', 'success');
                        } else {
                            btn.textContent = '❌';
                            showNotification('Wish annulé ! ❌', 'error');
                        }
                    });
            }
        });
    });
});
