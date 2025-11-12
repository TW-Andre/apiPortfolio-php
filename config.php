<<?php 
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['SUPABASE_HOST'];
$port = $_ENV['SUPABASE_PORT'];
$db   = $_ENV['SUPABASE_DB'];
$user = $_ENV['SUPABASE_USER'];
$pass = $_ENV['SUPABASE_PASS'];

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("SET client_encoding TO 'UTF8'");
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Conexão falhou: ' . $e->getMessage()]));
}
?>