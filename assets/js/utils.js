/**
 * Affiche une notification temporaire
 * @param {string} message - Le message à afficher
 * @param {string} type - Le type de notification ('success', 'error', 'warning', 'info')
 * @param {number} duration - Durée d'affichage en millisecondes (défaut: 3000)
 */
export function showNotification(message, type = 'success', duration = 3000) {
    const container = getNotificationContainer();

    const notification = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-400' : 'bg-red-400';
    notification.className = `p-5 rounded text-white shadow-md opacity-90 font-semibold ${bgColor}`;
    notification.textContent = message;

    container.appendChild(notification);

    setTimeout(() => {
        notification.remove();
        if (container.childElementCount === 0) {
            container.remove();
        }
    }, duration);
}
function getNotificationContainer() {
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.className = 'absolute right-3 top-25 z-50 flex flex-col gap-2';
        document.body.appendChild(container);
    }
    return container;
}

document.addEventListener('turbo:load', () => {
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach((flashMessage) => {
        if (flashMessage.dataset.status !== 'error') {
            setTimeout(() => {
                flashMessage.remove();
            }, 5000);
        }
    });
});