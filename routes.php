<?php
/*
 * Created on Tue Oct 04 2022
 *
 * Copyright (c) 2022 Happy Team - SSG104 FPT University
 */

// Require router file for URL routing logic to works 
require_once("{$_SERVER['DOCUMENT_ROOT']}/include/router.php");

// Return access denied error page when user open the API link
get('/', 'errors/403.php');

// Handling login backend logic
any('/v1.0/auth/login', function () {
    // Require database server connection credential establishment file for database queries to works
    require_once("{$_SERVER['DOCUMENT_ROOT']}/include/serverconnect.php");
    // Set response type header to JSON for better compatibility
    header('Content-Type: application/json; charset=utf-8');
    // If method is GET then API is not supported, return message
    if ($_SERVER['REQUEST_METHOD'] == "GET")
        echo json_encode(array("error" => "method_not_supported", "detail" => "API không hỗ trợ method GET!"));
    else if ($_SERVER['REQUEST_METHOD'] == "POST") {
        // If method is POST then continue the logic
        if (isset($_POST['username']) && isset($_POST['password'])) {
            // Handling authentication logic with database using prepared statements
            $stmt = $conn->prepare("SELECT username FROM se1741_students WHERE username=? LIMIT 1");
            $stmt->bind_param("s", $_POST['username']);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows == 1) {
                $stmt = $conn->prepare("SELECT name, last_name, username, member_code FROM se1741_students WHERE username=? AND password=? LIMIT 1");
                $stmt->bind_param("ss", $_POST['username'], $_POST['password']);
                $stmt->execute();
                $stmt->bind_result($name, $last_name, $username, $member_code);
                $stmt->store_result();
                // To check if the row exists
                if ($stmt->num_rows == 1) {
                    // Fetching the contents of the row
                    if ($stmt->fetch()) {
                        echo json_encode(array(
                            "status" => "success",
                            "status_code" => "user_auth_success",
                            "message" => "Đăng nhập thành công!",
                            "auth_data" => array("access_token" => "123", "refresh_token" => "abc")
                        ));
                    }
                } else {
                    echo json_encode(array(
                        "status" => "failed",
                        "status_code" => "user_auth_failed_wrong_pass",
                        "message" => "Mật khẩu sai! Vui lòng thử lại..."
                    ));
                }
            } else {
                echo json_encode(array(
                    "status" => "failed",
                    "status_code" => "user_auth_failed_wrong_username",
                    "message" => "Tên đăng nhập sai! Vui lòng thử lại..."
                ));
            }
        } else
            // If missing or wrong parameters then return an error
            echo json_encode(array("error" => "wrong_or_missing_params", "detail" => "Tham số bạn truyền vào cho API đang thiếu hoặc bị sai!"));
    } else
        // If other method then API is not supported, return error
        echo json_encode(array("error" => "unknown_method", "detail" => "API không hỗ trợ method bạn đang sử dụng!"));
});

// Handling getting student's information
get('/v1.0/student/info', function () {
});

// ##################################################
// ##################################################
// ##################################################

// IT'S A MUST to place 404 at the bottom
// Return API not found error when user navigate to unknown URLs
any('/404', 'errors/404.php');