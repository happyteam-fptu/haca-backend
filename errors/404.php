<?php
/*
 * Created on Tue Oct 04 2022
 *
 * Copyright (c) 2022 Happy Team - SSG104 FPT University
 */

// Set response type header to JSON for better browser compatibility
header('Content-Type: application/json; charset=utf-8');
// Set response code to 403
http_response_code(404);
// Print out messages
echo json_encode(array("error" => "not_found", "detail" => "Không tìm thấy API nào với địa chỉ bạn đã nhập!"));