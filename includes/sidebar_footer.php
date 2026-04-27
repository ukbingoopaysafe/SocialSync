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
        window.reportOneSignalSubscriptionState = function(OneSignal, label = 'Push subscription state') {
            try {
                console.log(`[OneSignal] ${label}:`, {
                    permission: OneSignal.Notifications.permission,
                    optedIn: OneSignal.User?.PushSubscription?.optedIn,
                    id: OneSignal.User?.PushSubscription?.id,
                    token: OneSignal.User?.PushSubscription?.token
                });
            } catch (stateError) {
                console.warn('OneSignal subscription state warning:', stateError);
            }
        };
        window.syncOneSignalWorkspace = async function(OneSignal) {
            const userId = String(<?= (int)$_SESSION['user_id'] ?>);
            const companyId = String(<?= (int)($_SESSION['company_id'] ?? 1) ?>);

            await OneSignal.login(userId);
            await OneSignal.User.addAlias('workspace_user', `${userId}:${companyId}`);
        };

        window.performAppLogout = async function(event) {
            if (event) {
                event.preventDefault();
            }

            try {
                if (window.OneSignal && typeof window.OneSignal.logout === 'function') {
                    await window.OneSignal.logout();
                } else {
                    await new Promise((resolve) => {
                        window.OneSignalDeferred.push(async function(OneSignal) {
                            try {
                                if (typeof OneSignal.logout === 'function') {
                                    await OneSignal.logout();
                                }
                            } catch (logoutError) {
                                console.warn('OneSignal logout warning:', logoutError);
                            }
                            resolve();
                        });
                    });
                }
            } catch (e) {
                console.warn('OneSignal logout warning:', e);
            }

            try {
                await fetch('api.php?action=logout', {
                    method: 'POST',
                    credentials: 'same-origin'
                });
            } finally {
                location.href = 'login.php';
            }
        };

        OneSignalDeferred.push(async function(OneSignal) {
            try {
                await OneSignal.init({
                    appId: "<?= ONESIGNAL_APP_ID ?>",
                    allowLocalhostAsSecureOrigin: true,
                    serviceWorkerParam: { scope: "/" },
                    serviceWorkerPath: "OneSignalSDKWorker.js"
                });

                OneSignal.User.PushSubscription.addEventListener('change', async function(event) {
                    console.log('[OneSignal] Push subscription changed:', event);

                    try {
                        await window.syncOneSignalWorkspace(OneSignal);
                        console.log('[OneSignal] Re-synced user/workspace after subscription change');
                    } catch (syncError) {
                        console.warn('OneSignal re-sync warning:', syncError);
                    }

                    window.reportOneSignalSubscriptionState(OneSignal, 'Push subscription state after change');
                });

                await window.syncOneSignalWorkspace(OneSignal);

                try {
                    if (!OneSignal.User.PushSubscription.optedIn) {
                        await OneSignal.User.PushSubscription.optIn();
                    }
                } catch (subscriptionError) {
                    console.warn('OneSignal push opt-in warning:', subscriptionError);
                }

                window.reportOneSignalSubscriptionState(OneSignal);
            } catch(e) {
                console.warn('OneSignal init failed:', e);
            }
        });
    }
</script>
<?php endif; ?>
