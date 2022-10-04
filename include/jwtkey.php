<?php
/*
 * Created on Wed Oct 05 2022
 *
 * Copyright (c) 2022 Happy Team - SSG104 FPT University
 */

// Include custom dotenv class for confidential and secure
require_once("{$_SERVER['DOCUMENT_ROOT']}/utils/dotenv.php");

(new DotEnv($_SERVER['DOCUMENT_ROOT'] . '/.env'))->load();

$JWT_KEY = getenv("JWT_KEY");
