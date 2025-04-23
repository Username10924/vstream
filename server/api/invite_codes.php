<?php
require_once '../config/db.php';
require_once '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["message" => "Auth header missing"]);
    exit;
}
$jwt = str_replace('Bearer ', '', $headers['Authorization']);
$jwt_secret_key = "bbc82dc9e38a077514c22114368bd6a404e187253307673070b3ac6a43e71396420ec4af6de6be950c6bd5cf7dc48b8838f329af88ca4c61af68ffdad1b18cf294e4ec990725b82723c1a49b25de53ce29767867ff2db2953e0fca1f0fab1db8f8b47fda01126111fdf8bb4cb5a9a1cf9c15102c258d9f04c30e780d780239f22eb982cbf2eddb35c1469ba77774edeee9b130dc89bbc29b11119c6fb275840494d2c37dcc5541df28d761c5dfd969fc7ab07914c96c93951b4dfa5d5b0258cd5f45298772671ad60a4d8985080a6e8fb4d56b4cb1cfa3165f4ae8245a10a14e4dea0a66da1a49739d65326205f05772346c3b9230abe432bc3c552f1bc9d408";

try {
    $decoded = JWT::decode($jwt, new Key($jwt_secret_key, 'HS256'));
    if (!isset($decoded->data->username)) {
        throw new Exception("Invalid token");
    }
    $username = $decoded->data->username;

    // check if user is admin
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || !$user['is_admin']) {
        http_response_code(403);
        echo json_encode(["message" => "Forbidden"]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT * FROM invite_codes");
        $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["invite_codes" => $codes]);
        http_response_code(200);
        exit;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // generate a new 16-digit random code to add to db
        $newCode = '';
        for ($i = 0; $i < 16; $i++) {
            $newCode .= mt_rand(0, 9);
        }
        $stmt = $pdo->prepare("INSERT INTO invite_codes (invite_code, is_used) VALUES (:invite_code, false)");
        $stmt->bindParam(':invite_code', $newCode);
        if ($stmt->execute()) {
            echo json_encode(["invite_code" => $newCode]);
            http_response_code(201);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to create invite code"]);
        }
        exit;
    }
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["message" => "Invalid token", "error" => $e->getMessage()]);
    exit;
}
?>