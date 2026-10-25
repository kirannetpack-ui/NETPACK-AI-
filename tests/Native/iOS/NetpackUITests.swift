import XCTest

final class NetpackUITests: XCTestCase {
    func testLoginAndRelaunchKeepTheSession() {
        continueAfterFailure = false
        let app = XCUIApplication(bundleIdentifier: "com.netpack.courier.ai")
        app.launch()
        let web = app.webViews.firstMatch
        XCTAssertTrue(web.waitForExistence(timeout: 60))
        let email = web.textFields.firstMatch
        XCTAssertTrue(email.waitForExistence(timeout: 90))
        email.tap()
        email.typeText("superadmin@netpack.test")

        let password = web.secureTextFields.firstMatch
        for _ in 0..<3 {
            if password.isHittable { break }
            app.swipeUp()
        }
        password.tap()
        password.typeText("Netpack!Admin#2026")

        let remember = web.staticTexts["Keep me signed in"]
        for _ in 0..<3 {
            if remember.isHittable { break }
            app.swipeUp()
        }
        if remember.exists { remember.tap() }
        let submit = web.buttons["Sign in"].firstMatch
        for _ in 0..<3 {
            if submit.isHittable { break }
            app.swipeUp()
        }
        submit.tap()
        let dashboard = web.staticTexts.matching(NSPredicate(format: "label CONTAINS %@", "Your operations, in focus.")).firstMatch
        XCTAssertTrue(dashboard.waitForExistence(timeout: 90))
        let screenshot = XCTAttachment(screenshot: app.screenshot())
        screenshot.name = "iPhone authenticated dashboard"
        screenshot.lifetime = .keepAlways
        add(screenshot)
        app.terminate()
        app.launch()
        XCTAssertTrue(dashboard.waitForExistence(timeout: 90))
    }
}
