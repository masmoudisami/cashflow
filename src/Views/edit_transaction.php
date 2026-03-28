<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Transaction - CashFlow</title>
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
        <h2>Modifier Transaction</h2>
        
        <form action="?page=update_transaction" method="POST" class="form-inline">
            <input type="hidden" name="id" value="<?= htmlspecialchars($transaction['id']) ?>">
            
            <div class="form-group">
                <label>Date:</label>
                <input type="date" name="date" value="<?= htmlspecialchars($transaction['transaction_date']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Catégorie:</label>
                <select name="category_id" id="category_id" required onchange="updateTransactionType()">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" data-type="<?= $cat['type'] ?>" <?= $cat['id'] == $transaction['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Montant:</label>
                <input type="number" step="0.001" name="amount" value="<?= htmlspecialchars($transaction['amount']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Description:</label>
                <input type="text" name="description" value="<?= htmlspecialchars($transaction['description']) ?>">
            </div>
            
            <div class="form-group">
                <label>Type:</label>
                <select name="type" id="transaction_type">
                    <option value="expense" <?= $transaction['type'] === 'expense' ? 'selected' : '' ?>>Dépense</option>
                    <option value="income" <?= $transaction['type'] === 'income' ? 'selected' : '' ?>>Revenu</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Paiement:</label>
                <select name="payment_method">
                    <option value="cash" <?= $transaction['payment_method'] === 'cash' ? 'selected' : '' ?>>Directe</option>
                    <option value="card" <?= $transaction['payment_method'] === 'card' ? 'selected' : '' ?>>Carte (Débit Différé)</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit">Enregistrer</button>
                <a href="?page=calendar" class="btn btn-cancel">Annuler</a>
            </div>
        </form>
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
        
        // Initialiser au chargement
        document.addEventListener('DOMContentLoaded', function() {
            updateTransactionType();
        });
    </script>
</body>
</html>