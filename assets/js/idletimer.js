// -------------------------------------------------------------------------------------
// Idle timer for logged-in pages. Added to a page by app_idle_timer_script() in
// SessionBootstrap.php, which sets these attributes on the script tag:
//   data-timeout    - seconds of no activity before the user is logged out
//   data-ping-url   - refreshes the server-side session while the user is active
//   data-logout-url - where to go when the timer runs out
// Activity is shared between tabs (localStorage), so working in one tab keeps the
// others from logging the user out.
// -------------------------------------------------------------------------------------
(function() {
    var script = document.currentScript;
    var timeout_ms = parseInt(script.getAttribute('data-timeout'), 10) * 1000;
    var ping_url = script.getAttribute('data-ping-url');
    var logout_url = script.getAttribute('data-logout-url');
    if (!(timeout_ms > 0)) {
        return;
    }

    var WARNING_MS = Math.min(60000, timeout_ms / 2); // show the warning this long before logout
    var PING_INTERVAL_MS = 30000;                      // ping the server at most this often
    var STORAGE_KEY = 'nsxmeetup_last_activity';

    var last_activity = Date.now();
    var last_ping = Date.now();   // loading the page counted as server activity
    var ping_timer = null;
    var logging_out = false;
    var warning = null;

    function SharedLastActivity() {
        try {
            return parseInt(localStorage.getItem(STORAGE_KEY), 10) || 0;
        } catch (e) {
            return 0;
        }
    }

    function RecordActivity() {
        var now = Date.now();
        if (now - last_activity < 1000) {
            return; // mousemove fires constantly - once a second is plenty
        }
        last_activity = now;
        try {
            localStorage.setItem(STORAGE_KEY, String(now));
        } catch (e) {}

        // Trailing throttle: the ping always goes out after the latest activity, so the
        // server's last_activity never falls behind what this timer thinks it is.
        if (ping_timer === null) {
            ping_timer = setTimeout(PingServer, Math.max(0, last_ping + PING_INTERVAL_MS - now));
        }
        CheckIdle();
    }

    function PingServer() {
        ping_timer = null;
        last_ping = Date.now();
        var request = new XMLHttpRequest();
        request.open('POST', ping_url);
        request.onload = function() {
            try {
                if (JSON.parse(request.responseText).status === 'expired') {
                    LogOut();
                }
            } catch (e) {}
        };
        request.send();
    }

    function LogOut() {
        if (logging_out) {
            return;
        }
        logging_out = true;
        window.location.href = logout_url;
    }

    function CheckIdle() {
        var idle_ms = Date.now() - Math.max(last_activity, SharedLastActivity());
        if (idle_ms >= timeout_ms) {
            LogOut();
        } else if (idle_ms >= timeout_ms - WARNING_MS) {
            ShowWarning(Math.ceil((timeout_ms - idle_ms) / 1000));
        } else if (warning) {
            warning.style.display = 'none';
        }
    }

    function ShowWarning(seconds_left) {
        if (!warning) {
            warning = document.createElement('div');
            warning.className = 'idle_timer_warning';
            warning.innerHTML = '<span></span> <a href="#" class="button small">Stay Logged In</a>';
            warning.querySelector('a').onclick = function(event) {
                event.preventDefault(); // the click itself counts as activity
            };
            document.body.appendChild(warning);
        }
        warning.querySelector('span').textContent =
            'You will be logged out in ' + seconds_left + ' second' + (seconds_left === 1 ? '' : 's') + ' due to inactivity.';
        warning.style.display = 'block';
    }

    ['mousemove', 'mousedown', 'keydown', 'wheel', 'scroll', 'touchstart'].forEach(function(name) {
        window.addEventListener(name, RecordActivity, {passive: true, capture: true});
    });
    try {
        localStorage.setItem(STORAGE_KEY, String(last_activity));
    } catch (e) {}

    // Checked by clock time rather than one long setTimeout, so a laptop waking from
    // sleep logs out right away instead of waiting out the rest of the timer.
    setInterval(CheckIdle, 1000);
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            CheckIdle();
        }
    });
})();
