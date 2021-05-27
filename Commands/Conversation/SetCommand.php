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

use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class SetCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'set';

    /**
     * @var string
     */
    protected $description = 'Set a new practice session';

    /**
     * @var string
     */
    protected $usage = '/set';

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

        // Preparing response
        $data = [
            'chat_id'      => $chat_id,
            // Remove any keyboard by default
            'reply_markup' => Keyboard::remove(['selective' => true]),
        ];

        if ($chat->isGroupChat() || $chat->isSuperGroup()) {
            // Force reply is applied by default so it can work with privacy on
            $data['reply_markup'] = Keyboard::forceReply(['selective' => true]);
        }

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

                    $data['text'] = 'Введите Дату/Время тренировки: (Например: 23 апреля, 14:30)';

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['date'] = $text;
                $text          = '';

            // No break!
            case 1:
                if ($text === '') {
                    $notes['state'] = 1;
                    $this->conversation->update();

                    $data['text'] = 'Введите адрес проведения тренировки: (Например: Riga, Zeiferta iela 12)';

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['address'] = $text;
                $text             = '';

            // No break!
            case 2:
                if ($text === '') {
                    $notes['state'] = 2;
                    $this->conversation->update();

                    $data['text'] = 'Введите краткое описание тренировки: ';

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['description'] = $text;
                $text         = '';

            // No break!
            case 3:
                if ($text === '') {
                    $notes['state'] = 3;
                    $this->conversation->update();

                    $data['text'] = 'Введите сумму стоимости тренировки: ';

                    $result = Request::sendMessage($data);
                    break;
                }

                $notes['price'] = $text;
                $text = '';

            case 4:
                $this->conversation->update();
                unset($notes['state']);

                $pdo = DB::getPdo();

                try {
                    $stmt = $pdo->prepare(
                        'INSERT INTO practice_event (`event_date`, `event_address`, `event_description`, `event_price`) 
                    VALUES (?, ?, ?, ?);'
                    );
                    $stmt->execute([$notes['date'], $notes['address'], $notes['description'], $notes['price']]);
                    echo 'Set event successfully!' . PHP_EOL;
                } catch (\Exception $e) {
                    echo 'Unsuccessful insert into practice_event!' . PHP_EOL;
                }

                $out_text = "Тренировка успешно запланирована! Регистрация доступна: " . PHP_EOL . PHP_EOL;
                $out_text .= 'Дата проведения: ' . $notes['date'] . PHP_EOL;
                $out_text .= 'Адрес: ' . $notes['address'] . PHP_EOL;
                $out_text .= 'Краткое описание : ' . $notes['description'] . PHP_EOL;
                $out_text .= 'Цена : ' . $notes['price'] . PHP_EOL;

                $data['text'] = $out_text;

                $this->conversation->stop();

                $result = Request::sendMessage($data);
                break;
        }

        return $result;
    }
}
