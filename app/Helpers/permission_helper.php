<?php

if (!function_exists('has_permission')) {
    function has_permission($permission)
    {
        $session = session();
        $permissions = $session->get('permissions') ?? [];

        return in_array($permission, $permissions);
    }
}
