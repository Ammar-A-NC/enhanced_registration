<?php include __DIR__ . '/_i18n_start.php'; ?>
<?php
style('core', 'guest');
$message = $_['message'] ?? 'Ein Fehler ist aufgetreten.';
?>

<div class="guest-box login-box" style="max-width:360px;margin:auto;">
    <h2 style="text-align:center;">Bestätigung fehlgeschlagen</h2>

    <p style="text-align:center;">
        <?php p($message); ?>
    </p>

    <p style="text-align:center;margin-top:18px;">
        <a href="/index.php/apps/enhanced_registration/checkmail">
            Code erneut eingeben
        </a>
    </p>

    <p style="text-align:center;margin-top:10px;">
        <a href="/index.php/apps/enhanced_registration/register">
            Neue E-Mail anfordern
        </a>
    </p>
</div>
<?php include __DIR__ . '/_i18n_end.php'; ?>
