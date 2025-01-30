<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    public function streamResponse(Request $request)
    {
        try {
            $thread = $request->input('thread', []);
            $system = $request->input('system', []);
            $query = $request->input('query', '');
            $context = $request->input('context', '');

            if (empty($query)) {
                return response()->json(['error' => 'Query is required'], 400);
            }

            $apiKey = env('VITE_OPENAI_API_KEY');
            if (empty($apiKey)) {
                Log::error('OpenAI API key is not configured');
                return response()->json(['error' => 'OpenAI API key is not configured'], 400);
            }

            // Build conversation history
            $conversation = collect($thread)->flatMap(function ($event) {
                if (isset($event['response'])) {
                    return [
                        ['role' => 'user', 'content' => $event['request']['query']],
                        ['role' => 'assistant', 'content' => $event['response']['data']]
                    ];
                }
                return [];
            })->all();

            // Add system messages
            $pluginSystemMessages = collect($system)->map(function ($content) {
                return ['role' => 'system', 'content' => $content];
            })->all();

            $systemMessages = array_merge(
                $pluginSystemMessages,
                [['role' => 'system', 'content' => 'You are a helpful AI assistant integrated into TinyMCE editor.']]
            );

            // Form the content
            $content = empty($context) || !empty($conversation)
                ? $query
                : "Question: {$query}\nContext: \"\"\"{$context}\"\"\"";

            // Build final messages array
            $messages = array_merge(
                $systemMessages,
                $conversation,
                [['role' => 'user', 'content' => $content]]
            );

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4',
                    'temperature' => 0.7,
                    'max_tokens' => 800,
                    'messages' => $messages,
                ]);

                if (!$response->successful()) {
                    $error = $response->json();
                    Log::error('OpenAI API error', $error);
                    return response()->json([
                        'error' => $error['error']['message'] ?? 'OpenAI API error'
                    ], $response->status());
                }

                $data = $response->json();
                return response()->json([
                    'content' => $data['choices'][0]['message']['content']
                ]);

            } catch (\Exception $e) {
                Log::error('OpenAI request error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'error' => 'Error communicating with OpenAI: ' . $e->getMessage()
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Controller error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}
