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

class ShowCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'show';

    /**
     * @var string
     */
    protected $description = 'Show next practice session details';

    /**
     * @var string
     */
    protected $usage = '/show';

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
        $practiceEventRow = $stmt->fetch(\PDO::FETCH_ASSOC);

        $data['text'] = "Registration is available | Registracija pieejama |Регистрация доступна" . PHP_EOL . PHP_EOL;
                $data['text'] .= 'Date | Datums | Дата : ' . $practiceEventRow['event_date'] . PHP_EOL . PHP_EOL;
                $data['text'] .= 'Address | Adrese | Адрес : ' . $practiceEventRow['event_address'] . PHP_EOL . PHP_EOL;
                $data['text'] .= 'Description | Apraksts |Краткое описание : ' . $practiceEventRow['event_description'] . PHP_EOL . PHP_EOL;
                $data['text'] .= 'Price | Cena | Цена : ' . $practiceEventRow['event_price'] . PHP_EOL . PHP_EOL;

        Request::sendMessage($data);


        $result = Request::emptyResponse();

        return $result;
    }
}
