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
 * User "/survey" command
 *
 * Example of the Conversation functionality in form of a simple survey.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class UsersCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'users';

    /**
     * @var string
     */
    protected $description = 'Show next practice session registered users';

    /**
     * @var string
     */
    protected $usage = '/users';

    /**
     * @var string
     */
    protected $version = '0.4.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

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

        $chat    = $message->getChat();
        $user    = $message->getFrom();
        $text    = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        // Preparing response
        $data = [
            'chat_id'      => $chat_id,
        ];

        $pdo = DB::getPdo();

        $stmt = $pdo->prepare('SELECT * FROM practice_event ORDER BY id DESC LIMIT 1;');
        $stmt->execute();
        $practiceEvent = $stmt->fetch();

        $userStmt = $pdo->prepare('SELECT * FROM registered_users WHERE practice_event_id = :practice_event_id');
        $userStmt->execute(['practice_event_id' => $practiceEvent['id']]);
        $practiceEventUser = $userStmt->fetchAll();

        $data['text'] = "Зарегистрированные пользователи: " . PHP_EOL . PHP_EOL;

        foreach ($practiceEventUser as $user) {
            $data['text'] .= 'Имя: ' . $user['real_name'] . PHP_EOL;
            $data['text'] .= 'TG: ' . '@' . $user['telegram_name'] . PHP_EOL;
            $data['text'] .= 'Указанный номер телефона: ' . $user['phone_number'] . PHP_EOL;
            $data['text'] .= PHP_EOL . PHP_EOL;
        }

        Request::sendMessage($data);


        $result = Request::emptyResponse();

        return $result;
    }
}
