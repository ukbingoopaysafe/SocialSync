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
        window.getOneSignalSyncSignature = function() {
            const userId = String(<?= (int)$_SESSION['user_id'] ?>);
            const companyId = String(<?= (int)($_SESSION['company_id'] ?? 1) ?>);
            return {
                userId,
                companyId,
                signature: `${location.origin}|${userId}|${companyId}`
            };
        };
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
        window.syncOneSignalWorkspace = async function(OneSignal, force = false) {
            const syncData = window.getOneSignalSyncSignature();
            const lastSyncSignature = localStorage.getItem('onesignal_sync_signature');
            const currentSubscriptionId = OneSignal.User?.PushSubscription?.id || '';

            if (!force && lastSyncSignature === syncData.signature && currentSubscriptionId) {
                return false;
            }

            await OneSignal.login(syncData.userId);
            await OneSignal.User.addAlias('workspace_user', `${syncData.userId}:${syncData.companyId}`);
            localStorage.setItem('onesignal_sync_signature', syncData.signature);
            return true;
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

        const bootOneSignal = function() {
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
                        const synced = await window.syncOneSignalWorkspace(OneSignal, true);
                        if (synced) {
                            console.log('[OneSignal] Re-synced user/workspace after subscription change');
                        }
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
        };

        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(bootOneSignal, { timeout: 1500 });
        } else {
            window.setTimeout(bootOneSignal, 300);
        }
    }
</script>
<?php endif; ?>
