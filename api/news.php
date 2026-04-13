<?php
// api/news.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // ajuste para o domínio da Vercel em produção
header('Access-Control-Allow-Methods: GET');

$apiKey = getenv('GNEWS_API_KEY');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$query = $_GET['q'] ?? 'tecnologia';        // palavra-chave
$category = $_GET['category'] ?? 'general'; // ou 'technology', 'business' etc.
$lang = $_GET['lang'] ?? 'pt';              // português
$country = $_GET['country'] ?? 'br';        // Brasil
$max = min((int)($_GET['max'] ?? 10), 50);  // limite da GNews

$url = "https://gnews.io/api/v4/top-headlines?" . http_build_query([
    'category' => $category,
    'lang'     => $lang,
    'country'  => $country,
    'max'      => $max,
    'apikey'   => $apiKey
]);

// Se quiser usar o endpoint de busca:
if (!empty($query)) {
    $url = "https://gnews.io/api/v4/search?" . http_build_query([
        'q'        => $query,
        'lang'     => $lang,
        'country'  => $country,
        'max'      => $max,
        'apikey'   => $apiKey
    ]);
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code(502);
    echo json_encode(['error' => 'Erro ao buscar notícias', 'code' => $httpCode]);
    exit;
}

echo $response; // devolve direto o JSON da GNews