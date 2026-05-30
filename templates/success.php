<?php include __DIR__ . '/_i18n_start.php'; ?>
<?php
style('core', 'guest');
style('enhanced_registration', 'enhanced');

$message = $_['message'] ?? 'Ihr Registrierungsantrag wurde gespeichert und wartet auf Freigabe.';
$redirectUrl = $_['redirect_url'] ?? '/login';
$countdown = 10;
$buttonText = ($redirectUrl === '/login') ? 'Zur Anmeldung' : 'Weiter';
?>
<meta http-equiv="refresh" content="<?php p((string)$countdown); ?>;url=<?php p($redirectUrl); ?>">

<style>
.nc-result-card{max-width:440px;margin:0 auto;text-align:center}
.nc-result-badge{display:inline-flex;align-items:center;justify-content:center;width:54px;height:54px;border-radius:999px;background:#e8f7ee;color:#136b2e;font-size:28px;margin-bottom:14px}
.nc-result-title{margin-bottom:10px}
.nc-result-text{color:#555;line-height:1.5;margin:12px 0}
.nc-result-muted{color:#777;font-size:13px;margin-top:12px}
.nc-countdown-bar{height:6px;background:#e5e7eb;border-radius:999px;overflow:hidden;margin:18px 0 6px}
.nc-countdown-progress{height:100%;width:100%;background:#16a34a;border-radius:999px;animation:ncCountdown <?php p((string)$countdown); ?>s linear forwards}
@keyframes ncCountdown{from{width:100%}to{width:0}}
</style>

<div class="guest-box login-box nc-result-card">
    <div class="nc-result-badge">✓</div>

    <h2 class="nc-result-title">Antrag gespeichert</h2>

    <p class="nc-result-text"><?php p($message); ?></p>

    <div class="nc-countdown-bar" aria-hidden="true">
        <div class="nc-countdown-progress"></div>
    </div>

    <p class="nc-result-muted">
        Automatische Weiterleitung in <strong><span id="nc-countdown"><?php p((string)$countdown); ?></span></strong> Sekunden.
    </p>

    <p style="margin-top:20px;">
        <a href="<?php p($redirectUrl); ?>" class="button primary"><?php p($buttonText); ?></a>
    </p>
</div>

<script>
(function () {
    var seconds = <?php p((string)$countdown); ?>;
    var target = <?php echo json_encode($redirectUrl); ?>;
    var el = document.getElementById('nc-countdown');

    if (!el) {
        return;
    }

    var timer = setInterval(function () {
        seconds -= 1;
        el.textContent = seconds;

        if (seconds <= 0) {
            clearInterval(timer);
            window.location.href = target;
        }
    }, 1000);
})();
</script>
<?php include __DIR__ . '/_i18n_end.php'; ?>
