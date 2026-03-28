<?php
class DashboardController {
    public function index() {
        Session::init();
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }

        $user_id = Session::get('user_id');
        $transactionModel = new Transaction();
        
        $current_month = date('Y-m');
        $balance = $transactionModel->getBalance($user_id, date('Y'), date('m'));
        $transactions = $transactionModel->getAll($user_id, $current_month);
        $categories = $transactionModel->getCategories($user_id);

        require 'src/Views/dashboard.php';
    }
}