<?php
require '../middlewares/auth_middleware.php';

if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    header("HTTP/1.1 405 Method Not Allowed");
    echo "method not allowed";
    exit;
}

auth_wrapper(function ($uid) {
    header('Content-Type: application/json');
    echo json_encode(['data' => 'this is extremely sensitive information!']);
});
