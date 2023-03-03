<?php

declare(strict_types=1);

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\Order;
use App\Models\Product;
use App\Models\UserCoupon;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use voku\helper\AntiXSS;

final class CouponController extends BaseController
{
    /**
     * @param array     $args
     */
    public function check(ServerRequest $request, Response $response, array $args)
    {
        $antiXss = new AntiXSS();
        $coupon_raw = $antiXss->xss_clean($request->getParam('coupon'));
        $product_id = $antiXss->xss_clean($request->getParam('product_id'));

        if ($coupon_raw === null || $coupon_raw === '') {
            return $response->withJson([
                'ret' => 0,
                'msg' => '优惠码不能为空',
            ]);
        }

        $coupon = UserCoupon::where('code', $coupon_raw)->first();

        if ($coupon === null) {
            return $response->withJson([
                'ret' => 0,
                'msg' => '优惠码无效',
            ]);
        }

        $product = Product::find($product_id);

        if ($product === null) {
            return $response->withJson([
                'ret' => 0,
                'msg' => '优惠码无效',
            ]);
        }

        $limit = \json_decode($coupon->limit);

        if ($limit->disabled === 1) {
            return $response->withJson([
                'ret' => 0,
                'msg' => '优惠码无效',
            ]);
        }

        $product_limit = explode(',' , $limit->product_id);

        if (! in_array($product_id, $product_limit)) {
            return $response->withJson([
                'ret' => 0,
                'msg' => '优惠码无效',
            ]);
        }

        $user = $this->user;
        $use_limit = $limit->use_time;

        if ($use_limit > 0) {
            $use_count = Order::where('user_id', $user->id)->where('coupon', $coupon->code)->count();
            if ($use_count >= $use_limit) {
                return $response->withJson([
                    'ret' => 0,
                    'msg' => '优惠码无效',
                ]);
            }
        }

        $content = \json_decode($coupon->content);
        
        if ($content->type === 'percentage') {
            $discount = $product->price * $content->value / 100;
        } else {
            $discount = $content->value;
        }

        $buy_price = $product->price - $content->value;

        return $response->withJson([
            'ret' => 1,
            'msg' => '优惠码可用',
            'discount' => $discount,
            'buy_price' => $buy_price,
        ]);
    }
}
