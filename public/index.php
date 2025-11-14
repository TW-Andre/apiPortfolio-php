<?php
// public/index.php - Entry point (sem .htaccess)

// === CORS ===
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = [
    'http://localhost:4000/',
    'https://andresantosdev.vercel.app',
    'http://localhost:5173'  // Vite padrão
];

if (in_array($origin, $allowed)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: http://localhost:4000"); // fallback
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

// === OPTIONS (preflight) ===
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// === Roteamento manual (sem .htaccess) ===
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($uri, '/');  // Remove barras
$segments = explode('/', $path);

// Remove "api" do caminho se estiver na URL
if ($segments[0] === 'api') {
    array_shift($segments);
}

// Agora $segments[0] é 'users', 'auth', etc.
if ($segments[0] === 'users') {
    require __DIR__ . '/../src/routes/users.php';
    exit;
}

// 404
http_response_code(404);
echo json_encode(['error' => 'Rota não encontrada']);