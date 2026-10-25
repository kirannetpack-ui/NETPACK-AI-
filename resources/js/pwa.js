// Register on public, sign-in and workspace pages, including installed webviews.
let installPrompt = null;
window.netpackInstallAvailable = false;

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    installPrompt = event;
    window.netpackInstallAvailable = true;
    window.dispatchEvent(new CustomEvent('pwa-installable'));
});

window.installNetpackApp = async () => {
    if (!installPrompt) return false;
    const prompt = installPrompt;
    installPrompt = null;
    window.netpackInstallAvailable = false;
    await prompt.prompt();
    const { outcome } = await prompt.userChoice;
    return outcome === 'accepted';
};

window.addEventListener('appinstalled', () => {
    installPrompt = null;
    window.netpackInstallAvailable = false;
    window.dispatchEvent(new CustomEvent('pwa-installed'));
});

if ('serviceWorker' in navigator && window.isSecureContext) {
    const register = () => navigator.serviceWorker
        .register('/service-worker.js', { scope: '/', updateViaCache: 'none' })
        .catch((error) => console.warn('Offline support could not start.', error));
    if (document.readyState === 'complete') register();
    else window.addEventListener('load', register, { once: true });
}
