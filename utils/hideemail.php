<?php
/*
 * Created on Sat Oct 08 2022
 *
 * Copyright (c) 2022 Happy Team - SSG104 FPT University
 */

function hideEmailAddress($email)
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        list($first, $last) = explode('@', $email);
        $first = str_replace(substr($first, '3'), str_repeat('*', strlen($first) - 3), $first);
        $last = explode('.', $last);
        $last_domain = str_replace(substr($last['0'], '1'), str_repeat('*', strlen($last['0']) - 1), $last['0']);
        $vn = null;
        if (count($last) == 3) {
            $vn = "." . $last['2'];
        }
        $hideEmailAddress = $first . '@' . $last_domain . '.' . $last['1'] . $vn;
        return $hideEmailAddress;
    }
}