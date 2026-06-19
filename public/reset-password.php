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
        
        $token = isset($data['token']) ? trim($data['token']) : '';
        $password = isset($data['password']) ? trim($data['password']) : '';
        $confirmPassword = isset($data['confirmPassword']) ? trim($data['confirmPassword']) : '';
        $type = isset($data['type']) ? trim($data['type']) : 'company';
        
        // Validasi input
        $errors = [];
        
        if (empty($token)) {
            $errors[] = 'Token tidak valid';
        }
        
        if (empty($password)) {
            $errors[] = 'Password tidak boleh kosong';
        } else if (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        }
        
        if (empty($confirmPassword)) {
            $errors[] = 'Konfirmasi password tidak boleh kosong';
        } else if ($password !== $confirmPassword) {
            $errors[] = 'Password dan konfirmasi password tidak cocok';
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }
        
        $table = ($type === 'company') ? 'companies' : 'admins';
        
        // Cek token valid
        $query = "SELECT id, email FROM $table WHERE reset_token = ? AND reset_token_expiry > NOW()";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            sendError('Database error: ' . $conn->error, 500);
        }
        
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            sendError('Link reset password sudah kadaluarsa. Silakan minta link baru.', 401);
        }
        
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // Hash password baru
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Update password dan hapus token
        $updateQuery = "UPDATE $table SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        
        if (!$updateStmt) {
            sendError('Database error: ' . $conn->error, 500);
        }
        
        $updateStmt->bind_param('si', $hashedPassword, $user['id']);
        
        if ($updateStmt->execute()) {
            // Kirim email konfirmasi
            $to = $user['email'];
            $subject = "Password Reset Berhasil - UNSCollab";
            $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #28a745; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                    .body { background-color: #f9f9f9; padding: 20px; }
                    .footer { background-color: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>✓ Password Berhasil Di-Reset</h2>
                    </div>
                    <div class='body'>
                        <p>Halo,</p>
                        <p>Password akun Anda telah berhasil di-reset. Anda sekarang dapat login dengan password baru Anda.</p>
                        <p><strong>Jika Anda tidak melakukan ini, segera hubungi tim support kami!</strong></p>
                    </div>
                    <div class='footer'>
                        <p>&copy; 2026 UNSCollab. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
            $headers .= "From: no-reply@unscollab.com" . "\r\n";
            
            mail($to, $subject, $message, $headers);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Password berhasil di-reset. Silakan login dengan password baru Anda.'
            ]);
        } else {
            sendError('Gagal mereset password: ' . $conn->error, 500);
        }
        
        $updateStmt->close();
        $conn->close();
    } catch (Exception $e) {
        sendError('Error: ' . $e->getMessage(), 500);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>
