<?php

namespace Feraandrei1\FilamentAiChatWidget\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiConversation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'tenant_id',
        'messages',

        'model',
        'temperature',
        'max_tokens',

        'tokens_used',
    ];

    protected $casts = [
        'messages' => 'array',
        'temperature' => 'decimal:2',
        'max_tokens' => 'integer',
        'tokens_used' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

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
}
