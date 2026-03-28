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
            <a href="?page=dashboard">Accueil</a>
            <a href="?page=transactions">Transactions</a>
            <a href="?page=calendar">Calendrier</a>
            <a href="?page=categories">Catégories</a>
            <a href="?page=logout">Déconnexion</a>
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
            <form action="?page=transaction_store" method="POST" class="form-inline">
                <div class="form-group">
                    <label>Catégorie:</label>
                    <select name="category_id" id="category_id" required onchange="updateTransactionType()">
                        <option value="">Sélectionner</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" data-type="<?= $cat['type'] ?>">
                                <?= htmlspecialchars($cat['name']) ?> (<?= $cat['type'] === 'expense' ? 'Dépense' : 'Revenu' ?>)
                            </option>
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
                    <select name="type" id="transaction_type">
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
                💡 Le type de transaction (Dépense/Revenu) est défini automatiquement selon la catégorie sélectionnée.
                Pour gérer les catégories, allez dans le menu <a href="?page=categories"><strong>Catégories</strong></a>
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
                                <a href="?page=transaction_delete&id=<?= $t['id'] ?>" class="text-danger" onclick="return confirm('Supprimer cette transaction ?')">Supprimer</a>
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
    <script>
        function updateTransactionType() {
            var categorySelect = document.getElementById('category_id');
            var typeSelect = document.getElementById('transaction_type');
            
            if (categorySelect && typeSelect) {
                var selectedOption = categorySelect.options[categorySelect.selectedIndex];
                var categoryType = selectedOption.getAttribute('data-type');
                
                if (categoryType === 'expense') {
                    typeSelect.value = 'expense';
                } else if (categoryType === 'income') {
                    typeSelect.value = 'income';
                }
            }
        }
        
        // Initialiser au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            updateTransactionType();
        });
    </script>
</body>
</html>

<?php
function formatNumber($number) {
    $num = (float)$number;
    $formatted = number_format($num, 3, ',', ' ');
    return $formatted;
}
?>