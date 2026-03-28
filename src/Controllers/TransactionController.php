<?php
class TransactionController {
    
    /**
     * Ajouter une transaction depuis la page Transactions
     */
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

        if ($model->create($user_id, $category_id, $amount, $description, $date, $type, $method, $deferred)) {
            Session::set('success', 'Transaction ajoutée avec succès');
        } else {
            Session::set('error', 'Erreur lors de l\'ajout de la transaction');
        }
        
        Session::redirect(BASE_URL . '/?page=transactions');
    }

    /**
     * Ajouter une transaction depuis le Calendrier
     */
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

        if ($model->create($user_id, $category_id, $amount, $description, $date, $type, $method, $deferred)) {
            Session::set('success', 'Transaction ajoutée avec succès');
        } else {
            Session::set('error', 'Erreur lors de l\'ajout de la transaction');
        }
        
        $redirect = BASE_URL . '/?page=calendar&y=' . date('Y', strtotime($date)) . '&m=' . date('m', strtotime($date));
        Session::redirect($redirect);
    }

    /**
     * Modifier une transaction
     */
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
            Session::set('success', 'Transaction modifiée avec succès');
        } else {
            Session::set('error', 'Erreur lors de la modification');
        }
        
        $redirect = BASE_URL . '/?page=calendar&y=' . date('Y', strtotime($date)) . '&m=' . date('m', strtotime($date));
        Session::redirect($redirect);
    }

    /**
     * Formulaire de modification d'une transaction
     */
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

    /**
     * Supprimer une transaction depuis la page Transactions
     */
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

    /**
     * Supprimer une transaction depuis le Calendrier
     */
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

    /**
     * Afficher la liste des transactions
     */
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

    /**
     * Afficher le calendrier matriciel
     */
    public function calendar() {
        Session::init();
        
        error_log("=== calendar() START ===");
        
        if (!Session::has('user_id')) {
            error_log("calendar: No user_id in session");
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
        
        error_log("calendar: user_id=$user_id, year=$year, month=$month");
        
        try {
            $transactions = $model->getAll($user_id, "$year-$month");
            $balance = $model->getBalance($user_id, $year, $month);
            $categories = $model->getCategories($user_id);
            $budget = $model->getMonthlyBudget($user_id, $year, $month);
            
            error_log("calendar: transactions=" . count($transactions) . ", categories=" . count($categories));
        } catch (Exception $e) {
            error_log("calendar: Exception - " . $e->getMessage());
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

        require 'src/Views/calendar.php';
    }

    /**
     * Afficher la page de gestion des catégories
     */
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

    /**
     * Ajouter une catégorie
     */
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
        
        // Déterminer automatiquement le type selon le bloc
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

    /**
     * Formulaire de modification d'une catégorie
     */
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

    /**
     * Modifier une catégorie
     */
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
        
        // Déterminer automatiquement le type selon le bloc
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

    /**
     * Supprimer une catégorie
     */
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

    /**
     * Enregistrer le budget mensuel
     */
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