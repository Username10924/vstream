<?php
require_once '../config/db.php';
require_once '../vendor/autoload.php';
use Firebase\JWT\JWT;

header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200); // Or 204 No Content
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["message" => "Authorization header missing"]);
    exit;
}
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['newPassword'])) {
    http_response_code(400);
    echo json_encode(["message" => "Password not provided"]);
    exit;
}
$password = $data['newPassword'];
if (!isset($data['oldPassword'])) {
    http_response_code(400);
    echo json_encode(["message" => "Old password not provided"]);
    exit;
}
$oldPassword = $data['oldPassword'];
$jwt = str_replace('Bearer ', '', $headers['Authorization']);
$jwt_secret_key = "bbc82dc9e38a077514c22114368bd6a404e187253307673070b3ac6a43e71396420ec4af6de6be950c6bd5cf7dc48b8838f329af88ca4c61af68ffdad1b18cf294e4ec990725b82723c1a49b25de53ce29767867ff2db2953e0fca1f0fab1db8f8b47fda01126111fdf8bb4cb5a9a1cf9c15102c258d9f04c30e780d780239f22eb982cbf2eddb35c1469ba77774edeee9b130dc89bbc29b11119c6fb275840494d2c37dcc5541df28d761c5dfd969fc7ab07914c96c93951b4dfa5d5b0258cd5f45298772671ad60a4d8985080a6e8fb4d56b4cb1cfa3165f4ae8245a10a14e4dea0a66da1a49739d65326205f05772346c3b9230abe432bc3c552f1bc9d408";

try {
    $decoded = JWT::decode($jwt, new Firebase\JWT\Key($jwt_secret_key, 'HS256'));
    if (!isset($decoded->exp) || $decoded->exp < time()) {
        throw new Exception("Token expired");
    }
    if (!isset($decoded->data->username)) {
        throw new Exception("Invalid token");
    }
    $userId = $decoded->data->username;

    // check if the old password is correct
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE username = :id");
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        if (!password_verify($oldPassword, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(["message" => "Old password is incorrect"]);
            exit;
        }
        // update the password
        $newPasswordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = :password WHERE username = :id");
        $stmt->bindParam(':password', $newPasswordHash);
        $stmt->bindParam(':id', $userId);
        if ($stmt->execute()) {
            echo json_encode(["message" => "Password updated successfully"]);
            http_response_code(200);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to update password"]);
        }
    }
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["message" => "Invalid token", "error" => $e->getMessage()]);
    exit;
}
?>