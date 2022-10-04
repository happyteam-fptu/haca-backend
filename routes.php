<?php
/*
 * Created on Tue Oct 04 2022
 *
 * Copyright (c) 2022 Happy Team - SSG104 FPT University
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Require router file for URL routing logic to works 
require_once("{$_SERVER['DOCUMENT_ROOT']}/include/router.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/include/authverify.php");

// Return access denied error page when user open the API link
any('/', 'errors/403.php');

// Testing playground here folks:
get("/test", "test.php");

// Handling login backend logic
any("/v1.0/auth/login", function () {
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
});

// Handling getting current user's information
get('/v1.0/auth/user', function () {
    // Set response type header to JSON for better browser compatibility
    header('Content-Type: application/json; charset=utf-8');
    if (auth_verify(getBearerToken())) {
        // If having and accepted bearer token within the request authentication header then
        // continue executing the below code...
        // TODO: Query from database and return JSON data of current user...
        // Require database server connection credential establishment file for database queries to works
        require_once("{$_SERVER['DOCUMENT_ROOT']}/include/serverconnect.php");
        // Get student's ID from access token payload
        $uid = getUIDFromToken(getBearerToken());
        $sql = "SELECT student_id, member_code, name, first_name, middle_name, last_name, email, username, day_of_birth, month_of_birth, year_of_birth, role, avatar, gender FROM se1741_students WHERE student_id = '$uid'";
        $res = $conn->query($sql);
        if ($res->num_rows == 1) {
            $row = $res->fetch_assoc();
            echo json_encode(array(
                "student_id" => $row['student_id'],
                "member_code" => $row['member_code'],
                "name" => $row['name'],
                "first_name" => $row['first_name'],
                "middle_name" => $row['middle_name'],
                "last_name" => $row['last_name'],
                "email" => $row['email'],
                "username" => $row['username'],
                "day_of_birth" => $row['day_of_birth'],
                "month_of_birth" => $row['month_of_birth'],
                "year_of_birth" => $row['year_of_birth'],
                "role" => $row['role'],
                "avatar" => $row['avatar'],
                "gender" => $row['gender']
            ));
        }
    }
});

// ##################################################
// ##################################################
// ##################################################

// IT'S A MUST to place 404 at the bottom
// Return API not found error when user navigate to unknown URLs
any('/404', 'errors/404.php');
