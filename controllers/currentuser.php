<?php
/*
 * Created on Sat Oct 08 2022
 *
 * Copyright (c) 2022 Happy Team - SSG104 FPT University
 */

// Set response type header to JSON for better browser compatibility
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if (auth_verify(getBearerToken()) && getTokenType(getBearerToken()) == "access") {
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
                "status" => "success",
                "status_code" => "retrieve_info_success",
                "detail" => "Lấy thông tin người dùng đang đăng nhập thành công!",
                "return_data" => array(
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
                )
            ));
        }
    } else {
        // Print out screen that refresh token isn't at the right type
        echo json_encode(array("error" => "wrong_token_type", "detail" => "Mã refresh token không đúng định dạng!"));
    }
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    echo json_encode(array("error" => "method_not_supported", "detail" => "API không hỗ trợ method POST!"));
} else
    // If other method then API is not supported, return error
    echo json_encode(array("error" => "unknown_method", "detail" => "API không hỗ trợ method bạn đang sử dụng!"));