<?php
class AuthController {
    public function login() {
        Session::init();
        if (Session::has('user_id')) {
            Session::redirect(BASE_URL . '/?page=dashboard');
        }
        
        $userModel = new User();
        $registrationDisabled = $userModel->hasAnyUser();
        
        require 'src/Views/login.php';
    }

    public function authenticate() {
        Session::init();
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            Session::set('error', 'Veuillez remplir tous les champs');
            Session::redirect(BASE_URL . '/?page=login');
        }

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if ($user === false) {
            Session::set('error', 'Erreur de connexion à la base de données');
            Session::redirect(BASE_URL . '/?page=login');
        }

        if ($user === null) {
            Session::set('error', 'Utilisateur inexistant');
            Session::redirect(BASE_URL . '/?page=login');
        }

        $verify = password_verify($password, $user['password']);
        
        if (!$verify) {
            error_log("Password verification failed for user: $username");
            Session::set('error', 'Mot de passe incorrect');
            Session::redirect(BASE_URL . '/?page=login');
        }

        session_regenerate_id(true);
        
        Session::set('user_id', (int)$user['id']);
        Session::set('username', $user['username']);
        Session::set('last_activity', time());
        
        Session::redirect(BASE_URL . '/?page=dashboard');
    }

    public function logout() {
        Session::init();
        Session::destroy();
        Session::redirect(BASE_URL . '/?page=login');
    }

    public function register() {
        Session::init();
        
        // Vérifier si des utilisateurs existent déjà
        $userModel = new User();
        if ($userModel->hasAnyUser()) {
            Session::set('error', 'Les inscriptions sont désactivées. Contactez l\'administrateur.');
            Session::redirect(BASE_URL . '/?page=login');
        }
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            Session::set('error', 'Veuillez remplir tous les champs');
            Session::redirect(BASE_URL . '/?page=login');
        }

        if (strlen($username) < 3 || strlen($username) > 50) {
            Session::set('error', 'Le nom d\'utilisateur doit faire entre 3 et 50 caractères');
            Session::redirect(BASE_URL . '/?page=login');
        }

        if (strlen($password) < 6) {
            Session::set('error', 'Le mot de passe doit faire au moins 6 caractères');
            Session::redirect(BASE_URL . '/?page=login');
        }

        if ($userModel->usernameExists($username)) {
            Session::set('error', 'Ce nom d\'utilisateur est déjà pris');
            Session::redirect(BASE_URL . '/?page=login');
        }

        if ($userModel->create($username, $password)) {
            Session::set('success', 'Compte créé. Connectez-vous.');
        } else {
            Session::set('error', 'Erreur lors de la création du compte');
        }
        Session::redirect(BASE_URL . '/?page=login');
    }
}