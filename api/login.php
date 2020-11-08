<?php
require '../bootstrap/bootstrap.php';

use Ahc\Jwt\JWT;

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("HTTP/1.1 405 Method Not Allowed");
    echo "method not allowed";
    exit;
}

if (!isset($_POST['username'], $_POST['password'])) {
    header("HTTP/1.1 400 Bad Request");
    echo "bad request";
    exit;
}

function returnToken($token)
{
    $jwtArray = ['token' => $token];
    header('Content-Type: application/json');
    echo json_encode($jwtArray);
    exit;
}

// https://github.com/adhocore/php-jwt
// Instantiate with key, algo, maxAge (s) and leeway (s).
$jwt = new JWT($_ENV['JWT_SECRET'], $_ENV['JWT_ALGORITHM'], $_ENV['JWT_MAX_AGE'], $_ENV['JWT_LEEWAY']);

if ($_POST['username'] == 'test' && $_POST['password'] == 'password') {
    $token = $jwt->encode([
        'uid'    => 1,
        'oid'    => 'DEV',
        'scopes' => ['user'],
    ]);
    returnToken($token);
} else if ($_POST['username'] == 'admin' && $_POST['password'] == 'password') {
    $token = $jwt->encode([
        'uid'    => 1,
        'oid'    => 'DEV',
        'scopes' => ['user', 'admin'],
    ]);
    returnToken($token);
} else {
    header("HTTP/1.1 401 Unauthorized");
    echo "unauthorized";
}
