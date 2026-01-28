<?php

if (!function_exists('FullnameCapital')) {
    function FullnameCapital($firstname, $lastname) {
        $firstname = mb_convert_case(trim($firstname), MB_CASE_TITLE, 'UTF-8');
        $lastname = mb_convert_case(trim($lastname), MB_CASE_TITLE, 'UTF-8');
        return trim($firstname . ' ' . $lastname);
    }
}