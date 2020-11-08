<?php
require '../bootstrap/bootstrap.php';

use Ahc\Jwt\JWT;

class AuthStatus {
    const SUCCESS = 0;
    const UNAUTHORIZED = 1;
    const FORBIDDEN = 2;
}

class AuthResult
{
    public int $status;
    public $uid;

    function __construct($status, $uid) {
        $this->status = $status;
        $this->uid = $uid;
    }
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
        return new AuthResult(AuthStatus::UNAUTHORIZED, null);
    }
    $intersect = array_intersect($payload['scopes'], $scopes);
    if (count($intersect) == count($scopes)) {
        $uid = $payload['uid'];
        return new AuthResult(AuthStatus::SUCCESS, $uid);
    } else {
        return new AuthResult(AuthStatus::FORBIDDEN, null);
    }
    return new AuthResult(AuthStatus::UNAUTHORIZED, null);
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

/**
 * Authentication and authorization wrapper function
 */
function auth_wrapper(callable $function, $scopes = ['user'])
{
    $token = getBearerToken();
    if ($token == null) abortUnauthorized();
    $verificationResult = verifyToken($token, $scopes);
    switch ($verificationResult->status) {
        case AuthStatus::SUCCESS:
            $function($verificationResult->uid);
            break;
        case AuthStatus::UNAUTHORIZED:
            abortUnauthorized();
            break;
        case AuthStatus::FORBIDDEN:
            abortForbidden();
            break;
        default:
            abortUnauthorized();
            break;
    }
}
