<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\Node;
use App\Models\User;
use App\Utils\Cookie as CookieUtils;
use App\Utils\Hash;
use function time;

final class Cookie extends Base
{
    public function login($uid, $time): void
    {
        $user = User::find($uid);
        $expire_in = $time + time();

        // 增加ip判断
        $remote_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        if ($remote_ip === null) {
            $remote_ip = $_SERVER['REMOTE_ADDR'];
        }

        if (strpos($_SERVER['HTTP_HOST'],"127.0.0.1") !== false || strpos($_SERVER['HTTP_HOST'],"localhost") !== false) {
            CookieUtils::set([
                'uid' => (string) $uid,
                'email' => $user->email,
                'key' => Hash::cookieHash($user->pass, $expire_in),
                'ip' => Hash::ipHash($remote_ip, $uid, $expire_in),
                'device' => Hash::deviceHash($_SERVER['HTTP_USER_AGENT'], $uid, $expire_in),
                'expire_in' => (string) $expire_in,
            ], $expire_in);
        } else {
            CookieUtils::setWithDomain([
                'uid' => (string) $uid,
                'email' => $user->email,
                'key' => Hash::cookieHash($user->pass, $expire_in),
                'ip' => Hash::ipHash($remote_ip, $uid, $expire_in),
                'device' => Hash::deviceHash($_SERVER['HTTP_USER_AGENT'], $uid, $expire_in),
                'expire_in' => (string) $expire_in,
            ], $expire_in, $_SERVER['HTTP_HOST']);
        }
    }

    public function getUser(): User
    {
        $uid = CookieUtils::get('uid');
        $email = CookieUtils::get('email');
        $key = CookieUtils::get('key');
        $ipHash = CookieUtils::get('ip');
        $deviceHash = CookieUtils::get('device');
        $expire_in = CookieUtils::get('expire_in');

        $user = new User();
        $user->isLogin = false;

        if ($uid === null ||
            $email === null ||
            $key === null ||
            $ipHash === null ||
            $deviceHash === null ||
            $expire_in === null
        ) {
            return $user;
        }

        if ($expire_in < time()) {
            return $user;
        }

        if ($_ENV['enable_login_bind_ip']) {
            // 增加ip判断
            $remote_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if ($remote_ip === null) {
                $remote_ip = $_SERVER['REMOTE_ADDR'];
            }

            $node = Node::where('node_ip', '=', $remote_ip)->first();

            if ($node === null && $ipHash !== Hash::ipHash($remote_ip, $uid, $expire_in)) {
                return $user;
            }
        }

        if ($_ENV['enable_login_bind_device']) {
            $ua = $_SERVER['HTTP_USER_AGENT'];

            if ($deviceHash !== Hash::deviceHash($ua, $uid, $expire_in)) {
                return $user;
            }
        }

        $user = User::find($uid);

        if ($user === null) {
            $user = new User();
            $user->isLogin = false;
            return $user;
        }

        if ($user->email !== $email) {
            $user = new User();
            $user->isLogin = false;
            return $user;
        }

        if (Hash::cookieHash($user->pass, $expire_in) !== $key) {
            $user = new User();
            $user->isLogin = false;
            return $user;
        }

        $user->isLogin = true;

        return $user;
    }

    public function logout(): void
    {
        if (strpos($_SERVER['HTTP_HOST'],"127.0.0.1") !== false || strpos($_SERVER['HTTP_HOST'],"localhost") !== false) {
            CookieUtils::set([
                'uid' => '',
                'email' => '',
                'key' => '',
                'ip' => '',
                'device' => '',
                'expire_in' => '',
            ], 0);
        } else {
            CookieUtils::setWithDomain([
                'uid' => '',
                'email' => '',
                'key' => '',
                'ip' => '',
                'device' => '',
                'expire_in' => '',
            ], 0, $_SERVER['HTTP_HOST']);
        }

    }
}
