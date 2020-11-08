<?php
require '../vendor/autoload.php';

use Ahc\Jwt\JWT;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable('..');
$dotenv->load();

class AuthResult
{
    const SUCCESS = 0;
    const UNAUTHORIZED = 1;
    const FORBIDDEN = 2;
}

function getBearerToken()
{
    $header = null;
    if (isset($_SERVER['Authorization'])) {
        $header = trim($_SERVER['Authorization']);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $header = trim($_SERVER['HTTP_AUTHORIZATION']);
    } else {
        return;
    }

    $exploded = explode(' ', $header);
    if (count($exploded) == 2) {
        return $exploded[1];
    }
    return;
}

function verifyToken($token, $scopes)
{
    $payload = null;
    try {
        $payload = (new JWT($_ENV['JWT_SECRET'], $_ENV['JWT_ALGORITHM'], $_ENV['JWT_MAX_AGE'], $_ENV['JWT_LEEWAY']))->decode($token);
    } catch (\Ahc\Jwt\JWTException $e) {
        return AuthResult::UNAUTHORIZED;
    }
    $intersect = array_intersect($payload['scopes'], $scopes);
    if (count($intersect) == count($scopes)) {
        return AuthResult::SUCCESS;
    } else {
        return AuthResult::FORBIDDEN;
    }
}

function abortUnauthorized()
{
    header("HTTP/1.1 401 Unauthorized");
    echo "unauthorized";
    exit;
}

function abortForbidden()
{
    header("HTTP/1.1 403 Forbidden");
    echo "forbidden";
    exit;
}

// Authentication and authorization wrapper function
function auth_wrapper(callable $function, $params = null, $scopes = ['user'])
{
    $token = getBearerToken();
    if ($token == null) abortUnauthorized();
    $verificationResult = verifyToken($token, $scopes);
    switch ($verificationResult) {
        case AuthResult::SUCCESS:
            $function($params);
            break;
        case AuthResult::UNAUTHORIZED:
            abortUnauthorized();
            break;
        case AuthResult::FORBIDDEN:
            abortForbidden();
            break;
        default:
            abortUnauthorized();
            break;
    }
}
