<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Add Authorization if you use Bearer tokens

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200); // Or 204 No Content
    exit();
}
header('Content-Type: application/json; charset=UTF-8');

require_once '../config/db.php';
require_once '../vendor/autoload.php'; // Make sure to include the Composer autoloader
use Firebase\JWT\JWT;

$data = json_decode(file_get_contents("php://input"));

if(
    !empty($data->username) &&
    !empty($data->password)
) {
    $username = $data->username;
    $password = password_hash($data->password, PASSWORD_BCRYPT);

    // Check if username exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo json_encode(['message' => 'Invalid username or password']);
        http_response_code(401);
        exit();
    }
    // Verify password
    if (!password_verify($data->password, $user['password_hash'])) {
        echo json_encode(['message' => 'Invalid username or password']);
        http_response_code(401);
        exit();
    }
    // Generate JWT token
    $key = "bbc82dc9e38a077514c22114368bd6a404e187253307673070b3ac6a43e71396420ec4af6de6be950c6bd5cf7dc48b8838f329af88ca4c61af68ffdad1b18cf294e4ec990725b82723c1a49b25de53ce29767867ff2db2953e0fca1f0fab1db8f8b47fda01126111fdf8bb4cb5a9a1cf9c15102c258d9f04c30e780d780239f22eb982cbf2eddb35c1469ba77774edeee9b130dc89bbc29b11119c6fb275840494d2c37dcc5541df28d761c5dfd969fc7ab07914c96c93951b4dfa5d5b0258cd5f45298772671ad60a4d8985080a6e8fb4d56b4cb1cfa3165f4ae8245a10a14e4dea0a66da1a49739d65326205f05772346c3b9230abe432bc3c552f1bc9d408";
    $createdAt = time();
    $expirationTime = $createdAt + 3600 * 24; // jwt valid for 24 hours
    $payload = [
        'iat' => $createdAt,
        'exp' => $expirationTime,
        'data' => [
            'username' => $user['username'],
            'password' => $user['password_hash'],
        ]
        ];
    $jwt = JWT::encode($payload, $key, 'HS256');
    // Return JWT token
    echo json_encode(['token' => $jwt]);
    http_response_code(200);
}
else {
    echo json_encode(['message' => 'Invalid input']);
    http_response_code(400);
}
?>