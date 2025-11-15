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
        // CONNECTION STRING COMPLETA DO SUPABASE (funciona no Render)
        $supabase_url = "pgsql:host=db.saqmuguywftejzehdcwx.supabase.co;port=5432;dbname=postgres;sslmode=require";
        $supabase_user = $_ENV['DB_USER'];      // postgres
        $supabase_pass = $_ENV['DB_PASS'];      // sua senha

        $pdo = new PDO($supabase_url, $supabase_user, $supabase_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10  // timeout de 10 segundos
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