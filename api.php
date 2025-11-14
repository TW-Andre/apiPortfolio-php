<?php
header('Content-Type: application/json');

// Permitir localhost (dev) e Vercel (produção)
$allowed_origins = [
    'https://andresantosdev.vercel.app',
    'http://localhost:4000',
    'http://127.0.0.1:4000'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // Opcional: bloquear outros, ou permitir todos em dev
    header("Access-Control-Allow-Origin: *");
}

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Responde OPTIONS (pré-voo)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'users') {
    try {
        $host = $_ENV['DB_HOST'] ?? '';
        $dbname = $_ENV['DB_NAME'] ?? '';
        $user = $_ENV['DB_USER'] ?? '';
        $pass = $_ENV['DB_PASS'] ?? '';

        if (!$host || !$dbname) {
            throw new Exception('DB config missing');
        }

        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
        if (strpos($host, 'psdb.cloud') !== false) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        }

        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass, $options);
        $stmt = $pdo->query('SELECT * FROM users LIMIT 10');
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $users]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
}
?>