import { Capacitor } from '@capacitor/core';
import { App } from '@capacitor/app';
import { Browser } from '@capacitor/browser';
import { Network } from '@capacitor/network';
import { SplashScreen } from '@capacitor/splash-screen';
import { StatusBar, Style } from '@capacitor/status-bar';

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

    // External support links belong in the system browser, outside the session.
    document.addEventListener('click', (event) => {
        const link = event.target.closest?.('a[href]');
        if (!link || event.defaultPrevented) return;
        const url = new URL(link.href, window.location.href);
        if (url.protocol === 'https:' && url.origin !== window.location.origin) {
            event.preventDefault();
            Browser.open({ url: url.href }).catch(console.warn);
        }
    });
}
