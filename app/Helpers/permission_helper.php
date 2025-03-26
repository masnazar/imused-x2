<?php

if (!function_exists('has_permission')) {
    function has_permission($permission)
{
    $session = session();
    $rolePermissions = $session->get('permissions') ?? [];
    $userPermissions = $session->get('user_permissions') ?? [];

    $allPermissions = array_unique(array_merge($rolePermissions, $userPermissions));

    return in_array($permission, $allPermissions);
}
}
