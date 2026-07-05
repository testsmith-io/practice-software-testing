// Injected into index.html by instrument.js (coverage mode only).
// Periodically ships window.__coverage__ to the collector endpoint of
// server.js. One file per page load; within a page load, counters only grow,
// so overwriting with the latest snapshot is lossless.
(function () {
  'use strict';
  var sessionId =
    Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
  var endpoint = '/__coverage__?id=' + sessionId;

  function send() {
    var cov = window.__coverage__;
    if (!cov) return;
    try {
      var body = JSON.stringify(cov);
      // sendBeacon is capped at ~64KB in Chromium and fails silently above
      // that; coverage payloads are megabytes, so plain fetch is the default.
      if (body.length < 60000 && navigator.sendBeacon) {
        navigator.sendBeacon(endpoint, body);
      } else if (window.fetch) {
        fetch(endpoint, { method: 'POST', body: body }).catch(function () {});
      } else {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', endpoint, true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(body);
      }
    } catch (e) {
      /* never break the app because of coverage */
    }
  }

  setInterval(send, 10000);
  window.addEventListener('pagehide', send);
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'hidden') send();
  });
})();
