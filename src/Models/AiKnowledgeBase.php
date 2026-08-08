<?php

namespace Feraandrei1\FilamentAiChatWidget\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiKnowledgeBase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'tenant_id',
        'name',
        'content',
        'active',
        'order_column',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order_column' => 'integer',
    ];

    public function company(): BelongsTo
    {
        $tenantModel = config('filament-ai-chat-widget.tenant_model')
            ?? config('filament.multi_tenancy.tenant_model')
            ?? 'App\\Models\\Company';

        return $this->belongsTo($tenantModel, 'company_id');
    }

    public function tenant(): BelongsTo
    {
        $tenantModel = config('filament-ai-chat-widget.tenant_model')
            ?? config('filament.multi_tenancy.tenant_model')
            ?? 'App\\Models\\Company';

        return $this->belongsTo($tenantModel, 'tenant_id');
    }

    public static function getActiveKnowledgeBases(): array
    {
        return self::where('active', true)
            ->orderBy('order_column')
            ->get()
            ->map(fn($aiKnowledgeBase) => [
                'role' => 'system',
                'content' => $aiKnowledgeBase->content,
            ])
            ->toArray();
    }
}
