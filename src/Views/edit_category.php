<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Catégorie - CashFlow</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <nav>
        <div class="brand">CashFlow</div>
        <div class="links">
            <a href="?page=dashboard">Accueil</a>
            <a href="?page=transactions">Transactions</a>
            <a href="?page=calendar">Calendrier</a>
            <a href="?page=categories">Catégories</a>
            <a href="?page=logout">Déconnexion</a>
        </div>
    </nav>
    <div class="container">
        <h2>Modifier la Catégorie</h2>
        
        <form action="?page=update_category" method="POST" class="form-category-edit">
            <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']) ?>">
            
            <div class="form-group">
                <label>Nom de la catégorie:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required minlength="2" maxlength="50">
            </div>
            
            <div class="form-group">
                <label>Bloc:</label>
                <select name="block_type" required>
                    <option value="revenus" <?= $category['block_type'] === 'revenus' ? 'selected' : '' ?>>📈 Revenus (Bleu)</option>
                    <option value="debits_directs" <?= $category['block_type'] === 'debits_directs' ? 'selected' : '' ?>>📉 Débits Directs (Rouge)</option>
                    <option value="debits_differes" <?= $category['block_type'] === 'debits_differes' ? 'selected' : '' ?>>💳 Débits Différés (Orange)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Type:</label>
                <select name="type" required>
                    <option value="income" <?= $category['type'] === 'income' ? 'selected' : '' ?>>Revenu</option>
                    <option value="expense" <?= $category['type'] === 'expense' ? 'selected' : '' ?>>Dépense</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-save">Enregistrer</button>
                <a href="?page=categories" class="btn-cancel">Annuler</a>
            </div>
        </form>
    </div>
</body>
</html>