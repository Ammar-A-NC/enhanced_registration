<?php
$urls = $_['urls'] ?? []; include __DIR__ . '/_i18n_start.php'; ?>
<?php $email = (string)($_['email'] ?? ''); ?>
<div class="guest-box">
    <h2>Bestätigungscode eingeben</h2>

    <?php if (!empty($_['message'])): ?>
        <p><?= htmlspecialchars($_['message']) ?></p>
    <?php endif; ?>

    <form method="get" action="<?php p($urls['passreset_verify'] ?? ''); ?>">
        <p>
            <input
                type="text"
                name="code"
                placeholder="8-stelliger Bestätigungscode"
                inputmode="numeric"
                pattern="[0-9]{8}"
                maxlength="8"
                autocomplete="one-time-code"
                required
                style="width:100%;padding:12px;"
            >
        </p>

        <p>
            <button type="submit" class="button primary">Weiter</button>
        </p>
    </form>

    <?php if (!empty($email)): ?>
        <form method="POST" action="<?php p($urls['passreset_resend'] ?? ''); ?>" style="margin-top:10px;">
            <input type="hidden" name="email" value="<?php p($email); ?>">
            <button type="submit" class="button" style="width:100%;">Bestätigungscode erneut senden</button>
        </form>
    <?php endif; ?>

    <p style="text-align:center;margin-top:18px;">
        <a href="/login" class="button">Zurück zum Login</a>
    </p>
</div>
<?php include __DIR__ . '/_i18n_end.php'; ?>
