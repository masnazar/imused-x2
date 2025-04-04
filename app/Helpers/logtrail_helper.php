<?php

if (!function_exists('logtrail')) {
    /**
     * Catat log aktivitas ke file log CodeIgniter
     *
     * @param string $action
     * @param string $message
     */
    function logtrail(string $action, string $message)
    {
        log_message('info', "[LOGTRAIL] ACTION: {$action} | {$message}");
    }
}
