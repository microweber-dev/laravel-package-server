<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllowedIps
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        $allowedIps = config('app.allowed_ips');
        if (!empty($allowedIps)) {
            //$allowedIps = explode(',', $allowedIps);
            //$allowedIps = array_trim($allowedIps);
            if (!empty($allowedIps)) {
                $isAllowed = false;
                foreach ($allowedIps as $allowedIp) {
                    $is = \Symfony\Component\HttpFoundation\IpUtils::checkIp($this->_userIp(), $allowedIp);
                    if ($is) {
                        $isAllowed = $is;
                    }
                }
                if (!$isAllowed) {
                    $error = 'You are not allowed to login from this IP address';
                    if ($request->expectsJson()) {
                        return response()->json(['error' => $error], 401);
                    }
                    return abort(403, $error);
                }
            }
        }

        return $next($request);
    }

    private function _userIp()
    {
        $ipaddress = '127.0.0.1';

        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ipaddress = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }  else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        }

        return $ipaddress;
    }

}
