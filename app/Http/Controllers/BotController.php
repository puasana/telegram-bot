<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Services\ChatGPTService;
use Telegram\Bot\Api;

use App\Models\Log;
use App\Models\SlotBooking;

class BotController extends Controller
{
    protected $chatGPTService;

    public function __construct(ChatGPTService $chatGPTService)
    {
        $this->chatGPTService = $chatGPTService;
    }

    public function commandsHandler()
    {
        $update = Telegram::getWebhookUpdates();

        // Check if the update contains a message
        if ($update->has('message')) {
            $message = $update->getMessage();

            $chatId = $message->getChat()->getId();
            $userId = $message->getFrom()->getId();
            $firstName = $message->getChat()->getFirstName();
            $text = $message->getText();
            $lines = [];

            Log::create([
                'telegram_account_id' => $userId,
                'telegram_account_name' => $firstName,
                'content' => $text
            ]);

            // Check if the text contains enter
            if (strpos($text, "\n")) {
                $lines = explode("\n", $text);
                $command = $lines[0];
            } else {
                $command = $text;
            }

            // Check if the message is a command or not
            if ($command === '/start') {
                $responseText = "Hello, $firstName! I am AEM Enersol Bot. Use /help to view the list of available commands";
            } elseif ($command === '/booking') {
                $type = '';
                $number = 0;
                
                if (count($lines) > 0) {
                    foreach ($lines as $line) {
                        $words = explode(' ', $line);
    
                        if (!in_array($words[0], ['type', 'number'])) {
                            $responseText = "Please provide the command correctly";
                        }
    
                        if (strtolower($words[0]) === 'type') {
                            if (strtolower($words[1]) === 'table') {
                                $type = 'table';
                            } elseif (strtolower($words[1]) === 'room') {
                                $type = 'room';
                            } else {
                                $responseText = "Please provide the command correctly";
                            }
                        }
    
                        if (strtolower($words[0]) === 'number') {
                            $number = $words[1];
                        }
                    }

                    if ($type && $number) {
                        SlotBooking::create([
                            'type' => $type,
                            'name' => $firstName,
                            'number' => $number
                        ]);

                        $responseText = "Great! the $type number $number is booked for you!";
                    }
                } else {
                    $responseText = "Please provide the command correctly";
                }
            } elseif ($command === '/check') {
                $table = SlotBooking::where('type', 'table')->count();
                $room = SlotBooking::where('type', 'room')->count();

                $responseText = "The table is booked at $table, and the room is booked at $room.";
            } elseif ($command === '/help') {
                $responseText = "The available command is \n /booking \n /check \n /ask";
            } elseif ($command === '/ask') {
                if (count($lines) > 0) {
                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => "Please wait as we retrieve the answer...",
                    ]);

                    $responseText = $this->chatGPTService->sendMessage($lines[1]);
                } else {
                    $responseText = "Please provide the command correctly";
                }
            } else {
                $responseText = "Please provide the command correctly or use /help to view the list of available commands";
            }

            // Send a response back to the user
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $responseText,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
