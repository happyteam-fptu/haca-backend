<?php
/*
 * Created on Sat Oct 08 2022
 *
 * Copyright (c) 2022 Happy Team - SSG104 FPT University
 */

// Require database server connection credential establishment file for database queries to works
require_once("{$_SERVER['DOCUMENT_ROOT']}/include/serverconnect.php");
// Set response type header to JSON for better browser compatibility
header('Content-Type: application/json; charset=utf-8');
// If method is GET then API is not supported, return message
if ($_SERVER['REQUEST_METHOD'] == "GET")
    echo json_encode(array("error" => "method_not_supported", "detail" => "API không hỗ trợ method GET!"));
else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // If method is POST then continue the logic
    if (empty($_POST['username']) || empty($_POST['password'])) {
        // If missing one or more fields then return an error
        if (empty($_POST['username']) && !empty($_POST['password'])) {
            $missing = array("username" => "Vui lòng điền vào trường này!");
        } else if (!empty($_POST['username']) && empty($_POST['password'])) {
            $missing = array("password" => "Vui lòng điền vào trường này!");
        } else {
            $missing = array("password" => "Vui lòng điền vào trường này!", "username" => "Vui lòng điền vào trường này!");
        }
        die(json_encode(array(
            "status" => "failed",
            "status_code" => "user_auth_failed_missing_field",
            "detail" => $missing
        )));
    }
    if (isset($_POST['username']) && isset($_POST['password'])) {
        // Handling authentication logic with database using prepared statements
        $stmt = $conn->prepare("SELECT username FROM se1741_students WHERE username=? LIMIT 1");
        $stmt->bind_param("s", $_POST['username']);
        $stmt->execute();
        $stmt->store_result();
        // We should use prepared statement, not normal SQL syntax. Because as I can see from my eyes,
        // many websites has this XSS or SQL Injection Bug. It might be harder to review the code, but
        // just in case there is some hacker curiously hacked our server :D
        if ($stmt->num_rows == 1) {
            $stmt = $conn->prepare("SELECT username, student_id, role FROM se1741_students WHERE username=? AND password=? LIMIT 1");
            $stmt->bind_param("ss", $_POST['username'], $_POST['password']);
            $stmt->execute();
            $stmt->bind_result($username, $student_id, $role);
            $stmt->store_result();
            // To check if the row exists
            if ($stmt->num_rows == 1) {
                // Fetching the contents of the row
                if ($stmt->fetch()) {
                    // JWT Authentication Access Token Generator for API
                    // Include JWT class library for easier generation
                    require_once("./utils/jwt.php");
                    // Include JWT Key variable from .env file
                    // Try to keep it as private as possible...
                    $JWT_KEY = getenv("JWT_KEY");
                    // Usually expires in 4 hours
                    $JWT_ACCESS_EXPIRE = getenv("JWT_ACCESS_TOKEN_EXPIRE_TIME");
                    // Usually expires in 1 month
                    $JWT_REFRESH_EXPIRE = getenv("JWT_REFRESH_TOKEN_EXPIRE_TIME");
                    // Handling generating access token logic here
                    // Setting JWT payload variables
                    $payload = array();
                    $payload['token_type'] = "access";
                    $payload['iat'] = strtotime(date("Y-m-d H:i:s"));
                    $payload['exp'] = strtotime(date("Y-m-d H:i:s")) + $JWT_ACCESS_EXPIRE;
                    $payload['uname'] = $username;
                    $payload['stid'] = $student_id;
                    $payload['role'] = $role;
                    // Encode these data with JWT key in .env into JSON Web Token
                    $token = JWT::encode($payload, $JWT_KEY);
                    // Clone current payload for access token into refresh token, save in other variable
                    $rpayload = $payload;
                    // Setting token type to refresh
                    $rpayload['token_type'] = "refresh";
                    $rpayload['iat'] = strtotime(date("Y-m-d H:i:s"));
                    // Change expire time to longer duration
                    $rpayload['exp'] = strtotime(date("Y-m-d H:i:s")) + $JWT_REFRESH_EXPIRE;
                    // Encode these data with JWT key in .env into JSON Web Token
                    $rtoken = JWT::encode($rpayload, $JWT_KEY);
                    // Return success status if both username and password are correct
                    echo json_encode(array(
                        "status" => "success",
                        "status_code" => "user_auth_success",
                        "detail" => "Đăng nhập thành công!",
                        "auth_data" => array("access_token" => $token, "refresh_token" => $rtoken)
                    ));
                }
            } else {
                // Return an error if the password is wrong
                echo json_encode(array(
                    "status" => "failed",
                    "status_code" => "user_auth_failed_wrong_pass",
                    "detail" => "Mật khẩu sai! Vui lòng thử lại..."
                ));
            }
        } else {
            // Return an error if there is no user exist with that inputted name
            echo json_encode(array(
                "status" => "failed",
                "status_code" => "user_auth_failed_unknown_user",
                "detail" => "Không tìm thấy người dùng mà bạn vừa nhập! Vui lòng thử lại..."
            ));
        }
    } else
        // If missing or wrong parameters then return an error
        echo json_encode(array(
            "error" => "wrong_or_missing_params",
            "detail" => "Tham số bạn truyền vào API đang thiếu hoặc bị sai!"
        ));
} else
    // If other method then API is not supported, return error
    echo json_encode(array("error" => "unknown_method", "detail" => "API không hỗ trợ method bạn đang sử dụng!"));