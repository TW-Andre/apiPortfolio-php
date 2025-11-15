<?php
header('Content-Type: application/json');

$allowed = ['https://andresantosdev.vercel.app', 'http://localhost:4000', 'http://127.0.0.1:4000'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'users') {
    try {
        // FORÇA IPv4 (evita erro de IPv6 no Render)
        $ipv4 = '34.201.16.99'; // IP público do seu Supabase (fixo por região)
        $dsn = "pgsql:hostaddr=$ipv4;port=5432;dbname={$_ENV['DB_NAME']};sslmode=require";
        
        $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $stmt = $pdo->query('SELECT * FROM users LIMIT 10');
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $data]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Not found']);
}
?>