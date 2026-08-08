<?php

namespace Feraandrei1\FilamentAiChatWidget\Services;

use Feraandrei1\FilamentAiChatWidget\Mcp\Resources\KnowledgeResource;
use Feraandrei1\FilamentAiChatWidget\Mcp\Resources\RulesResource;
use Feraandrei1\FilamentAiChatWidget\Mcp\Servers\LocalServer;

class McpResourceService
{
    public static function getSystemInstructions(): array
    {
        $messages = [];

        $serverClasses = array_values(array_unique(array_merge([LocalServer::class], config('openai.mcp.servers', []))));

        foreach ($serverClasses as $serverClass) {
            if (! class_exists($serverClass)) {
                continue;
            }

            try {
                $server = new $serverClass();

                if (! empty($server->instructions)) {
                    $messages[] = [
                        'role' => 'system',
                        'content' => $server->instructions,
                    ];
                }

        foreach ($server->resources as $resourceClass) {
            if (! class_exists($resourceClass)) {
                continue;
            }

            try {
                $resourceInstance = new $resourceClass();
                if (method_exists($resourceInstance, 'read')) {
                    $readContent = $resourceInstance->read();
                    $data = is_string($readContent) ? json_decode($readContent, true) : $readContent;

                    if (is_array($data)) {
                        if (isset($data['rules']) && is_array($data['rules'])) {
                            foreach ($data['rules'] as $rule) {
                                $messages[] = ['role' => 'system', 'content' => $rule];
                            }
                        } elseif (isset($data['knowledge']) && is_array($data['knowledge'])) {
                            foreach ($data['knowledge'] as $k) {
                                if (isset($k['content'])) {
                                    $messages[] = ['role' => 'system', 'content' => $k['content']];
                                }
                            }
                        } elseif (isset($data['content'])) {
                            $messages[] = ['role' => 'system', 'content' => (string) $data['content']];
                        } else {
                            $messages[] = ['role' => 'system', 'content' => json_encode($data)];
                        }
                    } elseif (is_string($readContent) && ! empty($readContent)) {
                        $messages[] = ['role' => 'system', 'content' => $readContent];
                    }
                }
            } catch (\Throwable $e) {
                // Ignore resource errors
            }
        }
            } catch (\Throwable $e) {
                // Ignore server errors
            }
        }

        $filamentResources = config('openai.mcp.filament_resources', []);
        foreach ($filamentResources as $mcpResource) {
            if ($mcpResource instanceof \Feraandrei1\FilamentAiChatWidget\Mcp\McpResource) {
                $context = $mcpResource->generateContext();
                if (! empty($context)) {
                    $messages[] = [
                        'role' => 'system',
                        'content' => $context,
                    ];
                }
            }
        }

        return $messages;
    }

    public static function getInstructionsAsString(): string
    {
        $instructions = self::getSystemInstructions();

        return collect($instructions)
            ->map(fn($message) => $message['content'])
            ->join("\n\n");
    }

    public static function getInstructionsCount(): int
    {
        return count(self::getSystemInstructions());
    }
}
