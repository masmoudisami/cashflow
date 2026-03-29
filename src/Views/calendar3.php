<?php
// Mode production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

if (!isset($year) || !isset($month)) {
    $year = date('Y');
    $month = date('m');
}

if (!isset($transactions) || !isset($categories) || !isset($balance)) {
    $transactions = [];
    $categories = [];
    $balance = [
        'forecast' => 0,
        'target_end_balance' => 0,
        'starting_balance' => 0,
        'current' => 0,
        'exceeds_target' => false
    ];
}

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

function getDayName($dayNum) {
    $days = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
    return $days[$dayNum];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier - CashFlow</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .matrix-calendar-wrapper {
            margin-top: 1.5rem;
            background: #fff;
            border-radius: 8px;
            padding: 1rem;
        }

        .matrix-calendar-container {
            overflow: auto;
            max-height: 70vh;
            position: relative;
        }
        
        .matrix-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.75rem;
            min-width: 1400px;
        }
        
        .matrix-table th,
        .matrix-table td {
            border-right: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            padding: 6px 4px;
            text-align: center;
            min-width: 50px;
        }

        /* Bordure gauche uniquement sur la première colonne */
        .matrix-table th:first-child,
        .matrix-table td:first-child {
            border-left: 1px solid #ddd;
        }

        /* Bordure haute uniquement sur le premier thead tr */
        .matrix-table thead tr:first-child th {
            border-top: 1px solid #ddd;
        }
        
        .matrix-table th {
            background: #34495e;
            color: #fff;
            font-weight: 600;
        }

        /* ── STICKY : ligne des dates ── */
        .matrix-table thead th {
            position: sticky;
            top: 0;
            z-index: 3;
        }

        /* ── STICKY : première colonne ── */
        .matrix-table th:first-child,
        .matrix-table td:first-child {
            position: sticky;
            left: 0;
            z-index: 2;
        }

        /* Coin supérieur gauche : au-dessus des deux axes */
        .matrix-table thead th:first-child {
            z-index: 5;
        }

        /* Ombres visuelles */
        .matrix-table thead th {
            box-shadow: 0 3px 6px rgba(0,0,0,0.18);
        }
        .matrix-table td:first-child {
            box-shadow: 3px 0 6px rgba(0,0,0,0.10);
        }

        /* Nécessaire pour que transform + z-index fonctionnent sur les cellules figées */
        .matrix-table thead th,
        .matrix-table td:first-child,
        .matrix-table th:first-child {
            position: relative;
            will-change: transform;
        }
        
        .matrix-table th.day-header {
            background: #2c3e50;
            font-size: 0.7rem;
        }
        
        .matrix-table th.day-header.weekend {
            background: #95a5a6;
        }
        
        .matrix-table th.day-header.today {
            background: #27ae60;
        }
        
        .matrix-table th.block-header {
            text-align: left;
            padding-left: 15px;
            font-size: 0.85rem;
            color: #fff;
        }
        
        .matrix-table td.cell {
            background: #fff;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 0.65rem;
            height: 38px;
            white-space: nowrap;
            overflow: hidden;
        }
        
        .matrix-table td.cell:hover {
            background: #e8f4f8;
        }
        
        .matrix-table td.cell.has-transaction {
            font-weight: 600;
            cursor: pointer;
        }
        
        .matrix-table td.cell.revenus { background: #d6eaf8; }
        .matrix-table td.cell.revenus:hover { background: #aed6f1; }
        .matrix-table td.cell.debits_directs { background: #fadbd8; }
        .matrix-table td.cell.debits_directs:hover { background: #f5b7b1; }
        .matrix-table td.cell.debits_differes { background: #fdebd0; }
        .matrix-table td.cell.debits_differes:hover { background: #f9e79f; }
        .matrix-table td.cell.weekend { background: #f8f9fa; }
        .matrix-table td.cell.today { border: 2px solid #27ae60; }
        
        .category-row { background: #fff; }
        
        .category-name {
            text-align: left;
            padding-left: 15px;
            font-weight: 600;
            color: #2c3e50;
            background: #fff;  /* fond opaque pour masquer le contenu derrière lors du scroll */
        }

        /* Fond opaque pour les en-têtes de blocs (ligne full-width) en sticky left */
        .block-revenus th.block-header { background: #3498db; }
        .block-debits-directs th.block-header { background: #e74c3c; }
        .block-debits-differes th.block-header { background: #f39c12; }

        /* Ces lignes de bloc s'étendent sur toute la largeur — pas besoin de sticky left séparé */
        .block-header-row th {
            position: static !important;
        }

        /* Fond opaque pour les lignes total en sticky left */
        .total-row td:first-child {
            background: #ecf0f1;
        }
        .total-row.solde-journalier td:first-child {
            background: #8e44ad;
        }
        
        .total-row {
            background: #ecf0f1;
            font-weight: bold;
        }
        
        .total-row td {
            border-top: 2px solid #34495e;
            font-size: 0.60rem;
        }
        
        .total-row.solde-journalier {
            background: #8e44ad;
            color: #fff;
        }
        
        .total-row.solde-journalier td {
            font-size: 0.60rem;
            font-weight: bold;
            color: #fff;
        }
        
        .cell-actions {
            display: none;
            position: absolute;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            padding: 10px;
            min-width: 220px;
        }
        
        .cell-actions.show { display: block; }
        .cell-actions .form-group { margin-bottom: 8px; }
        .cell-actions label {
            display: block;
            font-size: 0.65rem;
            margin-bottom: 3px;
            color: #7f8c8d;
        }
        .cell-actions input {
            width: 100%;
            padding: 5px;
            font-size: 0.65rem;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box;
        }
        .cell-actions button {
            display: block;
            width: 100%;
            margin: 3px 0;
            padding: 6px 10px;
            font-size: 0.65rem;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        
        .btn-save { background: #27ae60; color: #fff; }
        .btn-delete { background: #e74c3c; color: #fff; }
        .btn-cancel { background: #95a5a6; color: #fff; }
        
        .transaction-list-popup {
            max-height: 150px;
            overflow-y: auto;
            margin-bottom: 10px;
            border: 1px solid #eee;
            border-radius: 3px;
        }
        
        .transaction-item {
            padding: 6px 8px;
            border-bottom: 1px solid #eee;
            font-size: 0.65rem;
            cursor: pointer;
            text-align: left;
        }
        
        .transaction-item:hover,
        .transaction-item.selected {
            background: #3498db;
            color: #fff;
        }
        
        .transaction-item:last-child { border-bottom: none; }
        
        .popup-title {
            margin: 0 0 10px 0;
            font-size: 0.75rem;
            color: #2c3e50;
            font-weight: bold;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        /* ── IMPRESSION PDF : affiche la totalité de la matrice ── */
        @media print {
            /* Masquer tout sauf la table */
            nav,
            .budget-section,
            .balance-info,
            .nav-month,
            .actions,
            #transactionPopup {
                display: none !important;
            }

            /* Supprimer les contraintes de scroll */
            .matrix-calendar-wrapper,
            .matrix-calendar-container {
                overflow: visible !important;
                max-height: none !important;
                height: auto !important;
                padding: 0 !important;
                margin: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            /* La table prend toute la largeur et se déroule sur autant de pages que nécessaire */
            .matrix-table {
                width: 100% !important;
                min-width: unset !important;
                font-size: 0.55rem !important;
                border-collapse: collapse !important;
                page-break-inside: auto;
            }

            .matrix-table th,
            .matrix-table td {
                border: 1px solid #999 !important;
                padding: 3px 2px !important;
                min-width: unset !important;
            }

            /* Annuler le JS freeze (transform) pour l impression */
            .matrix-table thead th,
            .matrix-table td:first-child,
            .matrix-table th:first-child {
                transform: none !important;
                position: static !important;
                box-shadow: none !important;
                will-change: auto !important;
            }

            /* Répéter l en-tête sur chaque page imprimée */
            thead {
                display: table-header-group;
            }

            /* Éviter les coupures au milieu d une ligne */
            tr {
                page-break-inside: avoid;
            }

            body, html {
                margin: 0 !important;
                padding: 0 !important;
            }

            .container {
                overflow: visible !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
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
        <h2>Calendrier - Vue Matricielle</h2>
        
        <?php if (Session::has('error')): ?>
            <div class="alert error"><?= htmlspecialchars(Session::get('error')) ?></div>
            <?php Session::set('error', null); ?>
        <?php endif; ?>
        <?php if (Session::has('success')): ?>
            <div class="alert success"><?= htmlspecialchars(Session::get('success')) ?></div>
            <?php Session::set('success', null); ?>
        <?php endif; ?>

        <?php if ($balance['exceeds_target']): ?>
            <div class="alert alert-warning">
                ⚠️ Attention : Le solde prévisionnel (<?= formatNumber($balance['forecast']) ?>) est inférieur à l'objectif de fin de mois (<?= formatNumber($balance['target_end_balance']) ?>)
            </div>
        <?php endif; ?>

        <div class="budget-section">
            <h3>Budget du Mois</h3>
            <form action="<?= BASE_URL ?>/?page=save_budget" method="POST" class="form-inline">
                <input type="hidden" name="year" value="<?= htmlspecialchars($year) ?>">
                <input type="hidden" name="month" value="<?= htmlspecialchars($month) ?>">
                <div class="form-group">
                    <label>Solde Départ:</label>
                    <input type="number" step="0.001" name="starting_balance" value="<?= htmlspecialchars($balance['starting_balance']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Solde Final Cible:</label>
                    <input type="number" step="0.001" name="target_end_balance" value="<?= htmlspecialchars($balance['target_end_balance']) ?>" required>
                </div>
                <button type="submit">Enregistrer</button>
            </form>
        </div>

        <div class="balance-info">
            <div class="balance-card">
                <span class="label">Solde Départ:</span>
                <span class="value"><?= formatNumber($balance['starting_balance']) ?></span>
            </div>
            <div class="balance-card">
                <span class="label">Solde Courant:</span>
                <span class="value"><?= formatNumber($balance['current']) ?></span>
            </div>
            <div class="balance-card <?= $balance['exceeds_target'] ? 'warning' : '' ?>">
                <span class="label">Solde Prévisionnel:</span>
                <span class="value"><?= formatNumber($balance['forecast']) ?></span>
            </div>
            <div class="balance-card">
                <span class="label">Objectif Fin de Mois:</span>
                <span class="value"><?= formatNumber($balance['target_end_balance']) ?></span>
            </div>
        </div>

        <div class="nav-month">
            <a href="<?= BASE_URL ?>/?page=calendar&y=<?= date('Y', strtotime("$year-$month-01 -1 month")) ?>&m=<?= (int)date('m', strtotime("$year-$month-01 -1 month")) ?>">← Mois Précédent</a>
            <span><?= getMonthName($month) ?> <?= $year ?></span>
            <a href="<?= BASE_URL ?>/?page=calendar&y=<?= date('Y', strtotime("$year-$month-01 +1 month")) ?>&m=<?= (int)date('m', strtotime("$year-$month-01 +1 month")) ?>">Mois Suivant →</a>
        </div>

        <div class="matrix-calendar-wrapper">
        <div class="matrix-calendar-container">
            <table class="matrix-table">
                <thead>
                    <tr>
                        <th style="min-width: 180px;">Catégories</th>
                        <?php
                        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        $today = date('Y-m-d');
                        
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $d);
                            $dayOfWeek = date('N', strtotime($currentDate));
                            $isWeekend = ($dayOfWeek >= 6);
                            $isToday = ($currentDate === $today);
                            
                            $class = 'day-header';
                            if ($isWeekend) $class .= ' weekend';
                            if ($isToday) $class .= ' today';
                            
                            echo '<th class="' . $class . '">';
                            echo $d . '<br><span style="font-weight:normal;font-size:0.6rem;">' . getDayName(date('w', strtotime($currentDate))) . '</span>';
                            echo '</th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <!-- BLOC 1: REVENUS -->
                    <tr class="block-header-row block-revenus">
                        <th colspan="<?= $daysInMonth + 1 ?>" class="block-header">📈 REVENUS</th>
                    </tr>
                    <?php
                    $incomeCategories = array_filter($categories, function($c) {
                        return isset($c['block_type']) && $c['block_type'] === 'revenus';
                    });
                    
                    if (count($incomeCategories) > 0):
                        foreach ($incomeCategories as $cat):
                            echo '<tr class="category-row">';
                            echo '<td class="category-name">' . htmlspecialchars($cat['name']) . '</td>';
                            
                            for ($d = 1; $d <= $daysInMonth; $d++) {
                                $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $d);
                                
                                $dayCatTransactions = array_filter($transactions, function($t) use ($currentDate, $cat) {
                                    return isset($t['transaction_date']) && $t['transaction_date'] === $currentDate && isset($t['category_id']) && $t['category_id'] == $cat['id'];
                                });
                                
                                $totalAmount = 0;
                                foreach ($dayCatTransactions as $t) {
                                    $totalAmount += $t['amount'];
                                }
                                
                                $cellClass = 'cell revenus';
                                if ($totalAmount > 0) $cellClass .= ' has-transaction';
                                
                                $transactionData = json_encode(array_values($dayCatTransactions), JSON_HEX_QUOT | JSON_HEX_TAG);
                                
                                echo '<td class="' . $cellClass . '" data-category="' . $cat['id'] . '" data-date="' . $currentDate . '" data-type="revenus" data-transactions=\'' . $transactionData . '\' onclick="openTransactionPopup(this)">';
                                if ($totalAmount > 0) {
                                    echo formatNumber($totalAmount);
                                } else {
                                    echo '&nbsp;';
                                }
                                echo '</td>';
                            }
                            
                            echo '</tr>';
                        endforeach;
                    else:
                        echo '<tr><td colspan="' . ($daysInMonth + 1) . '" class="no-data" style="text-align:center;padding:20px;">Aucune catégorie de revenus. <a href="' . BASE_URL . '/?page=categories">Créer une catégorie</a></td></tr>';
                    endif;
                    ?>
                    
                    <tr class="total-row block-revenus">
                        <td class="category-name">Total Revenus</td>
                        <?php
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $d);
                            $dayIncome = 0;
                            foreach ($transactions as $t) {
                                if (isset($t['transaction_date']) && $t['transaction_date'] === $currentDate && isset($t['block_type']) && $t['block_type'] === 'revenus') {
                                    $dayIncome += $t['amount'];
                                }
                            }
                            echo '<td>' . ($dayIncome > 0 ? formatNumber($dayIncome) : '&nbsp;') . '</td>';
                        }
                        ?>
                    </tr>
                    
                    <!-- BLOC 2: DÉBITS DIRECTS -->
                    <tr class="block-header-row block-debits-directs">
                        <th colspan="<?= $daysInMonth + 1 ?>" class="block-header">📉 DÉBITS DIRECTS</th>
                    </tr>
                    <?php
                    $directCategories = array_filter($categories, function($c) {
                        return isset($c['block_type']) && $c['block_type'] === 'debits_directs';
                    });
                    
                    if (count($directCategories) > 0):
                        foreach ($directCategories as $cat):
                            echo '<tr class="category-row">';
                            echo '<td class="category-name">' . htmlspecialchars($cat['name']) . '</td>';
                            
                            for ($d = 1; $d <= $daysInMonth; $d++) {
                                $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $d);
                                
                                $dayCatTransactions = array_filter($transactions, function($t) use ($currentDate, $cat) {
                                    return isset($t['transaction_date']) && $t['transaction_date'] === $currentDate 
                                        && isset($t['category_id']) && $t['category_id'] == $cat['id'] 
                                        && isset($t['payment_method']) && $t['payment_method'] === 'cash';
                                });
                                
                                $totalAmount = 0;
                                foreach ($dayCatTransactions as $t) {
                                    $totalAmount += $t['amount'];
                                }
                                
                                $cellClass = 'cell debits_directs';
                                if ($totalAmount > 0) $cellClass .= ' has-transaction';
                                
                                $transactionData = json_encode(array_values($dayCatTransactions), JSON_HEX_QUOT | JSON_HEX_TAG);
                                
                                echo '<td class="' . $cellClass . '" data-category="' . $cat['id'] . '" data-date="' . $currentDate . '" data-type="debits_directs" data-transactions=\'' . $transactionData . '\' onclick="openTransactionPopup(this)">';
                                if ($totalAmount > 0) {
                                    echo formatNumber($totalAmount);
                                } else {
                                    echo '&nbsp;';
                                }
                                echo '</td>';
                            }
                            
                            echo '</tr>';
                        endforeach;
                    else:
                        echo '<tr><td colspan="' . ($daysInMonth + 1) . '" class="no-data" style="text-align:center;padding:20px;">Aucune catégorie de débits directs. <a href="' . BASE_URL . '/?page=categories">Créer une catégorie</a></td></tr>';
                    endif;
                    ?>
                    
                    <tr class="total-row block-debits-directs">
                        <td class="category-name">Total Débits Directs</td>
                        <?php
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $d);
                            $dayExpense = 0;
                            foreach ($transactions as $t) {
                                if (isset($t['transaction_date']) && $t['transaction_date'] === $currentDate && isset($t['block_type']) && $t['block_type'] === 'debits_directs' && isset($t['payment_method']) && $t['payment_method'] === 'cash') {
                                    $dayExpense += $t['amount'];
                                }
                            }
                            echo '<td>' . ($dayExpense > 0 ? formatNumber($dayExpense) : '&nbsp;') . '</td>';
                        }
                        ?>
                    </tr>
                    
                    <!-- BLOC 3: DÉBITS DIFFÉRÉS -->
                    <tr class="block-header-row block-debits-differes">
                        <th colspan="<?= $daysInMonth + 1 ?>" class="block-header">💳 DÉBITS DIFFÉRÉS</th>
                    </tr>
                    <?php
                    $deferredCategories = array_filter($categories, function($c) {
                        return isset($c['block_type']) && $c['block_type'] === 'debits_differes';
                    });
                    
                    if (count($deferredCategories) > 0):
                        foreach ($deferredCategories as $cat):
                            echo '<tr class="category-row">';
                            echo '<td class="category-name">' . htmlspecialchars($cat['name']) . '</td>';
                            
                            for ($d = 1; $d <= $daysInMonth; $d++) {
                                $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $d);
                                
                                $dayCatTransactions = array_filter($transactions, function($t) use ($currentDate, $cat) {
                                    return isset($t['transaction_date']) && $t['transaction_date'] === $currentDate 
                                        && isset($t['category_id']) && $t['category_id'] == $cat['id'] 
                                        && isset($t['payment_method']) && $t['payment_method'] === 'card';
                                });
                                
                                $totalAmount = 0;
                                foreach ($dayCatTransactions as $t) {
                                    $totalAmount += $t['amount'];
                                }
                                
                                $cellClass = 'cell debits_differes';
                                if ($totalAmount > 0) $cellClass .= ' has-transaction';
                                
                                $transactionData = json_encode(array_values($dayCatTransactions), JSON_HEX_QUOT | JSON_HEX_TAG);
                                
                                echo '<td class="' . $cellClass . '" data-category="' . $cat['id'] . '" data-date="' . $currentDate . '" data-type="debits_differes" data-transactions=\'' . $transactionData . '\' onclick="openTransactionPopup(this)">';
                                if ($totalAmount > 0) {
                                    echo formatNumber($totalAmount);
                                } else {
                                    echo '&nbsp;';
                                }
                                echo '</td>';
                            }
                            
                            echo '</tr>';
                        endforeach;
                    else:
                        echo '<tr><td colspan="' . ($daysInMonth + 1) . '" class="no-data" style="text-align:center;padding:20px;">Aucune catégorie de débits différés. <a href="' . BASE_URL . '/?page=categories">Créer une catégorie</a></td></tr>';
                    endif;
                    ?>
                    
                    <tr class="total-row block-debits-differes">
                        <td class="category-name">Total Débits Différés</td>
                        <?php
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $d);
                            $dayExpense = 0;
                            foreach ($transactions as $t) {
                                if (isset($t['transaction_date']) && $t['transaction_date'] === $currentDate && isset($t['block_type']) && $t['block_type'] === 'debits_differes' && isset($t['payment_method']) && $t['payment_method'] === 'card') {
                                    $dayExpense += $t['amount'];
                                }
                            }
                            echo '<td>' . ($dayExpense > 0 ? formatNumber($dayExpense) : '&nbsp;') . '</td>';
                        }
                        ?>
                    </tr>
                    
                    <!-- SOLDE PAR JOUR -->
                    <tr class="total-row solde-journalier">
                        <td class="category-name">📊 SOLDE JOURNALIER</td>
                        <?php
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $d);
                            $dayIncome = 0;
                            $dayExpenseDirect = 0;
                            $dayExpenseDeferred = 0;
                            
                            foreach ($transactions as $t) {
                                if (isset($t['transaction_date']) && $t['transaction_date'] === $currentDate) {
                                    if (isset($t['block_type']) && $t['block_type'] === 'revenus') {
                                        $dayIncome += $t['amount'];
                                    } elseif (isset($t['block_type']) && $t['block_type'] === 'debits_directs') {
                                        $dayExpenseDirect += $t['amount'];
                                    } elseif (isset($t['block_type']) && $t['block_type'] === 'debits_differes') {
                                        $dayExpenseDeferred += $t['amount'];
                                    }
                                }
                            }
                            
                            $dayBalance = $dayIncome - $dayExpenseDirect - $dayExpenseDeferred;
                            $color = $dayBalance >= 0 ? '#2ecc71' : '#e74c3c';
                            
                            echo '<td style="color: ' . $color . '; font-weight: bold;">';
                            echo ($dayBalance >= 0 ? '+' : '') . formatNumber($dayBalance);
                            echo '</td>';
                        }
                        ?>
                    </tr>
                </tbody>
            </table>
        </div>
        </div>

        <div class="actions">
            <a href="<?= BASE_URL ?>/?page=transactions" class="btn">Voir Toutes les Transactions</a>
            <button onclick="window.print()" class="btn btn-print">🖨️ Imprimer</button>
        </div>
    </div>

    <!-- Popup -->
    <div id="transactionPopup" class="cell-actions">
        <h4 class="popup-title">Gérer la Transaction</h4>
        <div id="existingTransactions" class="transaction-list-popup" style="display: none;">
            <div style="padding: 5px; font-size: 0.62rem; color: #7f8c8d; font-weight: 600;">▼ Transactions (cliquer pour modifier):</div>
            <div id="transactionList"></div>
        </div>
        <form id="transactionForm">
            <input type="hidden" id="popupCategory" value="">
            <input type="hidden" id="popupDate" value="">
            <input type="hidden" id="popupType" value="">
            <input type="hidden" id="popupTransactionId" value="">
            <div class="form-group">
                <label>Montant:</label>
                <input type="number" id="popupAmount" step="0.001" placeholder="Montant" required>
            </div>
            <div class="form-group">
                <label>Description:</label>
                <input type="text" id="popupDescription" placeholder="Description">
            </div>
            <button type="button" class="btn-save" onclick="saveTransaction()">💾 Enregistrer</button>
            <button type="button" class="btn-delete" onclick="deleteTransaction()" id="btnDelete" style="display: none;">🗑️ Supprimer</button>
            <button type="button" class="btn-cancel" onclick="closePopup()">❌ Annuler</button>
        </form>
    </div>

    <script>
        function openTransactionPopup(cell) {
            var category = cell.getAttribute('data-category');
            var date = cell.getAttribute('data-date');
            var type = cell.getAttribute('data-type');
            var transactionsJson = cell.getAttribute('data-transactions');
            
            console.log('openTransactionPopup:', { category, date, type, transactionsJson });
            
            document.getElementById('popupCategory').value = category;
            document.getElementById('popupDate').value = date;
            document.getElementById('popupType').value = type;
            document.getElementById('popupTransactionId').value = '';
            document.getElementById('popupAmount').value = '';
            document.getElementById('popupDescription').value = '';
            document.getElementById('btnDelete').style.display = 'none';
            
            var existingTransDiv = document.getElementById('existingTransactions');
            var transactionList = document.getElementById('transactionList');
            
            if (transactionsJson && transactionsJson !== '[]' && transactionsJson !== '') {
                try {
                    var transactions = JSON.parse(transactionsJson);
                    
                    if (transactions && transactions.length > 0) {
                        transactionList.innerHTML = '';
                        transactions.forEach(function(trans) {
                            var item = document.createElement('div');
                            item.className = 'transaction-item';
                            item.innerHTML = '<strong>' + (trans.category_name || 'Catégorie') + '</strong>: ' + formatNumber(trans.amount) + (trans.description ? '<br><em style="color:#7f8c8d;">' + trans.description + '</em>' : '');
                            item.onclick = function() {
                                document.querySelectorAll('.transaction-item').forEach(function(i) { i.classList.remove('selected'); });
                                item.classList.add('selected');
                                editTransaction(trans);
                            };
                            transactionList.appendChild(item);
                        });
                        existingTransDiv.style.display = 'block';
                    } else {
                        existingTransDiv.style.display = 'none';
                    }
                } catch(e) {
                    console.error('Error parsing transactions:', e);
                    existingTransDiv.style.display = 'none';
                }
            } else {
                existingTransDiv.style.display = 'none';
            }
            
            var rect = cell.getBoundingClientRect();
            var popup = document.getElementById('transactionPopup');
            popup.style.left = rect.left + 'px';
            popup.style.top = (rect.bottom + 5) + 'px';
            popup.classList.add('show');
            
            document.getElementById('popupAmount').focus();
        }
        
        function editTransaction(trans) {
            console.log('editTransaction:', trans);
            document.getElementById('popupTransactionId').value = trans.id;
            document.getElementById('popupAmount').value = trans.amount;
            document.getElementById('popupDescription').value = trans.description || '';
            document.getElementById('btnDelete').style.display = 'block';
        }
        
        function closePopup() {
            document.getElementById('transactionPopup').classList.remove('show');
            document.getElementById('popupAmount').value = '';
            document.getElementById('popupDescription').value = '';
            document.getElementById('popupTransactionId').value = '';
            document.getElementById('btnDelete').style.display = 'none';
            document.getElementById('existingTransactions').style.display = 'none';
        }
        
        function saveTransaction() {
            var transactionId = document.getElementById('popupTransactionId').value;
            var category = document.getElementById('popupCategory').value;
            var date = document.getElementById('popupDate').value;
            var type = document.getElementById('popupType').value;
            var amount = document.getElementById('popupAmount').value;
            var description = document.getElementById('popupDescription').value;
            
            if (!amount || amount <= 0) { alert('Veuillez saisir un montant valide'); return; }
            
            var paymentMethod = type === 'debits_differes' ? 'card' : 'cash';
            var transactionType = type === 'revenus' ? 'income' : 'expense';
            
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '?page=' + (transactionId ? 'update_transaction' : 'store_from_calendar');
            
            var fields = {};
            if (transactionId && transactionId !== '') { fields['id'] = transactionId; }
            fields['category_id'] = category;
            fields['amount'] = amount;
            fields['description'] = description;
            fields['date'] = date;
            fields['type'] = transactionType;
            fields['payment_method'] = paymentMethod;
            
            for (var key in fields) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
        
        function deleteTransaction() {
            var transactionId = document.getElementById('popupTransactionId').value;
            var date = document.getElementById('popupDate').value;
            
            if (!transactionId || transactionId === '') { alert('Aucune transaction à supprimer'); return; }
            if (!confirm('Voulez-vous vraiment supprimer cette transaction ?')) { return; }
            
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '?page=delete_from_calendar';
            
            var inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'id';
            inputId.value = transactionId;
            form.appendChild(inputId);
            
            var inputDate = document.createElement('input');
            inputDate.type = 'hidden';
            inputDate.name = 'date';
            inputDate.value = date;
            form.appendChild(inputDate);
            
            document.body.appendChild(form);
            form.submit();
        }
        
        function formatNumber(num) {
            return parseFloat(num).toLocaleString('fr-FR', {minimumFractionDigits: 3, maximumFractionDigits: 3});
        }
        
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.cell') && !e.target.closest('.cell-actions')) {
                closePopup();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') { closePopup(); }
        });
        
        document.getElementById('popupAmount').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { saveTransaction(); }
        });

        // ── Freeze JS de secours : fige en-têtes et colonne même si un ancêtre a overflow ──
        (function() {
            var container = document.querySelector('.matrix-calendar-container');
            if (!container) return;

            var table = container.querySelector('.matrix-table');
            if (!table) return;

            // Vérifie si le sticky CSS est actif (test rapide)
            // On force le JS dans tous les cas pour garantir le résultat

            function applyFreeze() {
                var scrollTop  = container.scrollTop;
                var scrollLeft = container.scrollLeft;

                // Figer la ligne en-tête (tous les th du thead)
                var headerCells = table.querySelectorAll('thead th');
                headerCells.forEach(function(th) {
                    th.style.transform = 'translateY(' + scrollTop + 'px)';
                });

                // Figer la première colonne (td:first-child et th:first-child dans tbody)
                var firstColCells = table.querySelectorAll('tbody tr > *:first-child');
                firstColCells.forEach(function(cell) {
                    cell.style.transform = 'translateX(' + scrollLeft + 'px)';
                });

                // Coin : cumule les deux translations
                var cornerCell = table.querySelector('thead th:first-child');
                if (cornerCell) {
                    cornerCell.style.transform = 'translateY(' + scrollTop + 'px) translateX(' + scrollLeft + 'px)';
                }
            }

            container.addEventListener('scroll', applyFreeze, { passive: true });
            applyFreeze();
        })();
    </script>
</body>
</html>