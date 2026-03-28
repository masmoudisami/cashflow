<?php
class Transaction {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($user_id, $category_id, $amount, $description, $date, $type, $method, $deferred) {
        try {
            $stmt = $this->db->prepare("INSERT INTO transactions (user_id, category_id, amount, description, transaction_date, type, payment_method, is_deferred) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$user_id, $category_id, $amount, $description, $date, $type, $method, $deferred]);
        } catch (PDOException $e) {
            error_log("Transaction create error: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $user_id, $category_id, $amount, $description, $date, $type, $method, $deferred) {
        try {
            $stmt = $this->db->prepare("UPDATE transactions SET category_id = ?, amount = ?, description = ?, transaction_date = ?, type = ?, payment_method = ?, is_deferred = ? WHERE id = ? AND user_id = ?");
            return $stmt->execute([$category_id, $amount, $description, $date, $type, $method, $deferred, $id, $user_id]);
        } catch (PDOException $e) {
            error_log("Transaction update error: " . $e->getMessage());
            return false;
        }
    }

    public function getById($id, $user_id) {
        try {
            $stmt = $this->db->prepare("SELECT t.*, c.name as category_name, c.block_type FROM transactions t JOIN categories c ON t.category_id = c.id WHERE t.id = ? AND t.user_id = ?");
            $stmt->execute([$id, $user_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Transaction getById error: " . $e->getMessage());
            return false;
        }
    }

    public function getAll($user_id, $month = null) {
        try {
            $sql = "SELECT t.*, c.name as category_name, c.block_type FROM transactions t JOIN categories c ON t.category_id = c.id WHERE t.user_id = ?";
            $params = [$user_id];
            if ($month) {
                $sql .= " AND DATE_FORMAT(t.transaction_date, '%Y-%m') = ?";
                $params[] = $month;
            }
            $sql .= " ORDER BY t.transaction_date DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Transaction getAll error: " . $e->getMessage());
            return [];
        }
    }

    public function delete($id, $user_id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
            return $stmt->execute([$id, $user_id]);
        } catch (PDOException $e) {
            error_log("Transaction delete error: " . $e->getMessage());
            return false;
        }
    }

    public function getBalance($user_id, $year, $month) {
        try {
            $start = "$year-$month-01";
            $end = date("Y-m-t", strtotime($start));
            
            $stmt = $this->db->prepare("SELECT type, SUM(amount) as total, payment_method, is_deferred FROM transactions WHERE user_id = ? AND transaction_date BETWEEN ? AND ? GROUP BY type, payment_method, is_deferred");
            $stmt->execute([$user_id, $start, $end]);
            $results = $stmt->fetchAll();

            $income = 0;
            $expense_direct = 0;
            $expense_deferred = 0;

            foreach ($results as $row) {
                if ($row['type'] === 'income') {
                    $income += $row['total'];
                } else {
                    if ($row['is_deferred'] == 1) {
                        $expense_deferred += $row['total'];
                    } else {
                        $expense_direct += $row['total'];
                    }
                }
            }

            $expense_total = $expense_direct + $expense_deferred;

            $budget = $this->getMonthlyBudget($user_id, $year, $month);
            $starting_balance = $budget ? (float)$budget['starting_balance'] : 0;
            $target_end_balance = $budget ? (float)$budget['target_end_balance'] : 0;
            
            $current = $starting_balance + $income - $expense_direct;
            $forecast = $starting_balance + $income - $expense_total;

            return [
                'income' => $income,
                'expense_direct' => $expense_direct,
                'expense_deferred' => $expense_deferred,
                'expense' => $expense_total,
                'deferred' => $expense_deferred,
                'starting_balance' => $starting_balance,
                'target_end_balance' => $target_end_balance,
                'current' => $current,
                'forecast' => $forecast,
                'exceeds_target' => $target_end_balance > 0 && $forecast < $target_end_balance
            ];
        } catch (PDOException $e) {
            error_log("getBalance error: " . $e->getMessage());
            return [
                'income' => 0,
                'expense_direct' => 0,
                'expense_deferred' => 0,
                'expense' => 0,
                'deferred' => 0,
                'starting_balance' => 0,
                'target_end_balance' => 0,
                'current' => 0,
                'forecast' => 0,
                'exceeds_target' => false
            ];
        }
    }

    public function getCategories($user_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM categories WHERE user_id = ? ORDER BY block_type, name");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("getCategories error: " . $e->getMessage());
            return [];
        }
    }

    public function getCategoryById($id, $user_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("getCategoryById error: " . $e->getMessage());
            return false;
        }
    }

    public function createCategory($user_id, $name, $type, $block_type) {
        try {
            // Vérifier que l'utilisateur existe
            $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                error_log("createCategory: User $user_id not found");
                return false;
            }
            
            // Insérer la catégorie
            $stmt = $this->db->prepare("INSERT INTO categories (user_id, name, type, block_type) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$user_id, $name, $type, $block_type]);
            
            if (!$result) {
                $error = $stmt->errorInfo();
                error_log("createCategory: SQL Error - " . print_r($error, true));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("createCategory: Exception - " . $e->getMessage());
            return false;
        }
    }

    public function updateCategory($id, $user_id, $name, $type, $block_type) {
        try {
            $stmt = $this->db->prepare("UPDATE categories SET name = ?, type = ?, block_type = ? WHERE id = ? AND user_id = ?");
            return $stmt->execute([$name, $type, $block_type, $id, $user_id]);
        } catch (PDOException $e) {
            error_log("updateCategory: " . $e->getMessage());
            return false;
        }
    }

    public function deleteCategory($id, $user_id) {
        try {
            // Vérifier si la catégorie est utilisée par des transactions
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM transactions WHERE category_id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                return ['success' => false, 'message' => 'Cette catégorie est utilisée par ' . $count . ' transaction(s)'];
            }
            
            $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
            $result = $stmt->execute([$id, $user_id]);
            
            return ['success' => $result, 'message' => $result ? 'Catégorie supprimée' : 'Erreur de suppression'];
        } catch (PDOException $e) {
            error_log("deleteCategory: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    public function getMonthlyBudget($user_id, $year, $month) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM monthly_budgets WHERE user_id = ? AND year = ? AND month = ?");
            $stmt->execute([$user_id, (int)$year, (int)$month]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Budget fetch error: " . $e->getMessage());
            return null;
        }
    }

    public function saveMonthlyBudget($user_id, $year, $month, $starting_balance, $target_end_balance) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO monthly_budgets (user_id, year, month, starting_balance, target_end_balance) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                    starting_balance = VALUES(starting_balance), 
                    target_end_balance = VALUES(target_end_balance)
            ");
            $result = $stmt->execute([
                (int)$user_id, 
                (int)$year, 
                (int)$month, 
                (float)$starting_balance, 
                (float)$target_end_balance
            ]);
            
            return $result;
        } catch (PDOException $e) {
            error_log("Budget save error: " . $e->getMessage());
            return false;
        }
    }
}