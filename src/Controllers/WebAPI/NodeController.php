<?php

declare(strict_types=1);

namespace App\Controllers\WebAPI;

use App\Controllers\BaseController;
use App\Models\Node;
use App\Models\StreamMedia;
use App\Utils\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Slim\Factory\AppFactory;
use function json_decode;
use function json_encode;
use function time;
use const VERSION;

final class NodeController extends BaseController
{
    public function saveReport(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $node_id = $request->getParam('node_id');
        $ip = $request->getServerParam('REMOTE_ADDR');
        if ($_ENV['checkNodeIp'] && ! $this->ckeckNodeIp($ip, $node_id)) {
            return AppFactory::determineResponseFactory()->createResponse(401)->withJson([
                'ret' => 0,
                'data' => 'Invalid request IP.',
            ]);
        }

        $content = $request->getParam('content');
        $result = json_decode(base64_decode($content), true);
        $report = new StreamMedia();
        $report->node_id = $node_id;
        $report->result = json_encode($result);
        $report->created_at = time();
        $report->save();

        return $response->withJson([
            'ret' => 1,
            'data' => 'ok',
        ]);
    }

    public function getInfo(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $node_id = $args['id'];
        $node = Node::find($node_id);

        if ($node === null) {
            return $response->withJson([
                'ret' => 0,
                'data' => 'Node not found.',
            ]);
        }

        $ip = $request->getServerParam('REMOTE_ADDR');
        if ($ip !== '127.0.0.1' && $node->node_ip !== $ip) {
            return AppFactory::determineResponseFactory()->createResponse(401)->withJson([
                'ret' => 0,
                'data' => 'Invalid request IP.',
            ]);
        }

        if ($node->sort === 0) {
            $node_explode = explode(';', $node->server);
            $node_server = $node_explode[0];
        } else {
            $node_server = $node->server;
        }
        $data = [
            'node_group' => $node->node_group,
            'node_class' => $node->node_class,
            'node_speedlimit' => $node->node_speedlimit,
            'traffic_rate' => $node->traffic_rate,
            'mu_only' => 0,
            'sort' => $node->sort,
            'server' => $node_server,
            'custom_config' => json_decode($node->custom_config, true, JSON_UNESCAPED_SLASHES),
            'type' => 'SSPanel-UIM',
            'version' => VERSION,
        ];

        return ResponseHelper::successWithDataEtag($request, $response, [
            'ret' => 1,
            'data' => $data,
        ]);
    }

    public function ckeckNodeIp($ip, $node_id): bool
    {
        $node = Node::find($node_id);
        if ($ip !== '127.0.0.1' && $node->node_ip !== $ip) {
            return false;
        } else {
            return true;
        }
    }

}
