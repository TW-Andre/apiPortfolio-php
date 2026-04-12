<?php
header('Content-Type: application/json');

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

$allowedOrigins = [
    'https://andresantosdev.vercel.app',
    // ← Adicione aqui seu domínio customizado se tiver (ex: https://andresantosdev.com)
    'https://www.andresantosdev.com',   // exemplo
    'http://localhost:4000',
    'http://127.0.0.1:4000',
    'http://localhost',
    'http://127.0.0.1'
];

// Liberação exata + fallback para localhost
if (in_array($origin, $allowedOrigins) || 
    strpos($origin, 'localhost') !== false || 
    strpos($origin, '127.0.0.1') !== false) {
    
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} 
// else { não envia nada → bloqueia intencionalmente }

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, apikey, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400'); // cache do preflight por 24h

// Preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$SUPABASE_URL = $_ENV['SUPABASE_HOST'] . '/rest/v1';
$APIKEY = $_ENV['API_KEY'];

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

$ch = curl_init();
switch ($action) {
    case 'users':
        curl_setopt($ch, CURLOPT_URL, "$SUPABASE_URL/users");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: $APIKEY",
            "Authorization: Bearer $APIKEY"
        ]);
        break;
    
    case 'create_user':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);

        if(json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'JSON inválido', 'details' => json_last_error_msg()]);
            exit();
        }

        if (empty($data['name']) || empty($data['role'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Campos obrigatórios: name, role']);
        }

        curl_setopt($ch, CURLOPT_URL, "$SUPABASE_URL/users");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: $APIKEY",
            "Authorization: Bearer $APIKEY",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ]);
        
        break;

    case "update_user":
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo json_encode(['error:' => 'ID ou método inválido']);
            exit();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        curl_setopt($ch, CURLOPT_URL, "$SUPABASE_URL/users?id=eq.$id");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: $APIKEY",
            "Authorization: Bearer $APIKEY",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ]);
        break;
    
    case "delete_user":
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(400);
            echo json_encode(['error:' => 'ID ou método inválido']);
            exit();
        }
        $data = json_decode(file_get_contents('php://input'), true);
        curl_setopt($ch, CURLOPT_URL, "$SUPABASE_URL/users?id=eq.$id");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: $APIKEY",
            "Authorization: Bearer $APIKEY",
        ]);
        break;
    
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação não encontrada']);
        exit();
}

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);

if ($curl_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro cURL', 'details' => $curl_error]);
    exit();
}

if ($httpcode >= 400) {
    http_response_code($httpcode);
    echo json_encode([
        'error' => 'Erro Supabase',
        'code' => $httpcode,
        'response' => json_decode($response, true)
    ]);
    exit();
}

http_response_code($httpcode);
echo $response;

curl_close($ch);
?>