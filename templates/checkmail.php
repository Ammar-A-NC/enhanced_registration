<?php include __DIR__ . '/_i18n_start.php'; ?>
<?php
style('core', 'guest');

$email = $_['email'] ?? '';
?>

<div class="guest-box">
    <h2>Bestätigungscode eingeben</h2>

    <p>
        Bitte prüfe deine E-Mail:
        <strong><?php p($email); ?></strong>
    </p>

    <form method="POST" action="/index.php/apps/enhanced_registration/checkmail">
        <input
            type="text"
            name="code"
            inputmode="numeric"
            pattern="[0-9]{8}"
            maxlength="8"
            placeholder="8-stelliger Bestätigungscode"
            required
            style="width:100%;margin-bottom:15px;"
        />

        <button type="submit" class="primary">
            Code prüfen
        </button>
    </form>

    <?php if (!empty($email)): ?>
        <form method="POST" action="/index.php/apps/enhanced_registration/resend-code" style="margin-top:10px;">
            <input type="hidden" name="email" value="<?php p($email); ?>">
            <button type="submit" class="button" style="width:100%;">Bestätigungscode erneut senden</button>
        </form>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/_i18n_end.php'; ?>
