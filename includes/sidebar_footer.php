        </main>
    </div>
</div>
<?php if (isset($_SESSION['user_id']) && defined('ONESIGNAL_APP_ID') && ONESIGNAL_APP_ID): ?>
<!-- OneSignal Web SDK (Push Notifications - requires HTTPS) -->
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
    // OneSignal only works on secure origins (HTTPS or localhost)
    if (location.protocol === 'https:' || location.hostname === 'localhost') {
        window.OneSignalDeferred = window.OneSignalDeferred || [];
        OneSignalDeferred.push(async function(OneSignal) {
            try {
                await OneSignal.init({
                    appId: "<?= ONESIGNAL_APP_ID ?>",
                    allowLocalhostAsSecureOrigin: true
                });
                await OneSignal.login(String(<?= (int)$_SESSION['user_id'] ?>));
            } catch(e) {
                console.warn('OneSignal init failed:', e);
            }
        });
    }
</script>
<?php endif; ?>
