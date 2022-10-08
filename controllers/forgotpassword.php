<?php
/*
 * Created on Sat Oct 08 2022
 *
 * Copyright (c) 2022 Happy Team - SSG104 FPT University
 */

// Require database server connection credential establishment file for database queries to works
require_once("{$_SERVER['DOCUMENT_ROOT']}/include/serverconnect.php");
// Import custom partly hide email function
include_once("{$_SERVER['DOCUMENT_ROOT']}/utils/hideemail.php");
// Set response type header to JSON for better browser compatibility
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == "GET")
    echo json_encode(array("error" => "method_not_supported", "detail" => "API không hỗ trợ method GET!"));
else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // If method is POST then continue the logic
    if (empty($_POST['mode'])) {
        // If missing mode option then return an error
        die(json_encode(array(
            "status" => "failed",
            "status_code" => "missing_parameter",
            "detail" => "Thiếu trường 'mode'!"
        )));
    } else {
        // If mode option is not empty then continue the logic
        if ($_POST['mode'] == "change_with_verify") {
            // If mode option is change password then continue the logic
            if (empty($_POST['verify_code']) || empty($_POST['new_password'])) {
                // If missing verify_code or new_password then return an error
                die(json_encode(array(
                    "status" => "failed",
                    "status_code" => "missing_parameter",
                    "detail" => "Thiếu trường 'verify_code' hoặc 'new_password'!"
                )));
            } else {
                // If verify_code and new_password are not empty then continue the logic
            }
        } else if ($_POST['mode'] == "change_without_verify") {
            // Handling change password without verify code logic here
            // Usually when user is logged-in and want to change password
            if (empty($_POST['old_password']) || empty($_POST['new_password'])) {
                // If missing old_password or new_password then return an error
                die(json_encode(array(
                    "status" => "failed",
                    "status_code" => "missing_parameter",
                    "detail" => "Thiếu trường 'old_password' hoặc 'new_password'!"
                )));
            } else {
                // If old_password and new_password are not empty then continue the logic
            }
        } else if ($_POST['mode'] == "find") {
            // Handling find username to get email address or find user associated with
            // the inputted email then send email logic here
            if (empty($_POST['search_query'])) {
                // If missing email then return an error
                die(json_encode(array(
                    "status" => "failed",
                    "status_code" => "missing_parameter",
                    "detail" => "Thiếu trường 'search_query'!"
                )));
            } else {
                // If query is not empty then continue the logic
                if (filter_var($_POST['search_query'], FILTER_VALIDATE_EMAIL)) {
                    // If the input is a valid email address
                    $email = $_POST['search_query'];
                    $sql = "SELECT email FROM se1741_students WHERE email = '$email' LIMIT 1";
                    $res = $conn->query($sql);
                    if ($res->num_rows == 1) {
                        $row = $res->fetch_assoc();
                        // User found, sending email to user's inbox
                        die(json_encode(array(
                            "status" => "success",
                            "status_code" => "user_found_with_email_sent",
                            "detail" => "Đã gửi email xác nhận đổi mật khẩu đến '" . strtolower($row['email']) . "'!"
                        )));
                    } else {
                        die(json_encode(array(
                            "status" => "failed",
                            "status_code" => "user_not_found",
                            "detail" => "Không tìm thấy người dùng!"
                        )));
                    }
                } else {
                    // If the input is a invalid email address then it should be username or student ID
                    $input = $_POST['search_query'];
                    $sql = "SELECT name, username, email, avatar FROM se1741_students WHERE username = '$input' OR student_id = '$input' LIMIT 1";
                    $res = $conn->query($sql);
                    if ($res->num_rows == 1) {
                        $row = $res->fetch_assoc();
                        // User found, showing confirm screen
                        if (isset($_POST['confirm']) && $_POST['confirm'] == "1") {
                            // Send mail to user's inbox
                        } else
                            die(json_encode(array(
                                "status" => "success",
                                "status_code" => "user_found_awaiting_confirmation",
                                "detail" => "Đã tìm thấy người dùng! Vui lòng xác nhận trước khi gửi mail...",
                                "user_data" => array(
                                    "name" => $row['name'],
                                    "username" => $row['username'],
                                    "email" => strtolower(hideEmailAddress($row['email'])),
                                    "avatar" => $row['avatar'],
                                )
                            )));
                    } else {
                        die(json_encode(array(
                            "status" => "failed",
                            "status_code" => "user_not_found",
                            "detail" => "Không tìm thấy người dùng!"
                        )));
                    }
                }
            }
        } else {
            // If mode option is not change password or find username then return an error
            die(json_encode(array(
                "status" => "failed",
                "status_code" => "invalid_parameter",
                "detail" => "Trường 'mode' không hợp lệ!"
            )));
        }
    }
} else
    // If other method then API is not supported, return error
    die(json_encode(array("error" => "unknown_method", "detail" => "API không hỗ trợ method bạn đang sử dụng!")));