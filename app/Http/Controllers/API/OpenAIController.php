<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatGptRequest;
use Illuminate\Support\Facades\Http;

class OpenAIController extends Controller
{
    public function chat(ChatGptRequest $request)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . config('openai.chat_gpt_key'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $request->content
                ]
            ],
            "temperature" => 0,
            "max_tokens" => 2048
        ])->body();

        return response()->json(json_decode($response));
    }
}
