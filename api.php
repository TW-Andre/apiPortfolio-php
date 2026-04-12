<?php
// ====================== CORS - COLOQUE ISSO NO TOPO ABSOLUTO DO ARQUIVO ======================
// Nada de echo, print, espaço em branco ou require antes deste bloco!

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

$allowedOrigins = [
    'https://andresantosdev.vercel.app',
    'https://www.andresantosdev.com',     // adicione aqui se usar domínio customizado
    'http://localhost:4000',
    'http://127.0.0.1:4000',
    'http://localhost',
    'http://127.0.0.1'
];

// Libera o origin exato ou qualquer localhost / vercel.app
if (in_array($origin, $allowedOrigins) || 
    str_contains($origin, 'localhost') || 
    str_contains($origin, '127.0.0.1') || 
    str_contains($origin, 'vercel.app')) {
    
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, apikey, Authorization, X-Requested-With, Accept');
header('Access-Control-Max-Age: 86400');   // cache do preflight

// Responde imediatamente ao preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// ====================== SEU CÓDIGO NORMAL COMEÇA AQUI ======================

header('Content-Type: application/json');

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

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

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'JSON inválido', 'details' => json_last_error_msg()]);
            exit();
        }

        if (empty($data['name']) || empty($data['role'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Campos obrigatórios: name, role']);
            exit();
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

    case 'update_user':
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            echo json_encode(['error' => 'ID ou método inválido']);
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

    case 'delete_user':
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(400);
            echo json_encode(['error' => 'ID ou método inválido']);
            exit();
        }
        curl_setopt($ch, CURLOPT_URL, "$SUPABASE_URL/users?id=eq.$id");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: $APIKEY",
            "Authorization: Bearer $APIKEY"
        ]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Ação não encontrada']);
        exit();
}

// Executa a requisição para o Supabase
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);

curl_close($ch);

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
        'response' => json_decode($response, true) ?: $response
    ]);
    exit();
}

http_response_code($httpcode);
echo $response;