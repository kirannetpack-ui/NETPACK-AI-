# COURIER with NETPACK — Android and iOS

Application identifier: `com.netpack.courier.ai` on both platforms.
The native apps open `https://netpack-ai.vercel.app/dashboard`. The backend
selects each user's portal after login. Web, PWA and native apps use the same
database and account approval rules.

## Build and test

The **Native Android and iOS builds** GitHub Actions workflow compiles both
platforms, runs the Android WebView login/session test, launches the iPhone
simulator, and uploads the resulting files as artifacts. It can be run manually:

```sh
gh workflow run native-builds.yml
```

The workflow produces:

- `netpack-android`: a signed **debug APK for testing**, an **unsigned release
  AAB**, lint output, and Android instrumentation results.
- `netpack-ios`: an iOS simulator application ZIP, an **unsigned device archive**,
  an iPhone launch screenshot, and compiler logs.

The debug APK is installable on an Android phone after the owner allows
installation from that source. It is not the Google Play release. Simulator
applications cannot be installed on a physical iPhone. Device installation and
TestFlight require Apple signing and a provisioning profile.

For local platform development:

```sh
npm ci
npm run native:sync
npm run native:android
# Run this on a Mac with Xcode 26 or newer:
npm run native:ios
```

Android uses JDK 21, SDK 36, and a minimum Android API of 24. iOS has a minimum
deployment target of 15. Native assets are kept separate from Laravel's public
directory so PHP entry points and unrelated website files never enter the app.

## Signing for store delivery

The first release needs the owner's Google Play and Apple Developer accounts.
No store accounts, private keys or certificates are stored in this repository.

For a signed Android release, set these environment variables privately, then run
`android/gradlew bundleRelease` (or `gradlew.bat` on Windows) from `android/`:

- `NETPACK_ANDROID_KEYSTORE`: absolute path to the upload keystore.
- `NETPACK_ANDROID_STORE_PASSWORD`, `NETPACK_ANDROID_KEY_ALIAS`,
  `NETPACK_ANDROID_KEY_PASSWORD`: the keystore's signing values.
- `NETPACK_BUILD_NUMBER`: a monotonically increasing integer; default is 1.

For iOS, open `ios/App/App.xcodeproj`, select the owner's team under Signing &
Capabilities, confirm the bundle identifier, and archive with an Apple
distribution identity. Export through Xcode Organizer to TestFlight or App Store
Connect. An unsigned archive is a compiler deliverable, not a distributable IPA.

## Native behavior

- HTTPS and a restricted application host; no cleartext traffic or Android app
  backups of session data.
- Native Android back navigation, keyboard support and status/splash handling.
- Foreground camera, microphone and location access requested through the WebView
  only when a user activates those tools. Background location is not enabled.
- Native connection notice and a bundled recovery page when the hosted workspace
  cannot be reached. Transactions need a live connection and are not queued.
- Same-origin document download links keep the authenticated browser session and
  open the native save/share sheet. Temporary files use the app cache; documents
  over 25 MB are handled through the web portal.
- External HTTPS links open outside the authenticated workspace.
- Native apps do not register the PWA service worker or show a PWA install prompt.
- Properly sized launcher icons; opaque iOS app icon; iOS usage descriptions and
  the filesystem required-reason privacy manifest.

Firebase/APNs remote push delivery is not configured. Voice transcription uses
the existing browser capabilities and text entry remains available when speech
recognition is unsupported. Camera, location, voice and document-sharing behavior
still need acceptance testing on the intended physical devices. Store review,
privacy disclosures and signing remain owner-controlled release steps.
