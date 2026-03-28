<?php
class TransactionController {
    public function store() {
        Session::init();
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }

        $model = new Transaction();
        $user_id = Session::get('user_id');
        
        $category_id = $_POST['category_id'] ?? null;
        $amount = $_POST['amount'] ?? null;
        $description = $_POST['description'] ?? '';
        $date = $_POST['date'] ?? date('Y-m-d');
        $type = $_POST['type'] ?? 'expense';
        $method = $_POST['payment_method'] ?? 'cash';
        $deferred = ($method === 'card') ? 1 : 0;

        if (!$category_id || !$amount) {
            Session::set('error', 'Champs requis manquants');
            Session::redirect(BASE_URL . '/?page=transactions');
        }

        $model->create($user_id, $category_id, $amount, $description, $date, $type, $method, $deferred);
        Session::redirect(BASE_URL . '/?page=transactions');
    }

    public function storeFromCalendar() {
        Session::init();
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }

        $model = new Transaction();
        $user_id = Session::get('user_id');
        
        $category_id = $_POST['category_id'] ?? null;
        $amount = $_POST['amount'] ?? null;
        $description = $_POST['description'] ?? '';
        $date = $_POST['date'] ?? date('Y-m-d');
        $type = $_POST['type'] ?? 'expense';
        $method = $_POST['payment_method'] ?? 'cash';
        $deferred = ($method === 'card') ? 1 : 0;

        if (!$category_id || !$amount || !$date) {
            Session::set('error', 'Champs requis manquants');
            $redirect = BASE_URL . '/?page=calendar&y=' . date('Y', strtotime($date)) . '&m=' . date('m', strtotime($date));
            Session::redirect($redirect);
        }

        $model->create($user_id, $category_id, $amount, $description, $date, $type, $method, $deferred);
        Session::set('success', 'Transaction ajoutée');
        
        $redirect = BASE_URL . '/?page=calendar&y=' . date('Y', strtotime($date)) . '&m=' . date('m', strtotime($date));
        Session::redirect($redirect);
    }

    public function update() {
        Session::init();
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }

        $model = new Transaction();
        $user_id = Session::get('user_id');
        
        $id = $_POST['id'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        $amount = $_POST['amount'] ?? null;
        $description = $_POST['description'] ?? '';
        $date = $_POST['date'] ?? date('Y-m-d');
        $type = $_POST['type'] ?? 'expense';
        $method = $_POST['payment_method'] ?? 'cash';
        $deferred = ($method === 'card') ? 1 : 0;

        if (!$id || !$category_id || !$amount) {
            Session::set('error', 'Champs requis manquants');
            Session::redirect(BASE_URL . '/?page=calendar');
        }

        if ($model->update($id, $user_id, $category_id, $amount, $description, $date, $type, $method, $deferred)) {
            Session::set('success', 'Transaction modifiée');
        } else {
            Session::set('error', 'Erreur lors de la modification');
        }
        
        $redirect = BASE_URL . '/?page=calendar&y=' . date('Y', strtotime($date)) . '&m=' . date('m', strtotime($date));
        Session::redirect($redirect);
    }

    public function edit() {
        Session::init();
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }

        $user_id = Session::get('user_id');
        $id = $_GET['id'] ?? null;
        $date = $_GET['date'] ?? date('Y-m-d');
        
        if (!$id) {
            Session::redirect(BASE_URL . '/?page=calendar');
        }

        $model = new Transaction();
        $transaction = $model->getById($id, $user_id);
        
        if (!$transaction) {
            Session::set('error', 'Transaction non trouvée');
            Session::redirect(BASE_URL . '/?page=calendar');
        }

        $categories = $model->getCategories($user_id);
        
        require 'src/Views/edit_transaction.php';
    }

    public function delete() {
        Session::init();
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }

        $model = new Transaction();
        $id = $_GET['id'] ?? null;
        $user_id = Session::get('user_id');
        
        if ($id) {
            $model->delete($id, $user_id);
        }
        Session::redirect(BASE_URL . '/?page=transactions');
    }

    public function deleteFromCalendar() {
        Session::init();
        
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }

        $model = new Transaction();
        $user_id = Session::get('user_id');
        
        // Accepter POST et GET
        $id = $_POST['id'] ?? $_GET['id'] ?? null;
        $date = $_POST['date'] ?? $_GET['date'] ?? date('Y-m-d');
        
        if (!$id) {
            Session::set('error', 'ID de transaction manquant');
            $redirect = BASE_URL . '/?page=calendar&y=' . date('Y', strtotime($date)) . '&m=' . date('m', strtotime($date));
            Session::redirect($redirect);
        }
        
        if ($model->delete($id, $user_id)) {
            Session::set('success', 'Transaction supprimée avec succès');
        } else {
            Session::set('error', 'Erreur lors de la suppression');
        }
        
        $redirect = BASE_URL . '/?page=calendar&y=' . date('Y', strtotime($date)) . '&m=' . date('m', strtotime($date));
        Session::redirect($redirect);
    }

    public function index() {
        Session::init();
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }

        $user_id = Session::get('user_id');
        $model = new Transaction();
        $transactions = $model->getAll($user_id);
        $categories = $model->getCategories($user_id);

        require 'src/Views/transactions.php';
    }

    public function calendar() {
        Session::init();
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }

        $user_id = Session::get('user_id');
        $model = new Transaction();
        
        $year = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
        $month = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('m');
        
        if ($year < 2000 || $year > 2100) {
            $year = (int)date('Y');
        }
        if ($month < 1 || $month > 12) {
            $month = (int)date('m');
        }
        
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        
        $transactions = $model->getAll($user_id, "$year-$month");
        $balance = $model->getBalance($user_id, $year, $month);
        $categories = $model->getCategories($user_id);
        $budget = $model->getMonthlyBudget($user_id, $year, $month);

        require 'src/Views/calendar.php';
    }

    public function categories() {
        Session::init();
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }

        $user_id = Session::get('user_id');
        $model = new Transaction();
        $categories = $model->getCategories($user_id);

        require 'src/Views/categories.php';
    }

    public function addCategory() {
        Session::init();
        
        if (!Session::has('user_id')) {
            Session::set('error', 'Vous devez être connecté');
            Session::redirect(BASE_URL . '/?page=login');
        }
        
        $user_id = Session::get('user_id');
        $name = trim($_POST['name'] ?? '');
        $block_type = $_POST['block_type'] ?? '';
        
        if (empty($name)) {
            Session::set('error', 'Le nom de la catégorie est requis');
            Session::redirect(BASE_URL . '/?page=categories');
        }
        
        if (!in_array($block_type, ['revenus', 'debits_directs', 'debits_differes'])) {
            Session::set('error', 'Bloc invalide');
            Session::redirect(BASE_URL . '/?page=categories');
        }
        
        if ($block_type === 'revenus') {
            $type = 'income';
        } else {
            $type = 'expense';
        }
        
        $model = new Transaction();
        
        if ($model->createCategory($user_id, $name, $type, $block_type)) {
            Session::set('success', 'Catégorie "' . htmlspecialchars($name) . '" créée avec succès');
        } else {
            Session::set('error', 'Erreur lors de la création de la catégorie');
        }
        
        Session::redirect(BASE_URL . '/?page=categories');
    }

    public function editCategory() {
        Session::init();
        
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }
        
        $user_id = Session::get('user_id');
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            Session::redirect(BASE_URL . '/?page=categories');
        }
        
        $model = new Transaction();
        $category = $model->getCategoryById($id, $user_id);
        
        if (!$category) {
            Session::set('error', 'Catégorie non trouvée');
            Session::redirect(BASE_URL . '/?page=categories');
        }
        
        require 'src/Views/edit_category.php';
    }

    public function updateCategory() {
        Session::init();
        
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }
        
        $user_id = Session::get('user_id');
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $block_type = $_POST['block_type'] ?? '';
        
        if (!$id || empty($name)) {
            Session::set('error', 'Données invalides');
            Session::redirect(BASE_URL . '/?page=categories');
        }
        
        if (!in_array($block_type, ['revenus', 'debits_directs', 'debits_differes'])) {
            Session::set('error', 'Bloc invalide');
            Session::redirect(BASE_URL . '/?page=categories');
        }
        
        if ($block_type === 'revenus') {
            $type = 'income';
        } else {
            $type = 'expense';
        }
        
        $model = new Transaction();
        
        if ($model->updateCategory($id, $user_id, $name, $type, $block_type)) {
            Session::set('success', 'Catégorie modifiée avec succès');
        } else {
            Session::set('error', 'Erreur lors de la modification');
        }
        
        Session::redirect(BASE_URL . '/?page=categories');
    }

    public function deleteCategory() {
        Session::init();
        
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }
        
        $user_id = Session::get('user_id');
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            Session::redirect(BASE_URL . '/?page=categories');
        }
        
        $model = new Transaction();
        $result = $model->deleteCategory($id, $user_id);
        
        if ($result['success']) {
            Session::set('success', $result['message']);
        } else {
            Session::set('error', $result['message']);
        }
        
        Session::redirect(BASE_URL . '/?page=categories');
    }

    public function saveBudget() {
        Session::init();
        
        if (!Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=login');
        }
        
        $user_id = Session::get('user_id');
        $year = $_POST['year'] ?? date('Y');
        $month = $_POST['month'] ?? date('m');
        $starting_balance = $_POST['starting_balance'] ?? 0;
        $target_end_balance = $_POST['target_end_balance'] ?? 0;
        
        $model = new Transaction();
        
        if ($model->saveMonthlyBudget($user_id, $year, $month, $starting_balance, $target_end_balance)) {
            Session::set('success', 'Budget mensuel enregistré');
        } else {
            Session::set('error', 'Erreur lors de l\'enregistrement');
        }
        
        $redirect = BASE_URL . '/?page=calendar&y=' . $year . '&m=' . $month;
        Session::redirect($redirect);
    }
}