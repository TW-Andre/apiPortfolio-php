<?php
// src/routes/users.php
require_once __DIR__ . '/../controllers/UserController.php';

$controller = new UserController($pdo);

if ($method === 'GET') {
    $users = $controller->getAll();
    echo json_encode($users);
}