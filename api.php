<?php
header('Content-Type: application/json');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = ['https://andresantosdev.vercel.app', 'http://localhost:4000', 'http://127.0.0.1:4000', 'http://localhost', 'http://127.0.0.1'];

if (in_array($origin, $allowed)) {
    header("Access-Control-Allow-Origin: $origin");
} else if (strpos($origin, 'localhost') !== false || strpos($origin, '127.0.0.1') !== false) {
    header("Access-Control-Allow-Origin: $origin"); // liberação dinâmica local
}

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, apikey, Authorization');
header('Access-Control-Allow-Credentials: true');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$SUPABASE_URL = 'https://saqmuguywftejzehdcwx.supabase.co/rest/v1';
$APIKEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InNhcW11Z3V5d2Z0ZWp6ZWhkY3d4Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjI0MjUxNzUsImV4cCI6MjA3ODAwMTE3NX0.V6ZEn47kDYf_o5OwhmFMMjN9ZEqhBnVAVhbpwieKexU'; // ← COLE AQUI O ANON KEY

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
        /* $input = file_get_contents('php://input');
        $data = json_decode($input, true); */
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