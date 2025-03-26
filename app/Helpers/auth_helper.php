<?php

if (!function_exists('user_id')) {
    function user_id()
    {
        $session = \Config\Services::session();
        return $session->get('user_id') ?? null;
    }
}