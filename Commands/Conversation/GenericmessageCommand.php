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
 * Generic message command
 *
 * Gets executed when any type of message is sent.
 *
 * In this message-related context, we can handle any kind of message.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Exception\TelegramException;

class GenericmessageCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'genericmessage';

    /**
     * @var string
     */
    protected $description = 'Handle generic message';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Main command execution
     *
     * @return ServerResponse
     */
    public function execute(): ServerResponse
    {

        $message = $this->getMessage();
        $message_text = $message->getText(true);
        $chat_id = $message->getChat()->getId();
        $user = $message->getFrom();
        $language = $user->getLanguageCode();

        // If a conversation is busy, execute the conversation command after handling the message.
        $conversation = new Conversation(
            $message->getFrom()->getId(),
            $message->getChat()->getId()
        );

        // Fetch conversation command if it exists and execute it.
        if ($conversation->exists() && $command = $conversation->getCommand()) {
            return $this->telegram->executeCommand($command);
        }


        if ($message_text !== '/start') {
            if ($language === 'ru') {
                $textMessage = 'Ваш запрос не найден...' . PHP_EOL . PHP_EOL;
                $textMessage .= 'Попробуйте одну из следующих команд:' . PHP_EOL . PHP_EOL;
                $textMessage .= '/help - Для получения всех команд.' . PHP_EOL;
                $textMessage .= '/show - Для запроса информации о следующей тренировке.' . PHP_EOL;
                $textMessage .= '/register - Для регистрации на тренировку' . PHP_EOL;

                return Request::sendMessage(['chat_id' => $chat_id, 'text' => $textMessage,]);
            }
            $textMessage = 'Your query is not found...' . PHP_EOL . PHP_EOL;
            $textMessage .= 'Try one of the following commands:' . PHP_EOL . PHP_EOL;
            $textMessage .= '/help - To view all available commands.' . PHP_EOL;
            $textMessage .= '/show - To view the next practice session.' . PHP_EOL;
            $textMessage .= '/register - To register for the next practice session.' . PHP_EOL;

            return Request::sendMessage(['chat_id' => $chat_id, 'text' => $textMessage,]);
        }


        return Request::emptyResponse();
    }
}

