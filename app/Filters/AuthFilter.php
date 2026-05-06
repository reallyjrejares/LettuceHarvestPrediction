<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $isLoggedIn = $session->get('is_logged_in') === true;
        $userId = $session->get('user_id');
        $hasUser = (bool) $userId;

        if (! $isLoggedIn || ! $hasUser) {
            return redirect()->to(site_url('/'))->with('errors', ['login' => 'Please sign in to continue.']);
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);

        if (! $user) {
            $session->destroy();
            return redirect()->to(site_url('/'))->with('errors', ['login' => 'Session expired. Please sign in again.']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
