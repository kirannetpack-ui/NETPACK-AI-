package com.netpack.courier.ai;

import static org.junit.Assert.*;

import android.webkit.WebView;
import androidx.test.core.app.ActivityScenario;
import androidx.test.ext.junit.runners.AndroidJUnit4;
import java.util.concurrent.CountDownLatch;
import java.util.concurrent.TimeUnit;
import java.util.concurrent.atomic.AtomicReference;
import org.junit.Test;
import org.junit.runner.RunWith;

@RunWith(AndroidJUnit4.class)
public class NativeLoginTest {
    private ActivityScenario<MainActivity> scenario;

    private String evaluate(String code) throws Exception {
        CountDownLatch complete = new CountDownLatch(1);
        AtomicReference<String> result = new AtomicReference<>();
        scenario.onActivity(activity -> {
            WebView web = activity.getBridge().getWebView();
            web.evaluateJavascript(code, value -> { result.set(value); complete.countDown(); });
        });
        assertTrue("WebView JavaScript did not respond", complete.await(10, TimeUnit.SECONDS));
        return result.get();
    }

    private void waitFor(String expression) throws Exception {
        long deadline = System.currentTimeMillis() + 90000;
        while (System.currentTimeMillis() < deadline) {
            if ("true".equals(evaluate(expression))) return;
            Thread.sleep(500);
        }
        fail("Timed out waiting for " + expression + "; page: " + evaluate("location.href"));
    }

    @Test
    public void hostedLoginAndSessionWorkInsideNativeWebView() throws Exception {
        try (ActivityScenario<MainActivity> active = ActivityScenario.launch(MainActivity.class)) {
            scenario = active;
            waitFor("Boolean(document.getElementById('loginForm'))");
            assertEquals("\"android\"", evaluate("window.Capacitor.getPlatform()"));
            evaluate("document.getElementById('email').value='superadmin@netpack.test';" +
                "document.getElementById('password').value='Netpack!Admin#2026';" +
                "document.getElementById('loginForm').requestSubmit(); true");
            waitFor("location.pathname === '/admin/dashboard' && Boolean(document.getElementById('main-content'))");
            evaluate("location.reload(); true");
            waitFor("location.pathname === '/admin/dashboard' && Boolean(document.getElementById('main-content'))");
            assertEquals("true", evaluate("document.documentElement.classList.contains('np-native')"));
        }
    }
}
