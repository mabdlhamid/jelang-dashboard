<?php

    namespace App\Filament\Resources;

    use App\Filament\Resources\SaleResource\Pages;
    use App\Models\Sale;
    use App\Models\Menu;
    use App\Models\User;
    use Filament\Forms;
    use Filament\Forms\Form;
    use Filament\Resources\Resource;
    use Filament\Tables;
    use Filament\Tables\Table;
    use Illuminate\Database\Eloquent\Builder;
    use Maatwebsite\Excel\Facades\Excel;
    use App\Exports\SalesExport;
    use Barryvdh\DomPDF\Facade\Pdf;
    use Filament\Tables\Actions\Action;
    use Filament\Forms\Components\DatePicker;
    use App\Models\DailyClosing;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Database\Eloquent\Model;

    class SaleResource extends Resource
    {
        protected static ?string $model = Sale::class;

        protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

        protected static ?string $navigationGroup = 'Manajemen Penjualan';
        protected static ?string $modelLabel = 'Transaksi';

        protected static ?int $navigationSort = 2;
         protected static ?string $pluralModelLabel = 'Transaksi Penjualan';

        protected static ?string $navigationLabel = 'Transaksi Penjualan';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    Forms\Components\Section::make('Informasi Transaksi')
                    ->description('Input data transaksi penjualan')
                        ->schema([
                            Forms\Components\Select::make('menu_id')
                                ->label('Menu Item')
                                ->options(fn () => Menu::pluck('name', 'id'))                                
                                ->required()
                                ->searchable()
                                ->preload(false)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $menu = Menu::find($state);
                                    if ($menu) {
                                        $set('unit_price', $menu->price);
                                    }
                                })
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('quantity')
                                ->label('Jumlah')
                                ->required()
                                ->numeric()
                                ->default(0)
                                ->minValue(1)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $unitPrice = $get('unit_price') ?? 0;
                                    $set('total_price', $state * $unitPrice);
                                }),
                            
                            Forms\Components\TextInput::make('unit_price')
                                ->label('Harga Satuan')
                                ->prefix('Rp')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(false),


                            Forms\Components\TextInput::make('total_price')
                                ->label('Total Harga')
                                ->prefix('Rp')
                                ->numeric()
                                ->required()
                                ->disabled()
                                ->dehydrated()
                                ->placeholder('Auto calculated'),

                            Forms\Components\Select::make('payment_status')
                                ->options([
                                    'paid' => 'Lunas',
                                    'pending' => 'Pending',
                                ])
                                ->default('paid')
                                ->required()
                                ->native(false),

                            Forms\Components\Select::make('payment_method')
                                ->label('Metode Pembayaran')
                                ->options([
                                    'cash' => '💵 Tunai',
                                    'qris' => '📱 QRIS',
                                    'debit' => '💳 Kartu Debit',
                                    'credit' => '💳 Kartu Kredit',
                                    'e-wallet' => '📲 E-Wallet (GoPay/OVO/Dana)',
                                    'transfer' => '🏦 Transfer Bank',
                                ])
                                ->default('cash')
                                ->required()
                                ->native(false),

                            Forms\Components\DateTimePicker::make('transaction_date')
                            ->label('Tanggal & Waktu Transaksi')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->timezone('Asia/Makassar'),
                        ])
                        ->columns(3),
                ]);
        }

        public static function table(Table $table): Table
        {
            return $table
                ->query(fn () => Sale::query()->with('menu')) // 🔥 TARUH DI SINI
                ->columns([
                    Tables\Columns\TextColumn::make('id')
                        ->label('ID')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),

                    Tables\Columns\TextColumn::make('transaction_date')
                        ->label('Tanggal & Waktu')
                        ->dateTime('d M Y, H:i')
                        ->timezone('Asia/Makassar')
                        ->sortable(),

                    Tables\Columns\TextColumn::make('menu.name')
                        ->label('Menu')
                        ->searchable()
                        ->sortable()
                        ->weight('bold'),

                    Tables\Columns\TextColumn::make('menu.category')
                        ->label('Kategori')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'Snack' => 'warning',
                            'Food' => 'info',
                            'Rice Bowl' => 'rose',
                            'Coffee' => 'success',
                            'Non-Coffee' => 'danger',
                            'Fresh' => 'primary',
                            'Manual Brew' => 'purple',
                            'Dessert' => 'info',
                            'Tea' => 'warning',
                            default => 'default',
                        })
                        ->sortable(),

                    Tables\Columns\TextColumn::make('quantity')
                        ->label('Jumlah')
                        ->numeric()
                        ->sortable()
                        ->alignCenter()
                        ->suffix(' items'),

                    Tables\Columns\TextColumn::make('total_price')
                         ->label('Total Harga')
                        ->money('IDR')
                        ->sortable(),

                    Tables\Columns\TextColumn::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->alignCenter()
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'cash' => '💵 Tunai',
                            'qris' => '📱 QRIS',
                            'debit' => '💳 Debit',
                            'credit' => '💳 Kredit',
                            'e-wallet' => '📲 E-Wallet',
                            'transfer' => '🏦 Transfer',
                            default => $state,
                        })
                      ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'qris' => 'info',
                        'debit', 'credit' => 'warning',
                        'e-wallet' => 'primary',
                        'transfer' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                    Tables\Columns\TextColumn::make('payment_status')
                        ->badge()
                        ->label('Status Pembayaran')
                        ->alignCenter()
                          ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => '✅ Lunas',
                        'pending' => '⏳ Pending',
                        default => $state,
                          })
                        ->color(fn (string $state): string => match ($state) {
                            'paid' => 'success',
                            'pending' => 'warning',
                            'cancelled' => 'danger',
                            default => 'gray',
                        })
                        ->sortable(),

                    Tables\Columns\TextColumn::make('status')
                        ->label('Status Lock')
                        ->badge()
                        ->getStateUsing(fn ($record) => $record->isLocked() ? 'Locked' : 'Active')
                        ->color(fn ($record) => $record->isLocked() ? 'danger' : 'success')
                        ->icon(fn ($record) => $record->isLocked() ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open'), 

                    Tables\Columns\TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                ])

                ->filters([
                    Tables\Filters\SelectFilter::make('payment_status')
                        ->label('Status Pembayaran')
                        ->options([
                            'paid' => 'Paid',
                            'pending' => 'Pending',
                            'cancelled' => 'Cancelled',
                        ]),

                    Tables\Filters\SelectFilter::make('menu_id')
                        ->label('Menu')
                        ->options(Menu::all()->pluck('name', 'id'))
                        ->searchable(),

                    Tables\Filters\Filter::make('transaction_date')
                        ->label('Tanggal Transaksi')
                        ->form([
                            Forms\Components\DatePicker::make('from')
                                ->label('From Date'),
                            Forms\Components\DatePicker::make('until')
                                ->label('Until Date'),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when(
                                    $data['from'],
                                    fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                                )
                                ->when(
                                    $data['until'],
                                    fn (Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                                );
                        }),
                ])

            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn (Sale $record): bool => self::userIsOwner() && ! $record->isLocked()),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Sale $record): bool => self::userIsOwner() && ! $record->isLocked()),
])

        ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\DeleteBulkAction::make()
                            ->visible(fn (): bool => self::userIsOwner()),
                    ]),
                ])
                ->defaultSort('transaction_date', 'desc')
                ->paginated([10, 25, 50]); // 🔥 TARUH DI SINI
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
                'index' => Pages\ListSales::route('/'),
                'create' => Pages\CreateSale::route('/create'),
                'edit' => Pages\EditSale::route('/{record}/edit'),
            ];
        }

        // Only Admin can access this resource
        public static function canViewAny(): bool
{
    return self::userIsOwnerOrAdmin();
}

public static function canCreate(): bool
{
    return self::userIsOwnerOrAdmin();
}

public static function canEdit($record): bool
{
    return self::userIsOwner() && ! $record->isLocked();
}

public static function canDelete($record): bool
{
    return self::userIsOwner() && ! $record->isLocked();
}

private static function userIsOwner(): bool
{
    $user = Auth::user();

    return $user instanceof User && $user->isOwner();
}

private static function userIsOwnerOrAdmin(): bool
{
    $user = Auth::user();

    return $user instanceof User && ($user->isOwner() || $user->isAdmin());
}

}