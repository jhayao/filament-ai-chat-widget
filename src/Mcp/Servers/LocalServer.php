<?php

namespace Feraandrei1\FilamentAiChatWidget\Mcp\Servers;

use Feraandrei1\FilamentAiChatWidget\Mcp\Resources;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Contracts\Transport;

class LocalServer extends Server
{
    public string $serverName = 'local';

    public string $serverVersion = '0.0.1';

    public string $instructions;

    public array $tools = [];

    public array $resources = [
        Resources\RulesResource::class,
        Resources\KnowledgeResource::class,
    ];

    public array $prompts = [];

    public function __construct(?Transport $transport = null)
    {
        $appName = config('app.name');

        $this->instructions = <<<MARKDOWN
            You are an AI assistant exclusively for the {$appName} application.
            Your role is to help users understand and use this application effectively.
            Be warm and professional with greetings and pleasantries, but guide all conversations toward helping with the application.
            Politely decline requests unrelated to this application. Maintain confidentiality and prioritize user privacy.
        MARKDOWN;

        $customResources = config('openai.mcp.resources', []);
        if (! empty($customResources) && is_array($customResources)) {
            $this->resources = array_values(array_unique(array_merge($this->resources, $customResources)));
        }

        $customTools = config('openai.mcp.tools', []);
        if (! empty($customTools) && is_array($customTools)) {
            $this->tools = array_values(array_unique(array_merge($this->tools, $customTools)));
        }

        $customPrompts = config('openai.mcp.prompts', []);
        if (! empty($customPrompts) && is_array($customPrompts)) {
            $this->prompts = array_values(array_unique(array_merge($this->prompts, $customPrompts)));
        }

        if ($transport) {
            parent::__construct($transport);
        }
    }
}
