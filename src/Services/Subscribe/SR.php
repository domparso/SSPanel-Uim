<?php

declare(strict_types=1);

namespace App\Services\Subscribe;

use App\Models\Config;
use App\Services\Subscribe;
use function base64_encode;
use function json_decode;
use function json_encode;
use const PHP_EOL;

final class SR extends Base
{
    public function getContent($user): string
    {
        $links = '';
        //判断是否开启V2Ray订阅
        if (! Config::obtain('enable_v2_sub')) {
            return $links;
        }

        $nodes_raw = Subscribe::getSubNodes($user);

        foreach ($nodes_raw as $node_raw) {
            $node_custom_config = json_decode($node_raw->custom_config, true);
            //檢查是否配置“前端/订阅中下发的服务器地址”
            if (! array_key_exists('server_user', $node_custom_config)) {
                $server = $node_raw->server;
            } else {
                $server = $node_custom_config['server_user'];
            }
            if ((int) $node_raw->sort === 11) {
                $enable_vless = $node_custom_config['enable_vless'] ?? '0';
                $v2_port = $node_custom_config['v2_port'] ?? ($node_custom_config['offset_port_user']
                    ?? ($node_custom_config['offset_port_node'] ?? 443));
                //默認值有問題的請懂 V2 怎麽用的人來改一改。
                $alter_id = $node_custom_config['alter_id'] ?? '0';
                $security = $node_custom_config['security'] ?? 'none';
                $network = $node_custom_config['network'] ?? '';
                $header = $node_custom_config['header'] ?? ['type' => 'none'];
                $header_type = $header['type'] ?? '';
                $host = $node_custom_config['host'] ?? '';
                $path = $node_custom_config['path'] ?? '/';
                $flow = $node_custom_config['flow'] ?? '';

                if ($enable_vless === "1") {
                    $encryption = $node_custom_config['encryption'] ?? 'none';
                    $allow_insecure = $node_custom_config['allow_insecure'] ?? '0';
                    $mux = $node_custom_config['mux'] ?? '';
//                    $transport = $node_custom_config['transport']
//                        ?? array_key_exists('grpc', $node_custom_config)
//                        && $node_custom_config['grpc'] === '1' ? 'grpc' : 'tcp';
                    $transport_plugin = $node_custom_config['transport_plugin'] ?? '';
                    $transport_method = $node_custom_config['transport_method'] ?? '';
                    $servicename = $node_custom_config['servicename'] ?? '';

                    $links .= 'vless://' . $user->uuid . '@' . $server . ':' . $v2_port . '?flow=' . ord($flow) . '&peer=' . $host . '&sni='
                        . $host . '&obfs=' . $transport_plugin . '&path=' . $path . '&mux=' . $mux . '&allowInsecure='
                        . $allow_insecure . '&obfsParam=' . $transport_method . '&type=' . $network . '&security='
                        . $security . '&serviceName=' . $servicename . '#' . $node_raw->name . PHP_EOL;
//                    $links .= 'vless://' . $user->uuid . '@' . $server . ':' . $v2_port . '?flow=' . $flow . '&encryption=' . $encryption . '&peer=' . $host . '&path=' . $path
//                        . '&type=' . $network . '&security=' . $security . '&header_type=' . $header_type . '#' . $node_raw->name . PHP_EOL;
                } else {
                    $v2rayn_array = [
                        'v' => '2',
                        'ps' => $node_raw->name,
                        'add' => $server,
                        'port' => $v2_port,
                        'id' => $user->uuid,
                        'aid' => $alter_id,
                        'net' => $network,
                        'type' => $header_type,
                        'host' => $host,
                        'path' => $path,
                        'tls' => $security,
                        'flow' => $flow,
                    ];
                    $links .= 'vmess://' . base64_encode(json_encode($v2rayn_array,JSON_UNESCAPED_SLASHES)) . PHP_EOL;
                }
            }
        }

        return base64_encode($links);
    }
}
