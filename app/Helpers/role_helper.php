<?php

if (!function_exists('hasRole')) {
    function hasRole($allowedRoles)
    {
        $userRole = session()->get('role_slug');
        return in_array($userRole, (array) $allowedRoles);
    }
}
