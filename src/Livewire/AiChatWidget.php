<?php

namespace Feraandrei1\FilamentAiChatWidget\Livewire;

use Feraandrei1\FilamentAiChatWidget\Models\AiConversation;
use Feraandrei1\FilamentAiChatWidget\Models\AiKnowledgeBase;
use Feraandrei1\FilamentAiChatWidget\Services\McpResourceService;
use Livewire\Component;
use Filament\Notifications\Notification;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use OpenAI\Laravel\Facades\OpenAI;

class AiChatWidget extends Component
{
    public $message = '';
    public $chatHistory = [];
    public $conversationId = null;

    public function mount()
    {
        $query = AiConversation::where('user_id', Auth::id());

        if (class_exists(\Filament\Facades\Filament::class) && \Filament\Facades\Filament::hasTenancy() && \Filament\Facades\Filament::getTenant()) {
            $tenantId = \Filament\Facades\Filament::getTenant()->getKey();
            $query->where(function ($q) use ($tenantId) {
                $q->where('company_id', $tenantId)
                  ->orWhere('tenant_id', $tenantId);
            });
        }

        $conversation = $query->latest()->first();

        if ($conversation) {
            $this->conversationId = $conversation->id;
            $this->chatHistory = $conversation->messages;
        }
    }

    public function getAiResponse($message)
    {
        $validator = Validator::make(
            ['message' => $message],
            ['message' => 'required|string|max:256']
        );

        if ($validator->fails()) {

            $errorsMessages = $validator->messages()->all();

            foreach ($errorsMessages as $errorsMessage) {
                Notification::make()
                    ->title($errorsMessage)
                    ->danger()
                    ->send();
            }

            return ['success' => false];
        }

        if (! $this->conversationId) {
            $tenantId = (class_exists(\Filament\Facades\Filament::class) && \Filament\Facades\Filament::hasTenancy() && \Filament\Facades\Filament::getTenant())
                ? \Filament\Facades\Filament::getTenant()->getKey()
                : null;

            $conversation = AiConversation::create([
                'user_id' => Auth::id(),
                'company_id' => $tenantId,
                'tenant_id' => $tenantId,
                'messages' => [],
                'model' => config('openai.model', env('OPENAI_MODEL', 'gpt-4o-mini')),
                'temperature' => 0.5,
                'max_tokens' => 500,
            ]);

            $this->conversationId = $conversation->id;
        } else {
            $conversation = AiConversation::find($this->conversationId);
        }

        $systemInstructions = McpResourceService::getSystemInstructions();

        $messagesToSend = array_merge(
            $systemInstructions,
            $this->chatHistory
        );

        try {
            $response = OpenAI::chat()->create([
                'model' => $conversation->model,
                'messages' => $messagesToSend,
                'max_tokens' => 500,
                'temperature' => (float) $conversation->temperature,
            ]);

            $reply = $response->choices[0]->message->content;
            $tokensUsed = $response->usage->totalTokens ?? 0;

            $this->chatHistory[] = ['role' => 'assistant', 'content' => $reply];

            $conversation->update([
                'messages' => $this->chatHistory,
                'tokens_used' => $conversation->tokens_used + $tokensUsed,
            ]);

            return ['success' => true];
        } catch (\Exception $e) {

            $this->chatHistory[] = [
                'role' => 'assistant',
                'content' => 'Sorry, I encountered an error. Please try again later.'
            ];

            $conversation->update([
                'messages' => $this->chatHistory,
            ]);

            Log::error('AI Chat Error: ' . $e->getMessage());

            return ['success' => true];
        }
    }

    public function render()
    {
        return view('filament-ai-chat-widget::livewire.ai-chat-widget');
    }
}
