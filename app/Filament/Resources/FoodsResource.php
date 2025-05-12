<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FoodsResource\Pages;
use App\Filament\Resources\FoodsResource\RelationManagers;
use App\Models\Foods;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class FoodsResource extends Resource
{
    protected static ?string $model = Foods::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->directory('foods')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->columnSpanFull()
                    ->prefix('Rp')
                    ->reactive(), // Reactive untuk memicu perubahan pada field lain
                Forms\Components\Toggle::make('is_promo')
                    ->reactive(),
                Forms\Components\Select::make('percent')
                    ->options([
                        10 => '10%',
                        25 => '25%',
                        35 => '35%',
                        50 => '50%',
                    ])
                    ->columnSpanFull()
                    ->reactive() // Reactive to trigger changes on other fields
                    ->hidden(fn($get) => !$get('is_promo'))
                    ->afterStateUpdated(function ($set, $get, $state) {
                        if ($get('is_promo') && $get('price') && $get('percent')) {
                            $discount = ($get('price') * (int)$get('percent')) / 100;
                            $set('price_afterdiscount', $get('price') - $discount);
                        } else {
                            $set('price_afterdiscount', $get('price'));
                        }
                    }), // Only active if is_promo is true

                Forms\Components\TextInput::make('price_afterdiscount')
                    ->numeric()
                    ->prefix('Rp')
                    ->readOnly()
                    ->columnSpanFull()
                    ->hidden(fn($get) => !$get('is_promo')), // Only visible if is_promo is true
                Forms\Components\Select::make('categories_id')
                    ->required()
                    ->columnSpanFull()
                    ->relationship('categories', 'name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_afterdiscount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('percent')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_promo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('categories.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFoods::route('/'),
            'create' => Pages\CreateFoods::route('/create'),
            'edit' => Pages\EditFoods::route('/{record}/edit'),
        ];
    }
}
