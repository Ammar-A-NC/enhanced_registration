<?php
$urls = $_['urls'] ?? []; include __DIR__ . '/_i18n_start.php'; ?>
<div class="guest-box">
    <h2>Passwort zurücksetzen</h2>

    <?php if (!empty($_['message'])): ?>
        <p><?= htmlspecialchars($_['message']) ?></p>
    <?php endif; ?>

    <form method="post" action="<?php p($urls['passreset_submit'] ?? ''); ?>">
        <p>
            <input type="email" name="email" placeholder="E-Mail-Adresse" required style="width:100%;padding:12px;">
        </p>

        <p>
            <button type="submit" class="button primary">Code senden</button>
        </p>
    </form>

<p style="text-align:center;margin-top:18px;">
    <a href="/login" class="button">Zurück zum Login</a>
</p>
</div>
<?php include __DIR__ . '/_i18n_end.php'; ?>
