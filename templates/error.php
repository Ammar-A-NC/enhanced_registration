<?php
$urls = $_['urls'] ?? []; include __DIR__ . '/_i18n_start.php'; ?>
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
        <a href="<?php p($urls['checkmail'] ?? '#'); ?>">
            Code erneut eingeben
        </a>
    </p>

    <p style="text-align:center;margin-top:10px;">
        <a href="<?php p($urls['register'] ?? '#'); ?>">
            Neue E-Mail anfordern
        </a>
    </p>
</div>
<?php include __DIR__ . '/_i18n_end.php'; ?>
