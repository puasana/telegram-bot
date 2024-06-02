<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Api;

class BotTelegramController extends Controller
{
    protected $telegram;

    /**
     * Create a new controller instance.
     *
     * @param  Api  $telegram
     */
    public function __construct(Api $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Show the bot information.
     */
    public function show()
    {
        $response = $this->telegram->getMe();

        return $response;
    }

    public function setWebhook() 
    {
        $response = Telegram::setWebhook(['url' => env('TELEGRAM_WEBHOOK_URL')]);
        
        dd($response);
    }

    public function commandsHandler() 
    {
        $updates = Telegram::commandsHandler(true);
        $chatId = $updates->getChat()->getId();
        $username = $updates->getChat()->getFirstName();

        // $response = $this->telegram->getMe();

        // $botId = $response->getId();
        // $firstName = $response->getFirstName();
        // $username = $response->getUsername();

        // if (strtolower($updates->getMessage()->getText() === 'halo')) {
        //     return Telegram::sendMessage([
        //         'chat_id' => $chatId,
        //         'text' => 'Halo '. $username
        //     ]);
        // }

        return Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Halo '. $username
                ]);

        // $response = $telegram->sendMessage([
        //     'chat_id' => 'CHAT_ID',
        //     'text' => 'Hello World'
        // ]);
        
        // $messageId = $response->getMessageId();
    }
}
