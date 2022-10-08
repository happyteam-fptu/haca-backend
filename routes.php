<?php
/*
 * Created on Tue Oct 04 2022
 *
 * Copyright (c) 2022 Happy Team - SSG104 FPT University
 */

// Setting PHP to display debug errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS Header allow request from any IP
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: *");

// Require router file for URL routing logic to works 
require_once("{$_SERVER['DOCUMENT_ROOT']}/include/router.php");
require_once("{$_SERVER['DOCUMENT_ROOT']}/include/authverify.php");

// ##################################################
// ##################################################
// ##################################################

// Return access denied error page when user open the API link
any('/', 'errors/403.php');
// Testing playground here folks:
get("/test", "test.php");
// Handling login backend logic
any("/v1.0/auth/login", "controllers/login.php");
// Handle generating new access token based on current valid refresh token...
any("/v1.0/auth/refresh", "controllers/refreshtoken.php");
// Handling forgot password logic
any("/v1.0/auth/password/reset", "controllers/forgotpassword.php");
// Handling getting current user's information
any('/v1.0/auth/user', "controllers/currentuser.php");

// ##################################################
// ##################################################
// ##################################################

// IT'S A MUST to place 404 at the bottom
// Return API not found error when user navigate to unknown URLs
any('/404', 'errors/404.php');