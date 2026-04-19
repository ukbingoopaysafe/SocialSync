        </main>
    </div>
</div>
<?php if (isset($_SESSION['user_id']) && defined('ONESIGNAL_APP_ID') && ONESIGNAL_APP_ID): ?>
<!-- OneSignal Web SDK -->
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
    window.OneSignalDeferred = window.OneSignalDeferred || [];
    OneSignalDeferred.push(async function(OneSignal) {
        await OneSignal.init({ appId: "<?= ONESIGNAL_APP_ID ?>" });
        await OneSignal.login(String(<?= (int)$_SESSION['user_id'] ?>));
    });
</script>
<?php endif; ?>
