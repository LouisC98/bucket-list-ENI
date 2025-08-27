/**
 * Affiche une notification temporaire
 * @param {string} message - Le message à afficher
 * @param {string} type - Le type de notification ('success', 'error', 'warning', 'info')
 * @param {number} duration - Durée d'affichage en millisecondes (défaut: 3000)
 */
export function showNotification(message, type = 'success', duration = 3000) {
    const notification = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-400' : 'bg-red-400';
    notification.className = `absolute right-3 top-10 z-50 px-4 py-2 rounded text-white ${bgColor}`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => notification.remove(), duration);
}