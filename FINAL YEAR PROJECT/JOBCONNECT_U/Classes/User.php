<?php
class User {
    private $pdo;
    private $user_id;
    private $email;
    private $user_type;
    private $first_name;
    private $last_name;
    private $phone;
    private $created_at;
    private $last_login;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Load user by ID
    public function loadById($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if ($user) {
            $this->user_id = $user['user_id'];
            $this->email = $user['email'];
            $this->user_type = $user['user_type'];
            $this->first_name = $user['first_name'];
            $this->last_name = $user['last_name'];
            $this->phone = $user['phone'];
            $this->created_at = $user['created_at'];
            $this->last_login = $user['last_login'];
            return true;
        }
        return false;
    }

    // Load user by email
    public function loadByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $this->user_id = $user['user_id'];
            $this->email = $user['email'];
            $this->user_type = $user['user_type'];
            $this->first_name = $user['first_name'];
            $this->last_name = $user['last_name'];
            $this->phone = $user['phone'];
            $this->created_at = $user['created_at'];
            $this->last_login = $user['last_login'];
            return true;
        }
        return false;
    }

    // Create new user
    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO users (email, password_hash, user_type, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['user_type'],
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? null
        ]);

        if ($success) {
            $this->user_id = $this->pdo->lastInsertId();
            return $this->loadById($this->user_id);
        }
        return false;
    }

    // Update user
    public function update($data) {
        $stmt = $this->pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE user_id = ?");
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? null,
            $this->user_id
        ]);
    }

    // Update password
    public function updatePassword($new_password) {
        $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        return $stmt->execute([
            password_hash($new_password, PASSWORD_DEFAULT),
            $this->user_id
        ]);
    }

    // Delete user
    public function delete() {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE user_id = ?");
        return $stmt->execute([$this->user_id]);
    }

    // Verify password
    public function verifyPassword($password) {
        $stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->execute([$this->user_id]);
        $user = $stmt->fetch();
        return password_verify($password, $user['password_hash']);
    }

    // Record login
    public function recordLogin() {
        $stmt = $this->pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?");
        return $stmt->execute([$this->user_id]);
    }

    // Getters
    public function getId() { return $this->user_id; }
    public function getEmail() { return $this->email; }
    public function getUserType() { return $this->user_type; }
    public function getFirstName() { return $this->first_name; }
    public function getLastName() { return $this->last_name; }
    public function getFullName() { return $this->first_name . ' ' . $this->last_name; }
    public function getPhone() { return $this->phone; }
    public function getCreatedAt() { return $this->created_at; }
    public function getLastLogin() { return $this->last_login; }
}
?>