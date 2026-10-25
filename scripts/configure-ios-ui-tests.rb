require 'xcodeproj'

# Add a test-only scheme in CI without changing the distributable App target.
project = Xcodeproj::Project.open('ios/App/App.xcodeproj')
app = project.targets.find { |target| target.name == 'App' }
tests = project.new_target(:ui_test_bundle, 'NativeUITests', :ios, '15.0')
tests.add_dependency(app)
group = project.main_group.new_group('NativeUITests')
source = group.new_file('../../tests/Native/iOS/NetpackUITests.swift')
tests.source_build_phase.add_file_reference(source)
tests.build_configurations.each do |config|
  config.build_settings['PRODUCT_BUNDLE_IDENTIFIER'] = 'com.netpack.courier.ai.uitests'
  config.build_settings['GENERATE_INFOPLIST_FILE'] = 'YES'
  config.build_settings['TEST_TARGET_NAME'] = 'App'
  config.build_settings['SWIFT_VERSION'] = '5.0'
  config.build_settings['CODE_SIGNING_ALLOWED'] = 'NO'
end
project.save
scheme = Xcodeproj::XCScheme.new
scheme.configure_with_targets(app, tests)
scheme.save_as(project.path, 'NativeUITests', true)
