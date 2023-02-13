<?php

declare(strict_types=1);

namespace App\Controllers\Admin\Setting;

use App\Controllers\BaseController;
use App\Models\Setting;

final class ImController extends BaseController
{
    public static $update_field = [
        'telegram_add_node',
        'telegram_add_node_text',
        'telegram_update_node',
        'telegram_update_node_text',
        'telegram_delete_node',
        'telegram_delete_node_text',
        'telegram_node_gfwed',
        'telegram_node_gfwed_text',
        'telegram_node_ungfwed',
        'telegram_node_ungfwed_text',
        'telegram_node_offline',
        'telegram_node_offline_text',
        'telegram_node_online',
        'telegram_node_online_text',
        'telegram_daily_job',
        'telegram_daily_job_text',
        'telegram_diary',
        'telegram_diary_text',
        'telegram_unbind_kick_member',
        'telegram_group_bound_user',
        'telegram_show_group_link',
        'telegram_group_link',
    ];

    public function im($request, $response, $args)
    {
        $settings = [];
        $settings_raw = Setting::get(['item', 'value', 'type']);

        foreach ($settings_raw as $setting) {
            if ($setting->type === 'bool') {
                $settings[$setting->item] = (bool) $setting->value;
            } else {
                $settings[$setting->item] = (string) $setting->value;
            }
        }

        return $response->write(
            $this->view()
                ->assign('update_field', self::$update_field)
                ->assign('settings', $settings)
                ->fetch('admin/setting/im.tpl')
        );
    }

    public function saveIm($request, $response, $args)
    {
        $list = self::$update_field;

        foreach ($list as $item) {
            $setting = Setting::where('item', '=', $item)->first();

            if ($setting->type === 'array') {
                $setting->value = \json_encode($request->getParam("${item}"));
            } else {
                $setting->value = $request->getParam("${item}");
            }

            if (! $setting->save()) {
                return $response->withJson([
                    'ret' => 0,
                    'msg' => "保存 ${item} 时出错",
                ]);
            }
        }

        return $response->withJson([
            'ret' => 1,
            'msg' => '保存成功',
        ]);
    }
}
