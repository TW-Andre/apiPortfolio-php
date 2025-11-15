<?php
header('Content-Type: application/json');

$allowed = ['https://andresantosdev.vercel.app', 'http://localhost:4000', 'http://127.0.0.1:4000'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, apikey, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'users') {
    $supabase_url = 'https://saqmuguywftejzehdcwx.supabase.co/rest/v1/users';
    $apikey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InNhcW11Z3V5d2Z0ZWp6ZWhkY3d4Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjI0MjUxNzUsImV4cCI6MjA3ODAwMTE3NX0.V6ZEn47kDYf_o5OwhmFMMjN9ZEqhBnVAVhbpwieKexU'; // ← COLE AQUI O ANON KEY

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $supabase_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $apikey",
        "Authorization: Bearer $apikey"
    ]);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode === 200) {
        echo $response; // já é JSON
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Supabase error', 'code' => $httpcode]);
    }
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Not found']);
}
?>