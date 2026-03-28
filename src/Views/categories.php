<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Catégories - CashFlow</title>
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
        <h2>Gestion des Catégories</h2>
        
        <?php if (Session::has('error')): ?>
            <div class="alert error"><?= htmlspecialchars(Session::get('error')) ?></div>
            <?php Session::set('error', null); ?>
        <?php endif; ?>
        <?php if (Session::has('success')): ?>
            <div class="alert success"><?= htmlspecialchars(Session::get('success')) ?></div>
            <?php Session::set('success', null); ?>
        <?php endif; ?>

        <div class="categories-section">
            <div class="category-form-card">
                <h3>Ajouter une Catégorie</h3>
                <form action="?page=add_category" method="POST" class="form-inline">
                    <div class="form-group">
                        <label>Nom:</label>
                        <input type="text" name="name" placeholder="Nom de la catégorie" required minlength="2" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label>Bloc:</label>
                        <select name="block_type" required>
                            <option value="revenus">📈 Revenus (Bleu)</option>
                            <option value="debits_directs">📉 Débits Directs (Rouge)</option>
                            <option value="debits_differes">💳 Débits Différés (Orange)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="categories-list">
            <h3 style="color: #3498db;">📈 Revenus</h3>
            <table class="categories-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Bloc</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $revenusCategories = array_filter($categories, function($c) {
                        return $c['block_type'] === 'revenus';
                    });
                    if (count($revenusCategories) > 0): 
                        foreach ($revenusCategories as $cat): 
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($cat['name']) ?></td>
                        <td><span class="badge" style="background: #3498db;">Revenus</span></td>
                        <td>
                            <a href="?page=edit_category&id=<?= $cat['id'] ?>" class="btn-edit">✎ Modifier</a>
                            <a href="?page=delete_category&id=<?= $cat['id'] ?>" class="btn-delete" onclick="return confirm('Supprimer cette catégorie ?')">🗑️ Supprimer</a>
                        </td>
                    </tr>
                    <?php 
                        endforeach;
                    else: 
                    ?>
                    <tr>
                        <td colspan="3" class="no-data">Aucune catégorie</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h3 style="color: #e74c3c;">📉 Débits Directs</h3>
            <table class="categories-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Bloc</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $directsCategories = array_filter($categories, function($c) {
                        return $c['block_type'] === 'debits_directs';
                    });
                    if (count($directsCategories) > 0): 
                        foreach ($directsCategories as $cat): 
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($cat['name']) ?></td>
                        <td><span class="badge" style="background: #e74c3c;">Débits Directs</span></td>
                        <td>
                            <a href="?page=edit_category&id=<?= $cat['id'] ?>" class="btn-edit">✎ Modifier</a>
                            <a href="?page=delete_category&id=<?= $cat['id'] ?>" class="btn-delete" onclick="return confirm('Supprimer cette catégorie ?')">🗑️ Supprimer</a>
                        </td>
                    </tr>
                    <?php 
                        endforeach;
                    else: 
                    ?>
                    <tr>
                        <td colspan="3" class="no-data">Aucune catégorie</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h3 style="color: #f39c12;">💳 Débits Différés</h3>
            <table class="categories-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Bloc</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $differesCategories = array_filter($categories, function($c) {
                        return $c['block_type'] === 'debits_differes';
                    });
                    if (count($differesCategories) > 0): 
                        foreach ($differesCategories as $cat): 
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($cat['name']) ?></td>
                        <td><span class="badge" style="background: #f39c12;">Débits Différés</span></td>
                        <td>
                            <a href="?page=edit_category&id=<?= $cat['id'] ?>" class="btn-edit">✎ Modifier</a>
                            <a href="?page=delete_category&id=<?= $cat['id'] ?>" class="btn-delete" onclick="return confirm('Supprimer cette catégorie ?')">🗑️ Supprimer</a>
                        </td>
                    </tr>
                    <?php 
                        endforeach;
                    else: 
                    ?>
                    <tr>
                        <td colspan="3" class="no-data">Aucune catégorie</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>