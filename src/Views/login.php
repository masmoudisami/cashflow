<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - CashFlow</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container-auth">
        <h1>Connexion</h1>
        <?php if (Session::has('error')): ?>
            <div class="alert error"><?= htmlspecialchars(Session::get('error')) ?></div>
            <?php Session::set('error', null); ?>
        <?php endif; ?>
        <?php if (Session::has('success')): ?>
            <div class="alert success"><?= htmlspecialchars(Session::get('success')) ?></div>
            <?php Session::set('success', null); ?>
        <?php endif; ?>
        <form action="<?= BASE_URL ?>/?page=authenticate" method="POST">
            <input type="text" name="username" placeholder="Utilisateur" required autocomplete="username">
            <input type="password" name="password" placeholder="Mot de passe" required autocomplete="current-password">
            <button type="submit">Se connecter</button>
        </form>
        
        <?php
        $userModel = new User();
        $registrationDisabled = $userModel->hasAnyUser();
        ?>
        
        <?php if (!$registrationDisabled): ?>
            <div class="register-section">
                <p class="register-info">Première connexion ? Créez votre compte :</p>
                <form action="<?= BASE_URL ?>/?page=register" method="POST" class="mt-2">
                    <input type="text" name="username" placeholder="Nouvel utilisateur" required minlength="3" maxlength="50" autocomplete="username">
                    <input type="password" name="password" placeholder="Nouveau mot de passe" required minlength="6" autocomplete="new-password">
                    <button type="submit" class="btn-register">S'inscrire</button>
                </form>
            </div>
        <?php else: ?>
            <div class="register-disabled">
                <p class="disabled-info">⚠️ Les inscriptions sont désactivées</p>
                <p class="disabled-sub">Un compte administrateur existe déjà.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>