<?php

/**
 * This file is part of the PHP Telegram Bot example-bot package.
 * https://github.com/php-telegram-bot/example-bot/
 *
 * (c) PHP Telegram Bot Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 *
 * When using deep-linking, the parameter can be accessed by getting the command text.
 *
 * @see https://core.telegram.org/bots#deep-linking
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

class StartCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var string
     */
    protected $description = 'Start command';

    /**
     * @var string
     */
    protected $usage = '/start';

    /**
     * @var string
     */
    protected $version = '1.2.0';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $user    = $message->getFrom();
        $language = $user->getLanguageCode();

        if ($language === 'ru') {
            return $this->replyToChat(
                'Приветствую!' . PHP_EOL .
                'Вам доступны следующие команды: ' . PHP_EOL . PHP_EOL .
                '/show - Просмотр запланированной тренировки.' . PHP_EOL . PHP_EOL .
                '/register - Запись на ближайшую тренировку.' . PHP_EOL . PHP_EOL .
                '/set - Регистрация следующей тренировки.' . PHP_EOL . PHP_EOL .
                '/users - Просмотр зарегистрированных пользователей.' . PHP_EOL . PHP_EOL .
                '/help - Получение списка всех команд.' . PHP_EOL
            );
        }
        return $this->replyToChat(
            'Welcome!' . PHP_EOL .
            'The following commands are available: ' . PHP_EOL . PHP_EOL .
            '/show - View the details of the next practice session.' . PHP_EOL . PHP_EOL .
            '/register - Register for the next practice session.' . PHP_EOL . PHP_EOL .
            '/set - Set the details of the next practice session.' . PHP_EOL . PHP_EOL .
            '/users - View registered users for the next practice session.' . PHP_EOL . PHP_EOL .
            '/help - Get the list of available commands.' . PHP_EOL
        );

    }
}
