<?php

declare(strict_types=1);

namespace App\Models;

/**
 * TelegramSession Model
 */
final class TelegramSession extends Model
{
    protected $connection = 'default';
    protected $table = 'telegram_session';
}
