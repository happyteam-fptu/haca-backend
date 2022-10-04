<?php
require_once("{$_SERVER['DOCUMENT_ROOT']}/utils/jwt.php");

/** 
 * Get header Authorization
 * */
function getAuthorizationHeader()
{
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

/**
 * get access token from header
 * */
function getBearerToken()
{
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

function auth_verify($jwt)
{
    try {
        require_once("{$_SERVER['DOCUMENT_ROOT']}/include/jwtkey.php");
        if (empty($jwt)) throw new Exception("Token cannot be empty!");
        $decoded_payload = JWT::decode($jwt, getenv("JWT_KEY"), array("HS256"));
        return $decoded_payload;
    } catch (Exception $e) {
        if ($e->getMessage() == "Token cannot be empty!") die(json_encode(array(
            "error" => "auth_token_not_provided",
            "detail" => "Mã token xác thực chưa cung cấp!",
            // "debug_info" => array("err_msg" => $e->getMessage(), "token" => $jwt, "key" => $JWT_KEY)
        )));
        die(json_encode(array(
            "error" => "expired_token_or_algorithm_not_supported",
            "detail" => "Mã token đã hết hạn hoặc có thể thuật toán mã hóa của token chưa được hỗ trợ.",
            // "debug_info" => array("err_msg" => $e->getMessage(), "token" => $jwt, "key" => $JWT_KEY)
        )));
        return false;
    }
}

// Get User ID from access token
function getUIDFromToken($jwt)
{
    $tokenParts = explode('.', $jwt);
    $payload = base64_decode($tokenParts[1]);
    $uid = json_decode($payload)->stid;
    return $uid;
}

function getTokenType($jwt)
{
    $tokenParts = explode('.', $jwt);
    $payload = base64_decode($tokenParts[1]);
    $type = json_decode($payload)->token_type;
    return $type;
}
