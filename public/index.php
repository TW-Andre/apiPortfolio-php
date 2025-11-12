<?php
// public/index.php - Entry point

// === CORS ===
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = ['http://localhost:4000', 'https://andresantosdev.vercel.app'];
if (in_array($origin, $allowed) || empty($origin)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// === Carrega config e rotas ===
require __DIR__ . '/../config.php';

$path = trim($_SERVER['PATH_INFO'] ?? '', '/');
$path_parts = $path ? explode('/', $path) : [];

if ($path_parts[0] === 'users') {
    require __DIR__ . '/../src/routes/users.php';
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Rota não encontrada']);