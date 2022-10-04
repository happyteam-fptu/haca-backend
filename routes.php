<?php
/*
 * Created on Tue Oct 04 2022
 *
 * Copyright (c) 2022 Happy Team - SSG104 FPT University
 */

// Require router file for URL routing logic to works 
require_once("{$_SERVER['DOCUMENT_ROOT']}/include/router.php");
// Require database server connection credential establishment file for database queries to works
require_once("{$_SERVER['DOCUMENT_ROOT']}/include/serverconnect.php");

// Return access denied error page when user open the API link
get('/', 'errors/403.php');

// Handling login backend logic
any('/v1.0/auth/login', function () {
    // If method is GET then API is not supported, return message
    if ($_SERVER['REQUEST_METHOD'] == "GET")
        echo json_encode(array("error" => "method_not_supported", "detail" => "API không hỗ trợ method GET!"));
    else if ($_SERVER['REQUEST_METHOD'] == "POST") {
        // If method is POST then continue the logic
        if (isset($_POST['username']) && isset($_POST['password'])) {
            // TODO: Handling authentication logic with database

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