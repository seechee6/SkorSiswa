<?php
namespace App\Models;

use PDO;

class User {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function register($name, $matric_no, $staff_no, $email, $password, $role) {
        try {
            // Check if user already exists by email, matric_no, or staff_no
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? OR matric_no = ? OR staff_no = ?");
            $stmt->execute([$email, $matric_no, $staff_no]);
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'error' => 'User with this email, matric number, or staff number already exists'
                ];
            }

            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $this->db->prepare(
                "INSERT INTO users (name, matric_no, staff_no, email, password_hash, role, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)"
            );

            $stmt->execute([
                $name,
                $matric_no,
                $staff_no,
                $email,
                $password_hash,
                $role
            ]);

            return [
                'success' => true,
                'message' => 'User registered successfully'
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    public function validateInput($data) {
        $errors = [];

        // Validate name
        if (empty($data['name']) || strlen($data['name']) < 2) {
            $errors['name'] = 'Name must be at least 2 characters long';
        }

        // Validate matric number or staff number
        if (empty($data['matric_no']) && empty($data['staff_no'])) {
            $errors['matric_no'] = 'Matric number or staff number is required';
        }
        if (!empty($data['matric_no']) && !preg_match('/^[A-Za-z0-9]+$/', $data['matric_no'])) {
            $errors['matric_no'] = 'Invalid matric number format';
        }
        if (!empty($data['staff_no']) && !preg_match('/^[A-Za-z0-9]+$/', $data['staff_no'])) {
            $errors['staff_no'] = 'Invalid staff number format';
        }

        // Validate email
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        // Validate password
        if (empty($data['password']) || strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters long';
        }

        // Validate role
        $validRoles = ['Student', 'Lecturer', 'Advisor', 'Admin'];
        if (empty($data['role']) || !in_array($data['role'], $validRoles)) {
            $errors['role'] = 'Invalid role selected';
        }

        return $errors;
    }

    public function login($login_id, $password) {
        // Try to find user by matric_no, staff_no, or email
        $stmt = $this->db->prepare("SELECT * FROM users WHERE matric_no = ? OR staff_no = ? OR email = ?");
        $stmt->execute([$login_id, $login_id, $login_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            return [
                'success' => true,
                'user' => $user
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Invalid login credentials'
            ];
        }
    }
}

// Only allow this endpoint for admin users (add authentication middleware)
$app->post('/admin/users', function ($request, $response) use ($db) {
    $data = $request->getParsedBody();
    // Validate and sanitize input...
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO users (name, matric_no, email, password_hash, staff_no, role) VALUES (?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute([
            $data['name'],
            $data['matric_no'],
            $data['email'],
            $passwordHash,
            $data['staff_no'],
            $data['role']
        ]);
        return $response->withJson(['success' => true]);
    } catch (PDOException $e) {
        return $response->withStatus(400)->withJson(['error' => 'User already exists or invalid data']);
    }
});
