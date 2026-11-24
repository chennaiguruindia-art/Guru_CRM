@auth
<script>
    (function () {
        // Automatic Idle Logout after 30 minutes of no user activity
        const IDLE_TIMEOUT_MS = 30 * 60 * 1000; // 30 minutes
        const STORAGE_KEY = 'crm_last_user_activity';

        function recordActivity() {
            try {
                localStorage.setItem(STORAGE_KEY, Date.now().toString());
            } catch (e) {}
        }

        // Initialize activity on page load
        recordActivity();

        // Listen for user interactions (mouse, keyboard, touch, scroll)
        const activityEvents = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
        activityEvents.forEach(function (eventName) {
            window.addEventListener(eventName, recordActivity, { passive: true });
        });

        // Check activity across all browser tabs every 10 seconds
        setInterval(function () {
            let lastActive = parseInt(localStorage.getItem(STORAGE_KEY) || Date.now(), 10);
            if (Date.now() - lastActive >= IDLE_TIMEOUT_MS) {
                // Clear the storage key to prevent repeated trigger
                try {
                    localStorage.removeItem(STORAGE_KEY);
                } catch (e) {}

                // Submit hidden logout form with timeout parameter
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('logout') }}";

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = "{{ csrf_token() }}";
                form.appendChild(csrfInput);

                const timeoutInput = document.createElement('input');
                timeoutInput.type = 'hidden';
                timeoutInput.name = 'timeout';
                timeoutInput.value = '1';
                form.appendChild(timeoutInput);

                document.body.appendChild(form);
                form.submit();
            }
        }, 10000);
    })();
</script>
@endauth
