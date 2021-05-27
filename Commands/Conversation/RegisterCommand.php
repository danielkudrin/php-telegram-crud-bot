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

class RegisterCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'register';

    /**
     * @var string
     */
    protected $description = 'Register users';

    /**
     * @var string
     */
    protected $usage = '/register';

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
    protected $private_only = false;

    /**
     * Conversation Object
     *
     * @var Conversation
     */
    protected $conversation;

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
        $telegramName = $user->getUsername();

        $data = [
            'chat_id' => $chat_id,
        ];

        // Conversation start
        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        // Load any existing notes from this conversation
        $notes = &$this->conversation->notes;
        Request::sendMessage(['chat_id' => $chat_id, 'text' => $notes]);
        !is_array($notes) && $notes = [];

        // Load the current state of the conversation
        $state = $notes['state'] ?? 0;

        $result = Request::emptyResponse();

        // State machine
        // Every time a step is achieved the state is updated
        switch ($state) {
            case 0:
                if ($text === '') {
                    $notes['state'] = 0;
                    $this->conversation->update();

                    $data['text'] = 'Your name | Jūsu vārds | Ваше имя: ';

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['name'] = $text;
                $text          = '';

            // No break!
            case 1:
                if ($text === '') {
                    $notes['state'] = 1;
                    $this->conversation->update();

                    $data['text'] = 'Your tel. number | Jūsu tel. numurs | Ваш телефонный номер :';

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['number'] = $text;
                $text             = '';

            // No break!
            case 2:
                $notes['telegram_name'] = $telegramName;
                $this->conversation->update();
                unset($notes['state']);

                $data['text'] = "Thank you! | Paldies! | Спасибо!";

                $pdo = DB::getPdo();

                $practiceEventStmt = $pdo->prepare('SELECT id FROM practice_event ORDER BY id DESC LIMIT 1;');
                $practiceEventStmt->execute();
                $practiceEventId = $practiceEventStmt->fetch();

                $stmt = $pdo->prepare('
INSERT INTO registered_users (`practice_event_id`, `real_name`, `phone_number`, `telegram_name`) 
VALUES (:practice_event_id, :real_name, :phone_number, :telegram_name);
');
                $stmt->execute(
                    [
                        'practice_event_id' => $practiceEventId[0],
                        'real_name' => $notes['name'],
                        'phone_number' => $notes['number'],
                        'telegram_name' => $telegramName,
                    ]
                );

                $this->conversation->stop();

                $result = Request::sendMessage($data);
                break;
        }

        return $result;
    }
}
