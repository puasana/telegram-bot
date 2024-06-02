<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Services\ChatGPTService;
use Telegram\Bot\Api;

class BotTestController extends Controller
{
    protected $chatGPTService;

    public function __construct(ChatGPTService $chatGPTService)
    {
        $this->chatGPTService = $chatGPTService;
    }

    public function handle(Request $request)
    {
        $text = $request->text;

        $responseText = $this->chatGPTService->sendMessage($text);

        return response()->json(['response' => $responseText]);
    }
}
