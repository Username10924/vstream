<?php
require_once '../config/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Preflight request, just return 200 OK
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['message' => 'No video id provided']);
    exit;
}

$videoId = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = :video_id");
    $stmt->bindParam(':video_id', $videoId, PDO::PARAM_INT);
    $stmt->execute();

    $video = $stmt->fetch(PDO::FETCH_ASSOC);

    if($video) {
        http_response_code(200);
        echo json_encode(['video' => $video]);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Video not found']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Internal server error']);
}
?>