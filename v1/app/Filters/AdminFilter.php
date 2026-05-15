<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $isAdmin = (bool) (session('auth_is_admin') ?? session('is_admin') ?? false);

        if (! $isAdmin) {
            log_message('error', 'Usuário não é administrador.');
            return redirect()->to('/')->with('erro', 'Acesso negado.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing required
    }
}