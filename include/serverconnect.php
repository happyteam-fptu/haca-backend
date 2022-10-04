<?php
/*
 * Created on Tue Oct 04 2022
 *
 * Copyright (c) 2022 Happy Team - SSG104 FPT University
 */

// File serverconnect.php chứa các thông tin quan trọng cần thiết để kết nối với máy chủ
// Bổ sung dòng `require_once("{$_SERVER['DOCUMENT_ROOT']}/include/serverconnect.php");` ở các file cần kết nối với máy chủ
// Trang web sẽ bị lỗi 500 Internal Error nếu như không tìm thấy file hoặc file bị lỗi trong hàm require()

// Include custom dotenv class for confidential and secure
require("{$_SERVER['DOCUMENT_ROOT']}/utils/dotenv.php");

(new DotEnv($_SERVER['DOCUMENT_ROOT'] . '/.env'))->load();
////////////////////////////////////////////
$maychu = getenv('DATABASE_HOST');
$tendangnhap = getenv('DATABASE_USER');
$matkhau = getenv('DATABASE_PASSWORD');
$tendb = getenv('DATABASE_NAME');
////////////////////////////////////////////
// Empty .env File Template:
// API_ENV=DEV || PRODUCTION
// BASE_URL_PRODUCTION=https://api-haca-se1741.tunnaduong.com
// BASE_URL_DEV=http://localhost
// DATABASE_HOST=localhost
// DATABASE_USER=root
// DATABASE_PASSWORD=
// DATABASE_NAME=sample_db
////////////////////////////////////////////
try {
    $db = mysqli_connect($maychu, $tendangnhap, $matkhau, $tendb);
    $conn = new mysqli($maychu, $tendangnhap, $matkhau, $tendb);
    $con = mysqli_connect($maychu, $tendangnhap, $matkhau, $tendb);
    // Mã này giúp cho trang khỏi bị các ký tự Unicode kì lạ
    mysqli_set_charset($conn, 'UTF8');
    mysqli_set_charset($db, 'UTF8');
    mysqli_set_charset($con, 'UTF8');
} catch (Exception $err) {
    die(json_encode(array("error" => "misconfigured_db_connect_failed", "detail" => "Có lỗi khi kết nối với database! Thử kiểm tra các thông tin đăng nhập và đảm bảo đã port 3306 đã được cho phép bởi tường lửa của server...")));
}