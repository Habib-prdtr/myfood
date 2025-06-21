<?php

namespace App\Filament\Resources;

use Log;
use Filament\Forms;
use App\Models\User;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Exceptions\Halt;
use App\Models\TransactionItems;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Route;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionItemsResource\Pages\EditTransactionItems;
use App\Filament\Resources\TransactionItemsResource\Pages\ListTransactionItems;
use App\Filament\Resources\TransactionItemsResource\Pages\CreateTransactionItems;



class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function getRecordTitle(?Model $record): string|null|Htmlable
    {
        return $record->name;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canViewAny(): bool
{
    return Auth::user()?->hasRole('admin') || Auth::user()?->hasRole('koki') || Auth::user()?->hasRole('pramusaji');
}

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('external_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('checkout_link')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('barcodes_id')
                    ->label('QR Code')
                    ->image() // Hanya menerima file gambar
                    ->directory('qr_code') // Direktori penyimpanan
                    ->disk('public') // Disk penyimpanan
                    ->default(function ($record) {
                        return $record->barcodes->image ?? null;
                    }),
                Forms\Components\TextInput::make('payment_method')
                    ->required(),
                Forms\Components\TextInput::make('payment_status')
                    ->required(),
                Forms\Components\TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('ppn')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('status_pesanan')
                    ->options([
                        'menunggu' => 'Menunggu',
                        'diproses' => 'Diproses',
                        'diantar' => 'Sudah Diantar',
                    ])
                    ->required()
                    ->label('Status Pesanan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Transaction Code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Customer Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('barcodes.table_number')
                    ->label('Nomor Meja'),
                Tables\Columns\TextColumn::make('payment_method')
                ->visible(fn () => Auth::user()?->hasRole('admin'))
                    ->label('Payment Method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                ->visible(fn () => Auth::user()?->hasRole('admin'))
                    ->label('Payment Status')
                    ->badge()
                    ->colors([
                        'success' => fn ($state): bool => in_array($state, ['SUCCESS', 'PAID', 'SETTLED']),
                        'warning' => fn ($state): bool => $state === 'PENDING',
                        'danger' => fn ($state): bool => in_array($state, ['FAILED', 'EXPIRED']),
                    ]),
                Tables\Columns\TextColumn::make('subtotal')
                ->visible(fn () => Auth::user()?->hasRole('admin'))
                    ->label('Subtotal')
                    ->numeric()
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('ppn')
                ->visible(fn () => Auth::user()?->hasRole('admin'))
                    ->label('PPN')
                    ->numeric()
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('total')
                ->visible(fn () => Auth::user()?->hasRole('admin'))
                    ->label('Total')
                    ->numeric()
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('status_pesanan')
                    ->label('Status Pesanan')
                    ->badge()
                    ->colors([
                        'gray' => 'menunggu',
                        'warning' => 'diproses',
                        'success' => 'diantar',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(), // ini boleh kamu hapus karena `canEdit` false
                Action::make('See transaction')
                    ->color('success')
                    ->url(
                        fn (Transaction $record): string => static::getUrl('transaction-items.index', [
                            'parent' => $record->id,
                        ])
                    ),
                Action::make('ubahStatus')
                ->visible(fn () => Auth::user()?->hasRole('koki') || Auth::user()?->hasRole('pramusaji'))
                ->label('Ubah Status')
                ->form(fn (Transaction $record) => [
                    Select::make('status_pesanan')
                ->label('Status Pesanan')
                ->options(function ($record) {
                    $user = Auth::user();
                    $currentStatus = $record->status_pesanan;

                    if ($user->hasRole('koki')) {
                        return [
                            'menunggu' => 'Menunggu',
                            'diproses' => 'Diproses',
                        ];
                    }

                    if ($user->hasRole('pramusaji')) {
                        $options = [];

                        // Tetap tampilkan status saat ini agar terlihat
                        if ($currentStatus === 'diproses') {
                            $options['diproses'] = 'Diproses';
                        }

                        // Pramusaji hanya bisa ubah ke "diantar"
                        $options['diantar'] = 'Sudah Diantar';

                        return $options;
                    }

                    // Fallback jika admin
                    return [
                        'menunggu' => 'Menunggu',
                        'diproses' => 'Diproses',
                        'diantar' => 'Sudah Diantar',
                    ];
                })
                ->default(fn ($record) => $record->status_pesanan)
                ->dehydrated(true)
                ->required(),
                ])
                ->action(function (array $data, $record): void {
                    // Validasi: tidak boleh langsung ke 'diantar' dari 'menunggu'
                    if ($data['status_pesanan'] === 'diantar' && $record->status_pesanan !== 'diproses') {
                        Notification::make()
                            ->title('Gagal mengubah status')
                            ->body('Pesanan hanya bisa diubah menjadi "Sudah Diantar" jika status sebelumnya adalah "Diproses".')
                            ->danger()
                            ->send();

                        return; // pastikan proses update tidak lanjut
                    }
                    // Jika lolos validasi, update status
                    $record->update([
                        'status_pesanan' => $data['status_pesanan'],
                    ]);
                })
                ->icon('heroicon-o-pencil')
                ->color('primary')

            ])
            ->bulkActions([]);
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),

            'transaction-items.index' => ListTransactionItems::route('/{parent}/transaction'),
        ];
    }
}
