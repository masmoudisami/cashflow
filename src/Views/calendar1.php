<?php
if (!isset($year) || !isset($month)) {
    $year = date('Y');
    $month = date('m');
}

$yearInt = (int)$year;
$monthInt = (int)$month;

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
    <title>Calendrier - CashFlow</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        .matrix-calendar-container {
            overflow-x: auto;
            margin-top: 1.5rem;
        }
        
        .matrix-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            min-width: 1200px;
        }
        
        .matrix-table th,
        .matrix-table td {
            border: 1px solid #ddd;
            padding: 6px 4px;
            text-align: center;
            min-width: 45px;
        }
        
        .matrix-table th {
            background: #34495e;
            color: #fff;
            font-weight: 600;
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
        
        .matrix-table th.category-header {
            background: #3498db;
            text-align: left;
            padding-left: 10px;
            min-width: 150px;
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
            font-size: 0.7rem;
            height: 35px;
        }
        
        .matrix-table td.cell:hover {
            background: #e8f4f8;
        }
        
        .matrix-table td.cell.has-transaction {
            font-weight: 600;
        }
        
        /* COULEURS MODIFIÉES */
        .matrix-table td.cell.revenus {
            background: #d6eaf8;
        }
        .matrix-table td.cell.revenus:hover {
            background: #aed6f1;
        }
        
        .matrix-table td.cell.debits_directs {
            background: #fadbd8;
        }
        .matrix-table td.cell.debits_directs:hover {
            background: #f5b7b1;
        }
        
        .matrix-table td.cell.debits_differes {
            background: #fdebd0;
        }
        .matrix-table td.cell.debits_differes:hover {
            background: #f9e79f;
        }
        
        .matrix-table td.cell.weekend {
            background: #f8f9fa;
        }
        
        .matrix-table td.cell.today {
            border: 2px solid #27ae60;
        }
        
        .matrix-table td.cell input {
            width: 100%;
            border: none;
            text-align: right;
            font-size: 0.68rem;
            padding: 2px;
            background: transparent;
        }
        
        .matrix-table td.cell input:focus {
            outline: 2px solid #3498db;
            background: #fff;
        }
        
        /* BLOCS COULEURS */
        .block-revenus th.block-header {
            background: #3498db;
        }
        
        .block-debits-directs th.block-header {
            background: #e74c3c;
        }
        
        .block-debits-differes th.block-header {
            background: #f39c12;
        }
        
        .category-row {
            background: #fff;
        }
        
        .category-name {
            text-align: left;
            padding-left: 15px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .total-row {
            background: #ecf0f1;
            font-weight: bold;
        }
        
        .total-row td {
            border-top: 2px solid #34495e;
        }
        
        .cell-actions {
            display: none;
            position: absolute;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            padding: 5px;
        }
        
        .cell-actions.show {
            display: block;
        }
        
        .cell-actions button {
            display: block;
            width: 100%;
            margin: 2px 0;
            padding: 4px 8px;
            font-size: 0.65rem;
        }
        
        .btn-edit {
            background: #3498db;
        }
        
        .btn-delete {
            background: #e74c3c;
        }
        
        .legend {
            display: flex;
            gap: 20px;
            margin: 1rem 0;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.75rem;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 3px;
            border: 1px solid #ddd;
        }
        
        @media print {
            .matrix-calendar-container {
                overflow: visible;
            }
            .matrix-table {
                font-size: 6pt;
                min-width: auto;
            }
            .matrix-table th,
            .matrix-table td {
                padding: 3px 2px;
                min-width: 30px;
            }
            .cell-actions {
                display: none !important;
            }
        }
    </style>
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
            <form action="?page=save_budget" method="POST" class="form-inline">
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
            <a href="?page=calendar&y=<?= date('Y', strtotime("$year-$month-01 -1 month")) ?>&m=<?= (int)date('m', strtotime("$year-$month-01 -1 month")) ?>">← Mois Précédent</a>
            <span><?= getMonthName($month) ?> <?= $year ?></span>
            <a href="?page=calendar&y=<?= date('Y', strtotime("$year-$month-01 +1 month")) ?>&m=<?= (int)date('m', strtotime("$year-$month-01 +1 month")) ?>">Mois Suivant →</a>
        </div>

        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background: #3498db;"></div>
                <span>Revenus (Bleu)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #e74c3c;"></div>
                <span>Débits Directs (Rouge)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #f39c12;"></div>
                <span>Débits Différés (Orange)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #27ae60; border: 2px solid #27ae60;"></div>
                <span>Aujourd'hui</span>
            </div>
        </div>

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
                            echo $d . '<br><span style="font-weight:normal;font-size:0.65rem;">' . getDayName(date('w', strtotime($currentDate))) . '</span>';
                            echo '</th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <!-- BLOC 1: REVENUS (BLEU) -->
                    <tr class="block-header-row block-revenus">
                        <th colspan="<?= $daysInMonth + 1 ?>" class="block-header">📈 REVENUS</th>
                    </tr>
                    <?php
                    $incomeCategories = array_filter($categories, function($c) {
                        return $c['block_type'] === 'revenus';
                    });
                    
                    foreach ($incomeCategories as $cat):
                        echo '<tr class="category-row">';
                        echo '<td class="category-name">' . htmlspecialchars($cat['name']) . '</td>';
                        
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $d);
                            $dayOfWeek = date('N', strtotime($currentDate));
                            $isWeekend = ($dayOfWeek >= 6);
                            $isToday = ($currentDate === $today);
                            
                            $dayCatTransactions = array_filter($transactions, function($t) use ($currentDate, $cat) {
                                return $t['transaction_date'] === $currentDate && $t['category_id'] == $cat['id'];
                            });
                            
                            $totalAmount = 0;
                            foreach ($dayCatTransactions as $t) {
                                $totalAmount += $t['amount'];
                            }
                            
                            $cellClass = 'cell revenus';
                            if ($isWeekend) $cellClass .= ' weekend';
                            if ($isToday) $cellClass .= ' today';
                            if ($totalAmount > 0) $cellClass .= ' has-transaction';
                            
                            echo '<td class="' . $cellClass . '" data-category="' . $cat['id'] . '" data-date="' . $currentDate . '" data-type="revenus">';
                            if ($totalAmount > 0) {
                                echo formatNumber($totalAmount);
                            } else {
                                echo '&nbsp;';
                            }
                            echo '</td>';
                        }
                        
                        echo '</tr>';
                    endforeach;
                    ?>
                    
                    <tr class="total-row block-revenus">
                        <td class="category-name">Total Revenus</td>
                        <?php
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $d);
                            $dayIncome = 0;
                            foreach ($transactions as $t) {
                                if ($t['transaction_date'] === $currentDate && $t['block_type'] === 'revenus') {
                                    $dayIncome += $t['amount'];
                                }
                            }
                            echo '<td>' . ($dayIncome > 0 ? formatNumber($dayIncome) : '&nbsp;') . '</td>';
                        }
                        ?>
                    </tr>
                    
                    <!-- BLOC 2: DÉBITS DIRECTS (ROUGE) -->
                    <tr class="block-header-row block-debits-directs">
                        <th colspan="<?= $daysInMonth + 1 ?>" class="block-header">📉 DÉBITS DIRECTS</th>
                    </tr>
                    <?php
                    $directCategories = array_filter($categories, function($c) {
                        return $c['block_type'] === 'debits_directs';
                    });
                    
                    foreach ($directCategories as $cat):
                        echo '<tr class="category-row">';
                        echo '<td class="category-name">' . htmlspecialchars($cat['name']) . '</td>';
                        
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $d);
                            $dayOfWeek = date('N', strtotime($currentDate));
                            $isWeekend = ($dayOfWeek >= 6);
                            $isToday = ($currentDate === $today);
                            
                            $dayCatTransactions = array_filter($transactions, function($t) use ($currentDate, $cat) {
                                return $t['transaction_date'] === $currentDate 
                                    && $t['category_id'] == $cat['id'] 
                                    && $t['payment_method'] === 'cash';
                            });
                            
                            $totalAmount = 0;
                            foreach ($dayCatTransactions as $t) {
                                $totalAmount += $t['amount'];
                            }
                            
                            $cellClass = 'cell debits_directs';
                            if ($isWeekend) $cellClass .= ' weekend';
                            if ($isToday) $cellClass .= ' today';
                            if ($totalAmount > 0) $cellClass .= ' has-transaction';
                            
                            echo '<td class="' . $cellClass . '" data-category="' . $cat['id'] . '" data-date="' . $currentDate . '" data-type="debits_directs">';
                            if ($totalAmount > 0) {
                                echo formatNumber($totalAmount);
                            } else {
                                echo '&nbsp;';
                            }
                            echo '</td>';
                        }
                        
                        echo '</tr>';
                    endforeach;
                    ?>
                    
                    <tr class="total-row block-debits-directs">
                        <td class="category-name">Total Débits Directs</td>
                        <?php
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $d);
                            $dayExpense = 0;
                            foreach ($transactions as $t) {
                                if ($t['transaction_date'] === $currentDate && $t['block_type'] === 'debits_directs' && $t['payment_method'] === 'cash') {
                                    $dayExpense += $t['amount'];
                                }
                            }
                            echo '<td>' . ($dayExpense > 0 ? formatNumber($dayExpense) : '&nbsp;') . '</td>';
                        }
                        ?>
                    </tr>
                    
                    <!-- BLOC 3: DÉBITS DIFFÉRÉS (ORANGE) -->
                    <tr class="block-header-row block-debits-differes">
                        <th colspan="<?= $daysInMonth + 1 ?>" class="block-header">💳 DÉBITS DIFFÉRÉS</th>
                    </tr>
                    <?php
                    $deferredCategories = array_filter($categories, function($c) {
                        return $c['block_type'] === 'debits_differes';
                    });
                    
                    foreach ($deferredCategories as $cat):
                        echo '<tr class="category-row">';
                        echo '<td class="category-name">' . htmlspecialchars($cat['name']) . '</td>';
                        
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $d);
                            $dayOfWeek = date('N', strtotime($currentDate));
                            $isWeekend = ($dayOfWeek >= 6);
                            $isToday = ($currentDate === $today);
                            
                            $dayCatTransactions = array_filter($transactions, function($t) use ($currentDate, $cat) {
                                return $t['transaction_date'] === $currentDate 
                                    && $t['category_id'] == $cat['id'] 
                                    && $t['payment_method'] === 'card';
                            });
                            
                            $totalAmount = 0;
                            foreach ($dayCatTransactions as $t) {
                                $totalAmount += $t['amount'];
                            }
                            
                            $cellClass = 'cell debits_differes';
                            if ($isWeekend) $cellClass .= ' weekend';
                            if ($isToday) $cellClass .= ' today';
                            if ($totalAmount > 0) $cellClass .= ' has-transaction';
                            
                            echo '<td class="' . $cellClass . '" data-category="' . $cat['id'] . '" data-date="' . $currentDate . '" data-type="debits_differes">';
                            if ($totalAmount > 0) {
                                echo formatNumber($totalAmount);
                            } else {
                                echo '&nbsp;';
                            }
                            echo '</td>';
                        }
                        
                        echo '</tr>';
                    endforeach;
                    ?>
                    
                    <tr class="total-row block-debits-differes">
                        <td class="category-name">Total Débits Différés</td>
                        <?php
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $d);
                            $dayExpense = 0;
                            foreach ($transactions as $t) {
                                if ($t['transaction_date'] === $currentDate && $t['block_type'] === 'debits_differes' && $t['payment_method'] === 'card') {
                                    $dayExpense += $t['amount'];
                                }
                            }
                            echo '<td>' . ($dayExpense > 0 ? formatNumber($dayExpense) : '&nbsp;') . '</td>';
                        }
                        ?>
                    </tr>
                    
                    <!-- SOLDE PAR JOUR -->
                    <tr class="total-row" style="background: #2c3e50; color: #fff;">
                        <td class="category-name">📊 SOLDE JOURNALIER</td>
                        <?php
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $currentDate = sprintf("%04d-%02d-%02d", $year, $month, $d);
                            $dayIncome = 0;
                            $dayExpenseDirect = 0;
                            $dayExpenseDeferred = 0;
                            
                            foreach ($transactions as $t) {
                                if ($t['transaction_date'] === $currentDate) {
                                    if ($t['block_type'] === 'revenus') {
                                        $dayIncome += $t['amount'];
                                    } elseif ($t['block_type'] === 'debits_directs') {
                                        $dayExpenseDirect += $t['amount'];
                                    } elseif ($t['block_type'] === 'debits_differes') {
                                        $dayExpenseDeferred += $t['amount'];
                                    }
                                }
                            }
                            
                            $dayBalance = $dayIncome - $dayExpenseDirect - $dayExpenseDeferred;
                            $color = $dayBalance >= 0 ? '#27ae60' : '#e74c3c';
                            
                            echo '<td style="color: ' . $color . '; font-weight: bold;">';
                            echo ($dayBalance >= 0 ? '+' : '') . formatNumber($dayBalance);
                            echo '</td>';
                        }
                        ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="actions">
            <a href="?page=transactions" class="btn">Voir Toutes les Transactions</a>
            <button onclick="window.print()" class="btn btn-print">🖨️ Imprimer</button>
        </div>
    </div>

    <div id="transactionPopup" class="cell-actions">
        <input type="hidden" id="popupCategory" value="">
        <input type="hidden" id="popupDate" value="">
        <input type="hidden" id="popupType" value="">
        <input type="number" id="popupAmount" step="0.001" placeholder="Montant" style="width: 100%; margin-bottom: 5px;">
        <input type="text" id="popupDescription" placeholder="Description" style="width: 100%; margin-bottom: 5px;">
        <button class="btn-edit" onclick="saveTransaction()">💾 Enregistrer</button>
        <button class="btn-delete" onclick="closePopup()">❌ Annuler</button>
    </div>

    <script>
        document.querySelectorAll('.matrix-table td.cell').forEach(function(cell) {
            cell.addEventListener('click', function() {
                var category = this.getAttribute('data-category');
                var date = this.getAttribute('data-date');
                var type = this.getAttribute('data-type');
                
                document.getElementById('popupCategory').value = category;
                document.getElementById('popupDate').value = date;
                document.getElementById('popupType').value = type;
                
                var rect = this.getBoundingClientRect();
                var popup = document.getElementById('transactionPopup');
                popup.style.left = rect.left + 'px';
                popup.style.top = (rect.bottom + 5) + 'px';
                popup.classList.add('show');
                
                document.getElementById('popupAmount').focus();
            });
        });
        
        function closePopup() {
            document.getElementById('transactionPopup').classList.remove('show');
            document.getElementById('popupAmount').value = '';
            document.getElementById('popupDescription').value = '';
        }
        
        function saveTransaction() {
            var category = document.getElementById('popupCategory').value;
            var date = document.getElementById('popupDate').value;
            var type = document.getElementById('popupType').value;
            var amount = document.getElementById('popupAmount').value;
            var description = document.getElementById('popupDescription').value;
            
            if (!amount || amount <= 0) {
                alert('Veuillez saisir un montant valide');
                return;
            }
            
            var paymentMethod = type === 'debits_differes' ? 'card' : 'cash';
            var transactionType = type === 'revenus' ? 'income' : 'expense';
            
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '?page=store_from_calendar';
            
            var fields = {
                'category_id': category,
                'amount': amount,
                'description': description,
                'date': date,
                'type': transactionType,
                'payment_method': paymentMethod
            };
            
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
        
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.cell') && !e.target.closest('.cell-actions')) {
                closePopup();
            }
        });
        
        document.getElementById('popupAmount').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                saveTransaction();
            }
        });
    </script>
</body>
</html>