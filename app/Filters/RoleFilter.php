<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $role = session()->get('role');
        if (! $role) {
            return redirect()->to(site_url('login'));
        }
        // Owner boleh mengakses semua area
        if ($role === 'owner') return null;

        if ($arguments && ! in_array($role, $arguments, true)) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
