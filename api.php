<?php
// api.php
header('Content-Type: application/json');

// === CORS: Permite localhost e produção ===
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

$allowed_origins = [
    'http://localhost:4000',      // Seu front local
    'http://localhost:5173',      // Vite padrão
    'https://andresantosdev.vercel.app/', // Seu front em produção (adicione depois)
    // Adicione mais se precisar
];

if (in_array($origin, $allowed_origins) || empty($origin)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: http://localhost:4000"); // fallback
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// === TRATA PREFlight (OPTIONS) ===
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'config.php';
$method = $_SERVER['REQUEST_METHOD'];
$path = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));

if ($path[0] === 'users') {
    require 'src/routes/users.php';
    exit;
}

// 404
http_response_code(404);
echo json_encode(['error' => 'Rota não encontrada']);