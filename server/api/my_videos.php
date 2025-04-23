<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once '../config/db.php';
require_once '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Preflight request
    http_response_code(200);
    exit;
}
// get auth header (to confirm user)
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Auth header not found']);
    exit;
}

$authHeader = $headers['Authorization'];
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $jwt = $matches[1];
} else {
    http_response_code(401);
    echo json_encode(['message' => 'Invalid Token']);
    exit;
}

// Decode JWT token
$secret_key = 'bbc82dc9e38a077514c22114368bd6a404e187253307673070b3ac6a43e71396420ec4af6de6be950c6bd5cf7dc48b8838f329af88ca4c61af68ffdad1b18cf294e4ec990725b82723c1a49b25de53ce29767867ff2db2953e0fca1f0fab1db8f8b47fda01126111fdf8bb4cb5a9a1cf9c15102c258d9f04c30e780d780239f22eb982cbf2eddb35c1469ba77774edeee9b130dc89bbc29b11119c6fb275840494d2c37dcc5541df28d761c5dfd969fc7ab07914c96c93951b4dfa5d5b0258cd5f45298772671ad60a4d8985080a6e8fb4d56b4cb1cfa3165f4ae8245a10a14e4dea0a66da1a49739d65326205f05772346c3b9230abe432bc3c552f1bc9d408';
$username = null;

try {
    $decode = JWT::decode($jwt, new Firebase\JWT\Key($secret_key, 'HS256'));
    $username = $decode->data->username;

    // Check if the token is expired
    if ($decode->exp < time()) {
        http_response_code(401);
        echo json_encode(['message' => 'Token expired']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, video_name, file_name FROM videos WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($videos) {
        echo json_encode(['videos' => $videos]);
    } else {
        echo json_encode(['message' => 'No videos found']);
    }
    
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['message' => 'Failed to fetch videos']);
}