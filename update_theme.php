<?php
require_once __DIR__ . "/includes/auth.php";

$userId = $_SESSION['user']['id'];

$data = json_decode(file_get_contents("php://input"), true);

if(!isset($data['theme'])){
    http_response_code(400);
    exit;
}

$dark = $data['theme'] === 'dark' ? 1 : 0;

// insere ou atualiza
$st = $pdo->prepare("
    INSERT INTO user_preferences (user_id, dark_mode)
    VALUES (?,?)
    ON DUPLICATE KEY UPDATE dark_mode=VALUES(dark_mode)
");
$st->execute([$userId, $dark]);

echo json_encode(["ok"=>true]);
