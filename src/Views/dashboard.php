<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - CashFlow</title>
    <link rel="stylesheet" href="public/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <h2>Vue d'ensemble - <?= getMonthName(date('m')) ?> <?= date('Y') ?></h2>
        <div class="cards">
            <div class="card">
                <h3>Solde Courant</h3>
                <p><?= formatNumber($balance['current']) ?></p>
            </div>
            <div class="card">
                <h3>Solde Prévisionnel</h3>
                <p><?= formatNumber($balance['forecast']) ?></p>
            </div>
            <div class="card">
                <h3>Revenus</h3>
                <p><?= formatNumber($balance['income']) ?></p>
            </div>
            <div class="card">
                <h3>Dépenses (Totales)</h3>
                <p><?= formatNumber($balance['expense']) ?></p>
                <small style="color:#7f8c8d;font-size:0.75rem;">
                    Direct: <?= formatNumber($balance['expense_direct']) ?> | Différé: <?= formatNumber($balance['expense_deferred']) ?>
                </small>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="balanceChart"></canvas>
        </div>
        <div class="actions">
            <a href="<?= BASE_URL ?>/?page=export_csv" class="btn">Export CSV</a>
            <a href="<?= BASE_URL ?>/?page=export_pdf" class="btn">Export PDF</a>
        </div>
    </div>
    <script>
        const ctx = document.getElementById('balanceChart');
        if(ctx) {
            const data = {
                labels: ['Revenus', 'Dépenses Directes', 'Dépenses Différées'],
                datasets: [{
                    label: 'Mois en cours',
                    data: [<?= $balance['income'] ?>, <?= $balance['expense_direct'] ?>, <?= $balance['expense_deferred'] ?>],
                    backgroundColor: ['#2ecc71', '#e74c3c', '#f39c12']
                }]
            };
            new Chart(ctx, { type: 'bar', data: data });
        }
    </script>
</body>
</html>

<?php
function formatNumber($number) {
    $num = (float)$number;
    $formatted = number_format($num, 3, ',', ' ');
    return $formatted;
}

function getMonthName($month) {
    $months = [
        '01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
        '05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
        '09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
    ];
    return $months[$month] ?? '';
}
?>