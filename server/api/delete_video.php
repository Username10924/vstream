<?php
require_once '../config/db.php';
require_once '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit();
}

$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Authorization header not found']);
    exit();
}

$jwt = str_replace('Bearer ', '', $headers['Authorization']);
$secret_key = 'bbc82dc9e38a077514c22114368bd6a404e187253307673070b3ac6a43e71396420ec4af6de6be950c6bd5cf7dc48b8838f329af88ca4c61af68ffdad1b18cf294e4ec990725b82723c1a49b25de53ce29767867ff2db2953e0fca1f0fab1db8f8b47fda01126111fdf8bb4cb5a9a1cf9c15102c258d9f04c30e780d780239f22eb982cbf2eddb35c1469ba77774edeee9b130dc89bbc29b11119c6fb275840494d2c37dcc5541df28d761c5dfd969fc7ab07914c96c93951b4dfa5d5b0258cd5f45298772671ad60a4d8985080a6e8fb4d56b4cb1cfa3165f4ae8245a10a14e4dea0a66da1a49739d65326205f05772346c3b9230abe432bc3c552f1bc9d408';

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['videoId'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Video ID not provided']);
    exit();
}

$videoId = $data['videoId'];
$username = null;

try {
    $decode = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    $username = $decode->data->username;

    // Fetch video info
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = :id AND username = :username");
    $stmt->bindParam(':id', $videoId);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $video = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$video) {
        http_response_code(404);
        echo json_encode(['message' => 'Video not found or not authorized']);
        exit();
    }

    // Delete file from server
    $filePath = '../uploads/' . $video['file_name'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM videos WHERE id = :id AND username = :username");
    $stmt->bindParam(':id', $videoId);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    http_response_code(200);
    echo json_encode(['message' => 'Video deleted successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Error deleting video']);
    exit();
}
?>