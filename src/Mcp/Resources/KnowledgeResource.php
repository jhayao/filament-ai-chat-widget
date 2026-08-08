<?php

namespace Feraandrei1\FilamentAiChatWidget\Mcp\Resources;

use Feraandrei1\FilamentAiChatWidget\Models\AiKnowledgeBase;
use Filament\Facades\Filament;
use Laravel\Mcp\Server\Resource;

class KnowledgeResource extends Resource
{
    protected string $description = 'AI knowledge base retrieved from the ai_knowledge_bases table';

    public function read(): string
    {
        $query = AiKnowledgeBase::where('active', true);

        if (class_exists(Filament::class) && Filament::hasTenancy() && Filament::getTenant()) {
            $tenantId = Filament::getTenant()->getKey();
            $query->where(function ($q) use ($tenantId) {
                $q->where('company_id', $tenantId)
                  ->orWhere('tenant_id', $tenantId)
                  ->orWhereNull('company_id');
            });
        }

        $aiKnowledgeBases = $query->orderBy('order_column')->get();

        $knowledge = $aiKnowledgeBases->map(function ($aiKnowledgeBase) {
            return [
                'id' => $aiKnowledgeBase->id,
                'name' => $aiKnowledgeBase->name,
                'content' => $aiKnowledgeBase->content,
                'order' => $aiKnowledgeBase->order_column,
            ];
        })->toArray();

        return json_encode([
            'type' => 'knowledge',
            'knowledge' => $knowledge,
            'count' => count($knowledge),
        ], JSON_PRETTY_PRINT);
    }
}
