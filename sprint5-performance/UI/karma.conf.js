module.exports = function (config) {
  config.set({
    basePath: '',
    frameworks: ['jasmine', '@angular-devkit/build-angular'],
    plugins: [
      require('karma-jasmine'),
      require('karma-chrome-launcher'),
      require('karma-firefox-launcher'),
      require('karma-jasmine-html-reporter'),
      require('karma-coverage'),
      require('@angular-devkit/build-angular/plugins/karma')
    ],
    client: {
      clearContext: false,
      captureConsole: false,
      jasmine: {
        random: false
      }
    },
    jasmineHtmlReporter: {
      suppressAll: true
    },
    coverageReporter: {
      dir: require('path').join(__dirname, './coverage/my-angular-project'),
      subdir: '.',
      reporters: [
        { type: 'html' },
        { type: 'text-summary' }
      ]
    },
    reporters: ['progress'],
    port: 9876,
    colors: true,
    logLevel: config.LOG_ERROR,
    autoWatch: false,
    browsers: ['FirefoxHeadless'],
    singleRun: true,
    restartOnFileChange: false,
    failOnEmptyTestSuite: false,
    customLaunchers: {
      FirefoxHeadless: {
        base: 'Firefox',
        flags: ['-headless']
      }
    },
  });
};
