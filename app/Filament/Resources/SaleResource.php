<?php

    namespace App\Filament\Resources;

    use App\Filament\Resources\SaleResource\Pages;
    use App\Models\Sale;
    use App\Models\Menu;
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

    class SaleResource extends Resource
    {
        protected static ?string $model = Sale::class;

        protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

        protected static ?string $navigationGroup = 'Sales Management';

        protected static ?int $navigationSort = 2;

        protected static ?string $navigationLabel = 'Sales Transactions';

        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    Forms\Components\Section::make('Transaction Details')
                        ->schema([
                            Forms\Components\Select::make('menu_id')
                                ->label('Menu Item')
                                ->options(Menu::all()->pluck('name', 'id'))
                                ->required()
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $menu = Menu::find($state);
                                    if ($menu) {
                                        $set('unit_price', $menu->price);
                                    }
                                })
                                ->columnSpan(2),

                            Forms\Components\DateTimePicker::make('transaction_date')
                                ->required()
                                ->default(now())
                                ->native(false)
                                ->displayFormat('d/m/Y H:i')
                                ->seconds(false)
                                ->columnSpan(2),

                            Forms\Components\Hidden::make('unit_price')
                                ->default(0),

                            Forms\Components\TextInput::make('quantity')
                                ->required()
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    $unitPrice = $get('unit_price') ?? 0;
                                    $set('total_price', $state * $unitPrice);
                                }),

                            Forms\Components\TextInput::make('total_price')
                                ->required()
                                ->numeric()
                                ->prefix('Rp')
                                ->disabled()
                                ->dehydrated()
                                ->placeholder('Auto calculated'),

                            Forms\Components\Select::make('payment_status')
                                ->options([
                                    'paid' => 'Paid',
                                    'pending' => 'Pending',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->required()
                                ->default('paid')
                                ->native(false),
                        ])
                        ->columns(3),
                ]);
        }

        public static function table(Table $table): Table
        {
            return $table
                ->columns([
                    Tables\Columns\TextColumn::make('id')
                        ->label('ID')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),

                    Tables\Columns\TextColumn::make('transaction_date')
                        ->dateTime('d M Y, H:i')
                        ->timezone('Asia/Makassar')
                        ->sortable()
                        ->searchable()
                        ->description(fn ($record) => $record->transaction_date->timezone('Asia/Makassar')->format('l, d F Y')),

                    Tables\Columns\TextColumn::make('menu.name')
                        ->label('Menu')
                        ->searchable()
                        ->sortable()
                        ->weight('bold'),

                    Tables\Columns\TextColumn::make('menu.category')
                        ->label('Category')
                        ->badge()
                        ->sortable(),

                    Tables\Columns\TextColumn::make('quantity')
                        ->numeric()
                        ->sortable()
                        ->suffix(' items'),

                    Tables\Columns\TextColumn::make('total_price')
                        ->money('IDR')
                        ->sortable(),

                    Tables\Columns\TextColumn::make('payment_status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'paid' => 'success',
                            'pending' => 'warning',
                            'cancelled' => 'danger',
                            default => 'gray',
                        })
                        ->sortable(),

                    Tables\Columns\TextColumn::make('status')
                        ->label('Lock Status')
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
                        ->hidden(fn ($record) => $record->isLocked()),
                    
                    Tables\Actions\DeleteAction::make()
                        ->hidden(fn ($record) => $record->isLocked()),
])
                ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\DeleteBulkAction::make(),
                    ]),
                ])
                ->defaultSort('transaction_date', 'desc');
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
            return auth()->user()->isAdmin();
        }
          public static function canEdit($record): bool
    {
        return !$record->isLocked();
    }

    public static function canDelete($record): bool
    {
        return !$record->isLocked();
    }

    public static function canCreate(): bool
    {
        return auth()->user()->isAdmin();
    }

        
    }
    