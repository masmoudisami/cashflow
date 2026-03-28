<?php
class ExportController {
    public function csv() {
        Session::init();
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }

        $user_id = Session::get('user_id');
        $model = new Transaction();
        $transactions = $model->getAll($user_id);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=transactions.csv');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['ID', 'Date', 'Description', 'Category', 'Type', 'Amount', 'Method']);

        foreach ($transactions as $t) {
            fputcsv($output, [$t['id'], $t['transaction_date'], $t['description'], $t['category_name'], $t['type'], $t['amount'], $t['payment_method']]);
        }
        fclose($output);
        exit;
    }

    public function pdf() {
        Session::init();
        
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }

        $user_id = Session::get('user_id');
        $username = Session::get('username');
        $projectRoot = dirname(__DIR__, 2);
        $scriptPath = $projectRoot . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'export_pdf.py';
        $outputFile = $projectRoot . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR . 'report_' . $user_id . '_' . time() . '.pdf';
        
        // Créer le dossier exports s'il n'existe pas
        $exportsDir = $projectRoot . DIRECTORY_SEPARATOR . 'exports';
        if (!is_dir($exportsDir)) {
            mkdir($exportsDir, 0777, true);
        }

        // Vérifier les permissions
        if (!is_writable($exportsDir)) {
            Session::set('error', 'Dossier exports non accessible en écriture');
            Session::redirect(BASE_URL . '/?page=dashboard');
        }

        // Vérifier que le script existe
        if (!file_exists($scriptPath)) {
            Session::set('error', 'Script PDF non disponible');
            Session::redirect(BASE_URL . '/?page=dashboard');
        }

        // Chemin Python
        $pythonCmd = '/usr/bin/python3';
        
        if (!file_exists($pythonCmd)) {
            $pythonCmd = trim(shell_exec('which python3'));
        }
        
        if (empty($pythonCmd) || !file_exists($pythonCmd)) {
            Session::set('error', 'Export PDF temporairement indisponible');
            Session::redirect(BASE_URL . '/?page=dashboard');
        }

        // Construire la commande
        $command = escapeshellarg($pythonCmd) . " " . 
                   escapeshellarg($scriptPath) . " " . 
                   escapeshellarg($user_id) . " " . 
                   escapeshellarg($username) . " " . 
                   escapeshellarg($outputFile) . " 2>&1";
        
        // Exécuter la commande
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        // Vérifier le résultat
        if ($return_var === 0 && file_exists($outputFile)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="report_cashflow.pdf"');
            header('Content-Length: ' . filesize($outputFile));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            readfile($outputFile);
            unlink($outputFile);
            exit;
        } else {
            Session::set('error', 'Erreur lors de la génération du PDF');
            Session::redirect(BASE_URL . '/?page=dashboard');
        }
    }
}