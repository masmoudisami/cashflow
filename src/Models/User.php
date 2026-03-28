<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($username, $password) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            return $stmt->execute([$username, $hash]);
        } catch (PDOException $e) {
            error_log("User creation error: " . $e->getMessage());
            return false;
        }
    }

    public function findByUsername($username) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("User lookup error: " . $e->getMessage());
            return false;
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("User lookup by ID error: " . $e->getMessage());
            return false;
        }
    }

    public function usernameExists($username) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetchColumn() > 0;
    }

    public function hasAnyUser() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM users");
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("User count error: " . $e->getMessage());
            return false;
        }
    }
}