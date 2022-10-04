<?php
require_once("{$_SERVER['DOCUMENT_ROOT']}/router.php");

get('/', 'errors/403.php');
get('/v1.0/auth/login', function () {
    echo 'Tung Anh dzai!';
});
get('/v1.0/student/info', function () {
    echo $_GET['tunganh'];
});

// ##################################################
// ##################################################
// ##################################################

// IT'S A MUST to place 404 at the bottom
any('/404', 'errors/404.php');