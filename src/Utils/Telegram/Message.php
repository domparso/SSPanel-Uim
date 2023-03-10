<?php

declare(strict_types=1);

namespace App\Utils\Telegram;

use App\Models\Setting;
use App\Utils\TelegramSessionManager;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use function in_array;
use function json_decode;

final class Message
{
    /**
     * Bot
     */
    private Api $bot;

    /**
     * 触发用户TG信息
     */
    private array $triggerUser;

    /**
     * 消息会话 ID
     */
    private $ChatID;

    /**
     * 触发源信息
     */
    private \Telegram\Bot\Objects\Message $Message;

    /**
     * 触发源信息 ID
     */
    private $MessageID;

    /**
     * @throws TelegramSDKException
     */
    public function __construct(Api $bot, \Telegram\Bot\Objects\Message $Message)
    {
        $this->bot = $bot;
        $this->triggerUser = [
            'id' => $Message->getFrom()->getId(),
            'name' => $Message->getFrom()->getFirstName() . ' ' . $Message->getFrom()->getLastName(),
            'username' => $Message->getFrom()->getUsername(),
        ];
        $this->User = TelegramTools::getUser($this->triggerUser['id']);
        $this->ChatID = $Message->getChat()->getId();
        $this->Message = $Message;
        $this->MessageID = $Message->getMessageId();

        if ($this->Message->getText() !== null) {
            // 消息内容
            $MessageData = trim($this->Message->getText());
            if ($this->ChatID > 0) {
                // 私聊
                if (strlen($MessageData) === 16) {
                    $Uid = TelegramSessionManager::verifyBindSession($MessageData);
                    if ($Uid === 0) {
                        $text = '绑定失败了呢，经检查发现：【' . $MessageData . '】的有效期为 10 分钟，您可以在我们网站上的 **资料编辑** 页面刷新后重试.';
                    } else {
                        $BinsUser = TelegramTools::getUser($Uid, 'id');
                        $BinsUser->telegram_id = $this->triggerUser['id'];
                        $BinsUser->im_type = 4;
                        $BinsUser->im_value = $this->triggerUser['username'];
                        $BinsUser->save();
                        if ($BinsUser->is_admin >= 1) {
                            $text = '尊敬的**管理员**您好，恭喜绑定成功。' . PHP_EOL . '当前绑定邮箱为：' . $BinsUser->email;
                        } else {
                            if ($BinsUser->class >= 1) {
                                $text = '尊敬的 **VIP ' . $BinsUser->class . '** 用户您好.' . PHP_EOL . '恭喜您绑定成功，当前绑定邮箱为：' . $BinsUser->email;
                            } else {
                                $text = '绑定成功了，您的邮箱为：' . $BinsUser->email;
                            }
                        }
                    }

                    $this->bot->sendMessage(
                        [
                            'chat_id' => $this->ChatID,
                            'text' => $text,
                            'parse_mode' => 'Markdown',
                        ]
                    );
                }
            }
            return;
        }

        if ($this->Message->getNewChatParticipant() !== null) {
            $this->newChatParticipant();
        }
    }

    /**
     * 回复讯息 | 默认已添加 chat_id 和 message_id
     *
     * @param array $sendMessage
     *
     * @throws TelegramSDKException
     */
    public function replyWithMessage(array $sendMessage): void
    {
        $sendMessage = array_merge(
            [
                'chat_id' => $this->ChatID,
                'message_id' => $this->MessageID,
            ],
            $sendMessage
        );
        $this->bot->sendMessage($sendMessage);
    }

    /**
     * 入群检测
     *
     * @throws TelegramSDKException
     * @throws TelegramSDKException
     * @throws TelegramSDKException
     * @throws TelegramSDKException
     * @throws TelegramSDKException
     */
    public function newChatParticipant(): void
    {
        $NewChatMember = $this->Message->getNewChatParticipant();
        $Member = [
            'id' => $NewChatMember->getId(),
            'name' => $NewChatMember->getFirstName() . ' ' . $NewChatMember->getLastName(),
        ];
        if ($NewChatMember->getUsername() === $_ENV['telegram_bot']) {
            // 机器人加入新群组
            if (Setting::obtain('allow_to_join_new_groups') !== true && ! in_array($this->ChatID, json_decode(Setting::obtain('group_id_allowed_to_join')))) {
                // 退群

                $this->replyWithMessage(
                    [
                        'text' => '不约，叔叔我们不约.',
                    ]
                );

                TelegramTools::sendPost(
                    'kickChatMember',
                    [
                        'chat_id' => $this->ChatID,
                        'user_id' => $Member['id'],
                    ]
                );
                if (count(json_decode(Setting::obtain('telegram_admins'))) >= 1) {
                    foreach (json_decode(Setting::obtain('telegram_admins')) as $id) {
                        $this->bot->sendMessage(
                            [
                                'text' => '根据您的设定，Bot 退出了一个群组.' . PHP_EOL . PHP_EOL . '群组名称：' . $this->Message->getChat()->getTitle(),
                                'chat_id' => $id,
                            ]
                        );
                    }
                }
            } else {
                $this->replyWithMessage(
                    [
                        'text' => '雷猴啊。',
                    ]
                );
            }
        } else {
            // 新成员加入群组
            $NewUser = TelegramTools::getUser($Member['id']);
            $deNewChatMember = json_decode($NewChatMember, true);
            if (
                Setting::obtain('telegram_group_bound_user') === true
                &&
                $this->ChatID === $_ENV['telegram_chatid']
                &&
                $NewUser === null
                &&
                $deNewChatMember['is_bot'] === false
            ) {
                $this->replyWithMessage(
                    [
                        'text' => '由于 ' . $Member['name'] . ' 未绑定账户，将被移除。',
                    ]
                );

                TelegramTools::sendPost(
                    'kickChatMember',
                    [
                        'chat_id' => $this->ChatID,
                        'user_id' => $Member['id'],
                    ]
                );
                return;
            }
            if (Setting::obtain('enable_welcome_message') === true) {
                $text = ($NewUser->class >= 1 ? '欢迎 VIP' . $NewUser->class . ' 用户 ' . $Member['name'] . '加入群组。' : '欢迎 ' . $Member['name']);

                $this->replyWithMessage(
                    [
                        'text' => $text,
                    ]
                );
            }
        }
    }
}
