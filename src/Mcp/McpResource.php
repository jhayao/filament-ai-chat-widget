<?php

namespace Feraandrei1\FilamentAiChatWidget\Mcp;

class McpResource
{
    protected string $resourceClass;

    protected string $mode = 'readOnly';

    public function __construct(string $resourceClass)
    {
        $this->resourceClass = $resourceClass;
    }

    public static function make(string $resourceClass): static
    {
        return new static($resourceClass);
    }

    public function readOnly(): static
    {
        $this->mode = 'readOnly';

        return $this;
    }

    public function crud(): static
    {
        $this->mode = 'crud';

        return $this;
    }

    public function getResourceClass(): string
    {
        return $this->resourceClass;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function isCrud(): bool
    {
        return $this->mode === 'crud';
    }

    public function isReadOnly(): bool
    {
        return $this->mode === 'readOnly';
    }

    public function generateContext(): string
    {
        $resourceClass = $this->resourceClass;

        if (! class_exists($resourceClass)) {
            return "Resource: {$resourceClass} (Mode: {$this->mode})";
        }

        $modelLabel = method_exists($resourceClass, 'getModelLabel')
            ? $resourceClass::getModelLabel()
            : class_basename($resourceClass);

        $pluralLabel = method_exists($resourceClass, 'getPluralModelLabel')
            ? $resourceClass::getPluralModelLabel()
            : $modelLabel;

        $modelClass = method_exists($resourceClass, 'getModel')
            ? $resourceClass::getModel()
            : null;

        $modeDescription = $this->isCrud()
            ? "Full CRUD access (create, view, update, delete)"
            : "Read-only access (view and search only)";

        $context = "FILAMENT RESOURCE CONTEXT:\n";
        $context .= "- Resource: {$pluralLabel} ({$resourceClass})\n";
        if ($modelClass) {
            $context .= "- Eloquent Model: {$modelClass}\n";
        }
        $context .= "- Access Permission Mode: {$modeDescription}\n";
        $context .= "- Instructions: Assist the user with questions, navigation, and actions related to {$pluralLabel} according to the {$modeDescription} mode.";

        return $context;
    }
}
