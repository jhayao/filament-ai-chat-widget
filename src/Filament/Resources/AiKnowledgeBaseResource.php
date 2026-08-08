<?php

namespace Feraandrei1\FilamentAiChatWidget\Filament\Resources;

use BackedEnum;
use Feraandrei1\FilamentAiChatWidget\Filament\Resources\AiKnowledgeBaseResource\Pages;
use Feraandrei1\FilamentAiChatWidget\Models\AiKnowledgeBase;

use Illuminate\Database\Eloquent\Builder;

use Filament\Actions;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AiKnowledgeBaseResource extends Resource
{
    protected static ?string $model = AiKnowledgeBase::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static bool $isScopedToTenant = true;

    protected static function orderByOrderColumn(Builder $query): Builder
    {
        return $query->orderBy('order_column');
    }

    public static function form(Schema $schema): Schema
    {
        $groupClass = class_exists(\Filament\Schemas\Components\Group::class)
            ? \Filament\Schemas\Components\Group::class
            : (class_exists(\Filament\Forms\Components\Group::class) ? \Filament\Forms\Components\Group::class : 'Group');

        $sectionClass = class_exists(\Filament\Schemas\Components\Section::class)
            ? \Filament\Schemas\Components\Section::class
            : (class_exists(\Filament\Forms\Components\Section::class) ? \Filament\Forms\Components\Section::class : 'Section');

        $textInputClass = class_exists(\Filament\Forms\Components\TextInput::class)
            ? \Filament\Forms\Components\TextInput::class
            : (class_exists(\Filament\Schemas\Components\TextInput::class) ? \Filament\Schemas\Components\TextInput::class : 'TextInput');

        $textareaClass = class_exists(\Filament\Forms\Components\Textarea::class)
            ? \Filament\Forms\Components\Textarea::class
            : (class_exists(\Filament\Schemas\Components\Textarea::class) ? \Filament\Schemas\Components\Textarea::class : 'Textarea');

        $toggleClass = class_exists(\Filament\Forms\Components\Toggle::class)
            ? \Filament\Forms\Components\Toggle::class
            : (class_exists(\Filament\Schemas\Components\Toggle::class) ? \Filament\Schemas\Components\Toggle::class : 'Toggle');

        return $schema
            ->schema([
                $groupClass::make()
                    ->schema([
                        $sectionClass::make()
                            ->schema([
                                $textInputClass::make('name')
                                    ->required()
                                    ->maxLength(255),

                                $textareaClass::make('content')
                                    ->required()
                                    ->rows(8)
                                    ->columnSpanFull()
                                    ->helperText('Knowledge that will be sent to the AI assistant'),

                                $toggleClass::make('active')
                                    ->required()
                                    ->default(true)
                                    ->helperText('Only active knowledge are used by the AI'),
                            ])->columns(2),
                    ])->columnSpan(1),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                static::orderByOrderColumn(AiKnowledgeBase::query())
            )
            ->reorderable('order_column')
            ->recordTitleAttribute('order_column')
            ->columns([

                Tables\Columns\TextColumn::make('order_column')
                    ->translateLabel()
                    ->label('Order'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\TernaryFilter::make('active')
                    ->label('Active')
                    ->boolean()
                    ->trueLabel('Only active')
                    ->falseLabel('Only inactive')
                    ->native(false),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAiKnowledgeBases::route('/'),
            'create' => Pages\CreateAiKnowledgeBase::route('/create'),
            'edit' => Pages\EditAiKnowledgeBase::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('AI knowledge base');
    }

    public static function getPluralModelLabel(): string
    {
        return __('AI knowledge bases');
    }
}
