import { Capacitor } from '@capacitor/core';
import { App } from '@capacitor/app';
import { Browser } from '@capacitor/browser';
import { Network } from '@capacitor/network';
import { SplashScreen } from '@capacitor/splash-screen';
import { StatusBar, Style } from '@capacitor/status-bar';
import { Filesystem, Directory } from '@capacitor/filesystem';
import { Share } from '@capacitor/share';

if (Capacitor.isNativePlatform()) {
    document.documentElement.classList.add('np-native');

    // Native webviews use the same HTTPS origin, session cookies, and CSRF forms.
    // Never copy credentials into localStorage or bridge them to another origin.
    const notice = document.createElement('div');
    notice.className = 'np-native-connection';
    notice.setAttribute('role', 'status');
    notice.textContent = 'Connection unavailable. Reconnect before submitting changes.';
    notice.hidden = true;
    document.body.append(notice);
    const connection = ({ connected }) => { notice.hidden = connected; };
    Network.getStatus().then(connection).catch(console.warn);
    Network.addListener('networkStatusChange', connection).catch(console.warn);
    SplashScreen.hide().catch(console.warn);
    StatusBar.setStyle({ style: Style.Light }).catch(console.warn);

    if (Capacitor.getPlatform() === 'android') {
        App.addListener('backButton', ({ canGoBack }) => {
            if (canGoBack) window.history.back();
            else App.minimizeApp();
        }).catch(console.warn);
    }

    async function shareDocument(url, suggestedName) {
        try {
            const response = await fetch(url, { credentials: 'same-origin' });
            if (!response.ok || /text\/html/i.test(response.headers.get('Content-Type') || '')) {
                throw new Error('Document unavailable. Check your account and retry.');
            }
            const blob = await response.blob();
            if (blob.size > 25 * 1024 * 1024) throw new Error('Use the web portal to download files over 25 MB.');
            const disposition = response.headers.get('Content-Disposition') || '';
            const name = (disposition.match(/filename="?([^";]+)"?/i)?.[1] || suggestedName || 'document')
                .replace(/[^a-zA-Z0-9._-]/g, '_').slice(0, 120);
            const data = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(String(reader.result).split(',')[1]);
                reader.onerror = reject;
                reader.readAsDataURL(blob);
            });
            const file = await Filesystem.writeFile({ path: `exports/${Date.now()}-${name}`, data,
                directory: Directory.Cache, recursive: true });
            await Share.share({ title: name, files: [file.uri], dialogTitle: 'Save or share document' });
        } catch (error) {
            window.alert(error.message || 'Unable to open the document. Please retry.');
        }
    }

    // External support links belong in the system browser, outside the session.
    document.addEventListener('click', (event) => {
        const link = event.target.closest?.('a[href]');
        if (!link || event.defaultPrevented) return;
        const url = new URL(link.href, window.location.href);
        if (url.origin === window.location.origin &&
            (link.hasAttribute('download') || /\/(?:download|export)(?:\/|$)/.test(url.pathname) || /\.pdf$/i.test(url.pathname))) {
            event.preventDefault();
            event.stopImmediatePropagation();
            shareDocument(url.href, link.download || url.pathname.split('/').pop());
            return;
        }
        if (url.protocol === 'https:' && url.origin !== window.location.origin) {
            event.preventDefault();
            Browser.open({ url: url.href }).catch(console.warn);
        }
    }, true);
}
