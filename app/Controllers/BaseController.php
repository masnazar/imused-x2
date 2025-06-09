<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class BaseController extends Controller
{
    protected $helpers = ['url', 'form', 'text'];
    protected $authService;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    protected function renderSidebar()
{
    $roleId = session()->get('role_id');
    $menuService = new \App\Services\MenuService();
    return $menuService->getSidebarMenusForRole($roleId);
}

}
