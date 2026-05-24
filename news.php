<?php
// news.php  (na raiz do projeto)
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

header('Content-Type: application/json');

// CORS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
    'https://andresantosdev.vercel.app',
    'https://www.andreprado.me',
    'https://andreprado.me',
    'http://localhost:4000',
    'http://127.0.0.1:4000',
    'http://localhost',
    'http://127.0.0.1'
];

if (in_array($origin, $allowedOrigins) || strpos($origin, 'localhost') !== false || strpos($origin, 'vercel.app') !== false) {
    header("Access-Control-Allow-Origin: $origin");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$apiKey = $_ENV['GNEWS_API_KEY'] ?? getenv('GNEWS_API_KEY') ?? '';

if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode(['error' => 'Chave API GNews não configurada']);
    exit;
}

$query   = $_GET['q'] ?? 'portfolio';
$lang    = $_GET['lang'] ?? 'pt';
$country = $_GET['country'] ?? 'br';
$max     = min((int)($_GET['max'] ?? 8), 10);

$url = "https://gnews.io/api/v4/search?" . http_build_query([
    'q'       => $query,
    'lang'    => $lang,
    'country' => $country,
    'max'     => $max,
    'apikey'  => $apiKey
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(502);
    echo json_encode(['error' => 'Erro de conexão com GNews', 'details' => $error]);
    exit;
}

if ($httpCode !== 200) {
    http_response_code(502);
    echo json_encode(['error' => 'Erro na GNews', 'code' => $httpCode]);
    exit;
}

echo $response;