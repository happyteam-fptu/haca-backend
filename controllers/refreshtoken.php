<?php
/*
 * Created on Sat Oct 08 2022
 *
 * Copyright (c) 2022 Happy Team - SSG104 FPT University
 */

// Set response type header to JSON for better browser compatibility
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] == "GET")
    echo json_encode(array("error" => "method_not_supported", "detail" => "API không hỗ trợ method GET!"));
else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // If method is POST then continue the logic
    if (auth_verify(getBearerToken())) {
        if (getTokenType(getBearerToken()) == "refresh") {
            // JWT Authentication Access Token Generator for API
            // Include JWT class library for easier generation
            require_once("./utils/jwt.php");

            // TODO: Get information from old refresh token
            $decoded_payload = auth_verify(getBearerToken());

            // Include JWT Key variable from .env file
            // Try to keep it as private as possible...
            $JWT_KEY = getenv("JWT_KEY");
            // Usually expires in 4 hours
            $JWT_ACCESS_EXPIRE = getenv("JWT_ACCESS_TOKEN_EXPIRE_TIME");
            // Handling generating access token logic here
            // Setting JWT payload variables
            $payload = array();
            $payload['token_type'] = "access";
            $payload['iat'] = strtotime(date("Y-m-d H:i:s"));
            $payload['exp'] = strtotime(date("Y-m-d H:i:s")) + $JWT_ACCESS_EXPIRE;
            $payload['uname'] = $decoded_payload->uname;
            $payload['stid'] = $decoded_payload->stid;
            $payload['role'] = $decoded_payload->role;
            // Encode these data with JWT key in .env into JSON Web Token
            $token = JWT::encode($payload, $JWT_KEY);
            // Return success status if both username and password are correct
            echo json_encode(array(
                "status" => "success",
                "status_code" => "refresh_token_success",
                "detail" => "Làm mới mã token thành công!",
                "return_data" => array("access_token" => $token)
            ));
        } else {
            // Print out screen that refresh token isn't at the right type
            echo json_encode(array("error" => "wrong_token_type", "detail" => "Mã refresh token không đúng định dạng!"));
        }
    }
} else
    // If other method then API is not supported, return error
    echo json_encode(array("error" => "unknown_method", "detail" => "API không hỗ trợ method bạn đang sử dụng!"));