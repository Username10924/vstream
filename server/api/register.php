<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=UTF-8');
require_once '../config/db.php';

$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->username) &&
    !empty($data->password) &&
    !empty($data->inviteCode)
) {
    $username = $data->username;
    $password = password_hash($data->password, PASSWORD_BCRYPT);
    $inviteCode = $data->inviteCode;
    // Check if the username is valid
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        echo json_encode(['message' => 'Invalid username']);
        http_response_code(400);
        exit();
    }
    // Check if the password is valid
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $data->password)) {
        echo json_encode(['message' => 'Invalid password']);
        http_response_code(400);
        exit();
    }
    // Check if the username already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // this will fetch one row with correct match
    if ($user) {
        echo json_encode(['message' => 'Username already exists']);
        http_response_code(409);
        exit();
    }
    // Check if the invite code is valid
    $stmt = $pdo->prepare("SELECT * FROM invite_codes WHERE invite_code = :inviteCode");
    $stmt->bindParam(':inviteCode', $inviteCode);
    $stmt->execute();
    $invite = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$invite) {
        echo json_encode(['message' => 'Invalid invite code']);
        http_response_code(400);
        exit();
    }
    if ($invite['is_used'] == true) {
        echo json_encode(['message' => 'Invite code already used']);
        http_response_code(400);
        exit();
    }
    // Insert the new user into the database
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password)");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    if ($stmt->execute()) {
        // Mark the invite code as used
        $stmt = $pdo->prepare("UPDATE invite_codes SET is_used = true WHERE invite_code = :inviteCode");
        $stmt->bindParam(':inviteCode', $inviteCode);
        $stmt->execute();
        echo json_encode(['message' => 'User registered successfully']);
        http_response_code(201);
    } else {
        echo json_encode(['message' => 'User registration failed']);
        http_response_code(500);
    }
}
?>