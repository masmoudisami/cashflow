<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Transactions - CashFlow</title>
    <link rel="stylesheet" href="public/css/style.css">
    <script src="public/js/app.js" defer></script>
</head>
<body>
    <nav>
        <div class="brand">CashFlow</div>
        <div class="links">
            <a href="<?= BASE_URL ?>/?page=dashboard">Accueil</a>
            <a href="<?= BASE_URL ?>/?page=transactions">Transactions</a>
            <a href="<?= BASE_URL ?>/?page=calendar">Calendrier</a>
            <a href="<?= BASE_URL ?>/?page=categories">Catégories</a>
            <a href="<?= BASE_URL ?>/?page=logout">Déconnexion</a>
        </div>
    </nav>
    <div class="container">
        <h2>Transactions</h2>
        
        <?php if (Session::has('error')): ?>
            <div class="alert error"><?= htmlspecialchars(Session::get('error')) ?></div>
            <?php Session::set('error', null); ?>
        <?php endif; ?>
        <?php if (Session::has('success')): ?>
            <div class="alert success"><?= htmlspecialchars(Session::get('success')) ?></div>
            <?php Session::set('success', null); ?>
        <?php endif; ?>

        <div class="filters">
            <input type="text" id="searchInput" placeholder="Filtrer par description..." onkeyup="filterTable()">
        </div>

        <div class="transaction-form-section">
            <h3>Ajouter une Transaction</h3>
            <form action="<?= BASE_URL ?>/?page=transaction_store" method="POST" class="form-inline">
                <div class="form-group">
                    <label>Catégorie:</label>
                    <select name="category_id" required>
                        <option value="">Sélectionner</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?> (<?= $cat['type'] === 'expense' ? 'Dépense' : 'Revenu' ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Montant:</label>
                    <input type="number" step="0.001" name="amount" placeholder="Montant" required min="0.001">
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <input type="text" name="description" placeholder="Description">
                </div>
                <div class="form-group">
                    <label>Date:</label>
                    <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Type:</label>
                    <select name="type">
                        <option value="expense">Dépense</option>
                        <option value="income">Revenu</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Paiement:</label>
                    <select name="payment_method">
                        <option value="cash">Directe</option>
                        <option value="card">Carte (Débit Différé)</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit">Ajouter</button>
                </div>
            </form>
            <p class="form-hint">
                💡 Pour gérer les catégories (ajouter, modifier, supprimer), allez dans le menu 
                <a href="<?= BASE_URL ?>/?page=categories"><strong>Catégories</strong></a>
            </p>
        </div>

        <div class="transactions-list">
            <h3>Liste des Transactions</h3>
            <table id="transactionTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Catégorie</th>
                        <th>Type</th>
                        <th>Montant</th>
                        <th>Paiement</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transactions) > 0): ?>
                        <?php foreach ($transactions as $t): ?>
                        <tr class="<?= ($t['payment_method'] === 'card') ? 'card-row' : '' ?>">
                            <td><?= $t['transaction_date'] ?></td>
                            <td><?= htmlspecialchars($t['description']) ?></td>
                            <td><?= htmlspecialchars($t['category_name']) ?></td>
                            <td>
                                <span class="badge badge-<?= $t['type'] ?>">
                                    <?= $t['type'] === 'expense' ? 'Dépense' : 'Revenu' ?>
                                </span>
                            </td>
                            <td><?= formatNumber($t['amount']) ?></td>
                            <td>
                                <?= ($t['payment_method'] === 'cash') ? 'Directe' : 'Carte' ?>
                                <?= ($t['is_deferred'] == 1) ? '<span class="badge-deferred">Différé</span>' : '' ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/?page=transaction_delete&id=<?= $t['id'] ?>" class="text-danger" onclick="return confirm('Supprimer cette transaction ?')">Supprimer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-data">Aucune transaction enregistrée</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
function formatNumber($number) {
    $num = (float)$number;
    $formatted = number_format($num, 3, ',', ' ');
    return $formatted;
}
?>