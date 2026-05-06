<?php

namespace App\Filters;

use App\Models\AdminModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $isAdminLoggedIn = $session->get('admin_logged_in') === true;
        $adminId = (int) ($session->get('admin_id') ?? 0);

        if (! $isAdminLoggedIn || $adminId <= 0) {
            return redirect()->to(site_url('admin/login'))->with('adminErrors', ['login' => 'Please sign in as admin to continue.']);
        }

        $adminModel = new AdminModel();
        $admin = $adminModel->find($adminId);

        if (! $admin) {
            $session->destroy();
            return redirect()->to(site_url('admin/login'))->with('adminErrors', ['login' => 'Admin session expired. Please sign in again.']);
        }

        $session->set([
            'admin_id' => (int) $admin['id'],
            'admin_username' => (string) ($admin['username'] ?? ''),
            'admin_logged_in' => true,
        ]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
