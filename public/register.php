<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once 'config.php';

function sendError($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            sendError('Invalid JSON input', 400);
        }
        
        $name = isset($data['name']) ? trim($data['name']) : '';
        $email = isset($data['email']) ? trim($data['email']) : '';
        $password = isset($data['password']) ? trim($data['password']) : '';
        
        // Validasi input
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Nama perusahaan tidak boleh kosong';
        } else if (strlen($name) < 3) {
            $errors[] = 'Nama perusahaan minimal 3 karakter';
        }
        
        if (empty($email)) {
            $errors[] = 'Email tidak boleh kosong';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }
        
        if (empty($password)) {
            $errors[] = 'Password tidak boleh kosong';
        } else if (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }
        
        // Check if email already exists
        $query = "SELECT id FROM companies WHERE email = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            sendError('Database error: ' . $conn->error, 500);
        }
        
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            sendError('Email sudah terdaftar', 409);
        }
        $stmt->close();
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Insert ke database
        $query = "INSERT INTO companies (name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            sendError('Database error: ' . $conn->error, 500);
        }
        
        $stmt->bind_param('sss', $name, $email, $hashedPassword);
        
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Registrasi berhasil. Silakan login.',
                'redirect' => 'index.html'
            ]);
        } else {
            sendError('Gagal melakukan registrasi: ' . $conn->error, 500);
        }
        
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        sendError('Error: ' . $e->getMessage(), 500);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>
