<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://andresantosdev.vercel.app/');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'users') {
    // Conexão com DB (use PDO)
    $pdo = new PDO('mysql:host=seu-host;dbname=seu-db', $user, $pass);
    $stmt = $pdo->query('SELECT * FROM users');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
?>