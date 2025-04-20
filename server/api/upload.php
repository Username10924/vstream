<?php
require_once '../config/db.php';
require_once '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed']);
    exit();
}

$headers = getallheaders();
if(!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(['message' => 'Authorization header not found']);
    exit();
}

if (!isset($_POST['videoName'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Video name not provided']);
    exit();
}

$jwt = str_replace('Bearer ', '', $headers['Authorization']);
$secret_key = 'bbc82dc9e38a077514c22114368bd6a404e187253307673070b3ac6a43e71396420ec4af6de6be950c6bd5cf7dc48b8838f329af88ca4c61af68ffdad1b18cf294e4ec990725b82723c1a49b25de53ce29767867ff2db2953e0fca1f0fab1db8f8b47fda01126111fdf8bb4cb5a9a1cf9c15102c258d9f04c30e780d780239f22eb982cbf2eddb35c1469ba77774edeee9b130dc89bbc29b11119c6fb275840494d2c37dcc5541df28d761c5dfd969fc7ab07914c96c93951b4dfa5d5b0258cd5f45298772671ad60a4d8985080a6e8fb4d56b4cb1cfa3165f4ae8245a10a14e4dea0a66da1a49739d65326205f05772346c3b9230abe432bc3c552f1bc9d408';

$username = null;

try {
    $decode = JWT::decode($jwt, new Firebase\JWT\Key($secret_key, 'HS256'));
    $username = $decode->data->username;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['message' => 'User not found or invalid token']);
        exit();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Database error while fetching user']);
    exit();
}

// File upload
$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) {
    if(!mkdir($uploadDir, 0777, true)) {
        http_response_code(500);
        echo json_encode(['message' => 'Failed to create upload directory']);
        exit();
    }
}

if(!is_writable($uploadDir)) {
    http_response_code(500);
    echo json_encode(['message' => 'Upload directory is not writable']);
    exit();
}

if(!isset($_FILES['videoFile']) || $_FILES['videoFile']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    $errorMessage = 'File upload error';
    if (isset($_FILES['videoFile']['error'])) {
        switch ($_FILES['videoFile']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $errorMessage = 'File size exceeds the maximum limit';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $errorMessage = 'File too large';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMessage = 'File was only partially uploaded';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMessage = 'No file was uploaded';
                break;
            default:
                $errorMessage = 'Unknown upload error';
                break;
        }
    }
    echo json_encode(['message' => $errorMessage]);
    exit();
}

$fileName = $_POST['videoName'];
$fileSize = $_FILES['videoFile']['size'];
$fileTmpPath = $_FILES['videoFile']['tmp_name'];
$fileType = $_FILES['videoFile']['type'];
$fileBaseName = basename($_FILES['videoFile']['name']);
$fileExtension = strtolower(pathinfo($fileBaseName, PATHINFO_EXTENSION));

$maxFileSize = 100 * 1024 * 1024; // 100 MB
$allowedFileExtensions = ['mp4', 'avi', 'mov', 'mkv'];
if ($fileSize > $maxFileSize) {
    http_response_code(400);
    echo json_encode(['message' => 'File size larger than 100MB']);
    exit();
}

if (!in_array($fileExtension, $allowedFileExtensions)) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid file type']);
    exit();
}

// Generate a unique file name & Upload
$uniqueFileName = uniqid() . '.' . $fileExtension;
$destPath = $uploadDir . $uniqueFileName;

if (move_uploaded_file($fileTmpPath, $destPath)) {
    // Save video info to database
    try {
        $stmt = $pdo->prepare("INSERT INTO videos (username, video_name, file_name, file_size, file_type) VALUES (:username, :video_name, :file_name, :file_size, :file_type)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':video_name', $fileName);
        $stmt->bindParam(':file_name', $uniqueFileName);
        $stmt->bindParam(':file_size', $fileSize);
        $stmt->bindParam(':file_type', $fileType);
        $stmt->execute();
        $videoId = $pdo->lastInsertId();
        
        http_response_code(200);
        echo json_encode([
            'message' => 'File uploaded successfully',
            'videoId' => $videoId,
            'fileName' => $fileName
        ]);
    } catch (Exception $e) {
        unlink($destPath); // Remove the uploaded video
        http_response_code(500);
        echo json_encode(['message' => 'Database error']);
        exit();
    }
} else {
    http_response_code(500);
    echo json_encode(['message' => 'Failed to upload file']);
    exit();
}

?>