<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Product;
use App\Helpers\DiscountCalculator;
use App\Helpers\BonusCalculator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Transactions';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ── PANDUAN LANGKAH DEMI LANGKAH ───────────────────────────────────
                Forms\Components\Placeholder::make('panduan_pengisian')
                    ->label('')
                    ->columnSpanFull()
                    ->content(new HtmlString("
                        <div style='background:linear-gradient(135deg,#eff6ff 0%,#f0fdf4 100%);border:1px solid #bfdbfe;border-radius:14px;padding:18px 22px;margin-bottom:8px;'>
                            <div style='font-size:1rem;font-weight:700;color:#1e40af;margin-bottom:12px;display:flex;align-items:center;gap:8px;'>
                                <svg style='width:20px;height:20px;flex-shrink:0' fill='currentColor' viewBox='0 0 24 24'><path d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z'/></svg>
                                Panduan Mengisi Form Transaksi Bon
                            </div>
                            <div style='display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:10px;'>
                                <div style='background:white;border-radius:10px;padding:10px 14px;border:1px solid #dbeafe;display:flex;align-items:center;gap:10px;'>
                                    <span style='font-size:1.6rem;'>①</span>
                                    <div><div style='font-weight:700;color:#1e40af;font-size:0.85rem;'>Informasi Bon</div><div style='color:#64748b;font-size:0.75rem;'>Isi nomor bon &amp; pilih pelanggan</div></div>
                                </div>
                                <div style='background:white;border-radius:10px;padding:10px 14px;border:1px solid #d1fae5;display:flex;align-items:center;gap:10px;'>
                                    <span style='font-size:1.6rem;'>②</span>
                                    <div><div style='font-weight:700;color:#065f46;font-size:0.85rem;'>Daftar Produk</div><div style='color:#64748b;font-size:0.75rem;'>Tambah produk &amp; jumlah barang</div></div>
                                </div>
                                <div style='background:white;border-radius:10px;padding:10px 14px;border:1px solid #e0e7ff;display:flex;align-items:center;gap:10px;'>
                                    <span style='font-size:1.6rem;'>③</span>
                                    <div><div style='font-weight:700;color:#4338ca;font-size:0.85rem;'>Cek Total &amp; Simpan</div><div style='color:#64748b;font-size:0.75rem;'>Periksa total lalu klik Simpan</div></div>
                                </div>
                            </div>
                        </div>
                    ")),

                Forms\Components\Grid::make(3)
                    ->schema([
                        // Left block: General Information
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Section::make('Informasi Umum Transaksi')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->schema([
                                        TextInput::make('nomor_bon')
                                            ->label('Nomor Bon / Faktur')
                                            ->helperText('Nomor unik untuk melacak bon ini.')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->default(fn () => 'BON-' . date('Ymd-His'))
                                            ->placeholder('Contoh: BON-20231027-001'),

                                        Select::make('customer_id')
                                            ->label('Pilih Pelanggan')
                                            ->relationship('customer', 'name')
                                            ->helperText('Pelanggan menentukan skema diskon yang berlaku.')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateAllLines($get, $set)),

                                        DatePicker::make('tanggal')
                                            ->label('Tanggal Transaksi')
                                            ->helperText('Tanggal saat bon ini diterbitkan.')
                                            ->default(now())
                                            ->required(),

                                        Select::make('status')
                                            ->label('Status Pembayaran')
                                            ->options([
                                                'Piutang' => 'Piutang (Belum Lunas)',
                                                'Lunas' => 'Lunas (Sudah Dibayar)',
                                            ])
                                            ->default('Piutang')
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                if ($state === 'Lunas') {
                                                    $set('tanggal_pelunasan', now()->toDateString());
                                                } else {
                                                    $set('tanggal_pelunasan', null);
                                                }
                                                self::updateAllLines($get, $set);
                                            }),

                                        DatePicker::make('tanggal_pelunasan')
                                            ->label('Tanggal Pelunasan')
                                            ->helperText('Kapan tagihan ini dibayar penuh oleh pelanggan.')
                                            ->visible(fn (Get $get) => $get('status') === 'Lunas')
                                            ->required(fn (Get $get) => $get('status') === 'Lunas')
                                            ->default(now()),
                                    ])->columns(1),
                            ])->columnSpan(1),

                        // Middle/Right block: Items & Totals
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Section::make('Pengaturan Bonus & Pengiriman')
                                    ->icon('heroicon-o-truck')
                                    ->schema([
                                        Toggle::make('is_bonus')
                                            ->label('Transaksi Bonus (Produk Gratis)')
                                            ->helperText('Aktifkan jika produk ini dikategorikan sebagai bonus/free items.')
                                            ->default(false)
                                            ->live()
                                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateAllLines($get, $set)),

                                        TextInput::make('bonuses_claimed')
                                            ->label('Jumlah Bonus Diklaim')
                                            ->helperText('Masukkan total kuota bonus yang ditukar oleh pelanggan.')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->visible(fn (Get $get) => (bool) $get('is_bonus'))
                                            ->required(fn (Get $get) => (bool) $get('is_bonus')),

                                        TextInput::make('ongkir')
                                            ->label('Biaya Pengiriman (Ongkir)')
                                            ->helperText('Masukkan biaya kirim tambahan jika ada.')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->live()
                                            ->minValue(0),
                                    ])->columns(2),

                                // Customer Bonus progress display on select
                                Placeholder::make('customer_bonus_progress')
                                    ->label('Status Progres Bonus Pelanggan')
                                    ->visible(fn (Get $get) => filled($get('customer_id')))
                                    ->content(function (Get $get) {
                                        $customerId = $get('customer_id');
                                        $customer = Customer::find($customerId);
                                        if (!$customer) return '';
                                        
                                        $stats = BonusCalculator::getStats($customer);
                                        $available = $stats['bonuses_available'];
                                        $progress = $stats['progress_percentage'];
                                        $carryOver = $stats['carry_over_omzet'];
                                        $threshold = $stats['threshold'];
                                        
                                        $badgeStyle = $available > 0
                                            ? 'background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;padding:5px 14px;border-radius:20px;font-weight:700;font-size:0.85rem;'
                                            : 'background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:5px 14px;border-radius:20px;font-weight:700;font-size:0.85rem;';

                                        $statusText = $available > 0
                                            ? "✅ {$available} Bonus Tersedia — Pelanggan berhak barang gratis!"
                                            : "❌ Belum Ada Bonus — Target omzet belum tercapai.";

                                        $barColor = $available > 0 ? '#10b981' : '#6366f1';

                                        return new HtmlString("
                                            <div style='background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;'>
                                                <div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;'>
                                                    <span style='font-size:0.9rem;font-weight:700;color:#334155;'>Status Bonus Pelanggan:</span>
                                                    <span style='{$badgeStyle}'>{$statusText}</span>
                                                </div>
                                                <div>
                                                    <div style='display:flex;justify-content:space-between;font-size:0.82rem;color:#64748b;margin-bottom:6px;'>
                                                        <span>Progress ke bonus berikutnya:</span>
                                                        <span style='font-weight:600;'>Rp " . number_format($carryOver, 0, ',', '.') . " / Rp " . number_format($threshold, 0, ',', '.') . "</span>
                                                    </div>
                                                    <div style='width:100%;background:#e2e8f0;border-radius:999px;height:14px;overflow:hidden;'>
                                                        <div style='background:{$barColor};height:14px;border-radius:999px;width:{$progress}%;transition:width 0.5s ease;'></div>
                                                    </div>
                                                    <div style='font-size:0.75rem;color:#94a3b8;margin-top:4px;'>{$progress}% dari Bonus Eligibility Threshold</div>
                                                </div>
                                            </div>
                                        ");
                                    }),
                            ])->columnSpan(2),
                    ]),

                Forms\Components\Section::make('Daftar Item Transaksi')
                    ->description('Masukkan produk yang dibeli. Harga akan otomatis disesuaikan dengan diskon pelanggan.')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, $state, $statePath) {
                                        $parts = explode('.', $statePath);
                                        self::updateLineTotals($get, $set, "items.{$parts[1]}");
                                    }),

                                TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, $state, $statePath) {
                                        $parts = explode('.', $statePath);
                                        self::updateLineTotals($get, $set, "items.{$parts[1]}");
                                    }),

                                // Readonly / Computed Fields (auto-filled, read-only)
                                TextInput::make('harga_base')
                                    ->label('🏷️ Harga Dasar')
                                    ->helperText('Otomatis — harga sebelum diskon')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->extraInputAttributes(['style' => 'background:#f0fdf4;color:#166534;font-weight:600;cursor:not-allowed;']),

                                TextInput::make('discounted_unit_price')
                                    ->label('💰 Harga Setelah Diskon')
                                    ->helperText('Otomatis — harga per unit setelah diskon')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->extraInputAttributes(['style' => 'background:#eff6ff;color:#1e40af;font-weight:700;cursor:not-allowed;']),

                                TextInput::make('line_omzet')
                                    ->label('📊 Total Baris Ini')
                                    ->helperText('Otomatis — harga × jumlah')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->extraInputAttributes(['style' => 'background:#fdf4ff;color:#6b21a8;font-weight:700;cursor:not-allowed;']),

                                // Hidden snapshots which are saved to the database
                                Forms\Components\Hidden::make('product_name'),
                                Forms\Components\Hidden::make('product_type'),
                                Forms\Components\Hidden::make('harga_modal'),
                                Forms\Components\Hidden::make('discount_steps'),
                                Forms\Components\Hidden::make('line_laba'),
                            ])
                            ->columns(5)
                            ->default([])
                            ->reorderable(false)
                            ->addActionLabel('Tambah Item Baru')
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::updateAllLines($get, $set)),
                    ]),

                Forms\Components\Section::make('③ Ringkasan Total Tagihan')
                    ->description('Periksa total tagihan sebelum menyimpan. Pastikan jumlahnya sudah benar.')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Placeholder::make('totals_summary')
                            ->label('')
                            ->columnSpanFull()
                            ->content(function (Get $get) {
                                $items     = $get('items') ?: [];
                                $isBonus   = (bool) $get('is_bonus');

                                $totalOmzet = 0;
                                $itemCount  = 0;
                                foreach ($items as $item) {
                                    $totalOmzet += (float) ($item['line_omzet'] ?? 0);
                                    if (!empty($item['product_id'])) $itemCount++;
                                }

                                $ongkir    = (float) ($get('ongkir') ?? 0);
                                $totalOwed  = $totalOmzet + $ongkir;

                                $totalLabel = $isBonus ? '🎁 Total Biaya Bonus' : '💳 TOTAL YANG HARUS DIBAYAR';
                                $totalColor = $isBonus ? '#059669' : '#4f46e5';
                                $bgColor    = $isBonus
                                    ? 'linear-gradient(135deg,#d1fae5,#ecfdf5)'
                                    : 'linear-gradient(135deg,#eff6ff,#eef2ff)';
                                $border     = $isBonus ? '#6ee7b7' : '#a5b4fc';

                                $bonusNote = $isBonus
                                    ? "<div style='background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:0.88rem;color:#92400e;'>
                                           🎁 <strong>Ini Transaksi Bonus</strong> — Barang diberikan GRATIS kepada pelanggan. Total tagihan = Rp 0.
                                       </div>"
                                    : "";

                                return new HtmlString("
                                    <div style='max-width:520px;margin-left:auto;'>
                                        {$bonusNote}
                                        <div style='background:{$bgColor};border:2px solid {$border};border-radius:16px;padding:22px 26px;box-shadow:0 4px 16px rgba(0,0,0,0.07);'>
                                            <div style='font-size:0.78rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:14px;'>📋 Rincian Tagihan</div>
                                            <div style='display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid rgba(0,0,0,0.06);'>
                                                <span style='color:#475569;font-size:0.95rem;'>Jumlah produk</span>
                                                <span style='font-weight:600;color:#1e293b;'>{$itemCount} item</span>
                                            </div>
                                            <div style='display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid rgba(0,0,0,0.06);'>
                                                <span style='color:#475569;font-size:0.95rem;'>Subtotal Omzet</span>
                                                <span style='font-weight:600;color:#1e293b;font-size:0.95rem;'>Rp " . number_format($totalOmzet, 2, ',', '.') . "</span>
                                            </div>
                                            <div style='display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid rgba(0,0,0,0.06);'>
                                                <span style='color:#475569;font-size:0.95rem;'>Ongkos Kirim</span>
                                                <span style='font-weight:600;color:#1e293b;font-size:0.95rem;'>Rp " . number_format($ongkir, 2, ',', '.') . "</span>
                                            </div>
                                            <div style='display:flex;justify-content:space-between;align-items:center;padding:16px 0 0 0;margin-top:4px;'>
                                                <span style='color:{$totalColor};font-size:1.05rem;font-weight:700;'>{$totalLabel}</span>
                                                <span style='color:{$totalColor};font-size:1.7rem;font-weight:900;letter-spacing:-0.02em;'>Rp " . number_format($totalOwed, 2, ',', '.') . "</span>
                                            </div>
                                        </div>
                                        <div style='text-align:center;margin-top:10px;font-size:0.8rem;color:#94a3b8;'>
                                            ✅ Pastikan total sudah benar sebelum klik tombol <strong>Simpan</strong>.
                                        </div>
                                    </div>
                                ");
                            }),
                    ]),

                Forms\Components\Section::make('📝 Catatan Tambahan (Opsional)')
                    ->description('Tuliskan catatan khusus untuk bon ini jika diperlukan, misalnya instruksi pengiriman atau keterangan lain. Boleh dikosongkan.')
                    ->icon('heroicon-o-pencil-square')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Textarea::make('deskripsi')
                            ->label('Catatan / Keterangan')
                            ->helperText('Contoh: "Kirim ke gudang belakang", "Bayar minggu depan", dll.')
                            ->rows(4)
                            ->placeholder('Tulis catatan di sini jika ada...'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_bon')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Piutang' => 'warning',
                        'Lunas' => 'success',
                    })
                    ->sortable(),

                IconColumn::make('is_bonus')
                    ->label('Bonus?')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('total_owed')
                    ->label('Total Tagihan (Bon)')
                    ->money('idr')
                    ->getStateUsing(fn (Transaction $record) => $record->total_owed),

                TextColumn::make('tanggal_pelunasan')
                    ->label('Tgl Pelunasan')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Pelanggan')
                    ->relationship('customer', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Piutang' => 'Piutang (Outstanding)',
                        'Lunas' => 'Lunas (Settled)',
                    ]),

                Tables\Filters\TernaryFilter::make('is_bonus')
                    ->label('Filter: Transaksi Bonus'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('pdf')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn (Transaction $record): string => route('admin.transactions.pdf', $record))
                    ->openUrlInNewTab(),
                
                // Pelunasan single Bon action
                Tables\Actions\Action::make('mark_lunas')
                    ->label('Sudah Lunas')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Transaction $record) => $record->status === 'Piutang')
                    ->form([
                        DatePicker::make('tanggal_pelunasan')
                            ->label('Tanggal Pelunasan')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (Transaction $record, array $data) {
                        $record->update([
                            'status' => 'Lunas',
                            'tanggal_pelunasan' => $data['tanggal_pelunasan'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Update dynamic values for a single line item in the repeater.
     */
    public static function updateLineTotals(Get $get, Set $set, string $statePath)
    {
        $productId = $get("{$statePath}.product_id");
        $quantity = (int) $get("{$statePath}.quantity") ?: 1;
        $customerId = $get('customer_id');
        $isBonus = (bool) $get('is_bonus');

        if (!$productId || !$customerId) {
            return;
        }

        $product = Product::find($productId);
        $customer = Customer::find($customerId);

        if (!$product || !$customer) {
            return;
        }

        $discountSteps = $product->type === 'LM' 
            ? ($customer->discount_lm ?: []) 
            : ($customer->discount_br ?: []);

        $hargaBase = (float) $product->harga_base;
        $hargaModal = (float) $product->harga_modal;

        if ($isBonus) {
            $discountedUnitPrice = 0.00;
            $lineOmzet = 0.00;
            $lineLaba = 0.00;
            $discountSteps = [];
        } else {
            $discountedUnitPrice = DiscountCalculator::calculate($hargaBase, $discountSteps);
            $lineOmzet = $discountedUnitPrice * $quantity;
            $lineLaba = ($discountedUnitPrice - $hargaModal) * $quantity;
        }

        $set("{$statePath}.product_name", $product->name);
        $set("{$statePath}.product_type", $product->type);
        $set("{$statePath}.harga_base", $hargaBase);
        $set("{$statePath}.harga_modal", $hargaModal);
        $set("{$statePath}.discount_steps", $discountSteps);
        $set("{$statePath}.discounted_unit_price", $discountedUnitPrice);
        $set("{$statePath}.line_omzet", $lineOmzet);
        $set("{$statePath}.line_laba", $lineLaba);
    }

    /**
     * Iterate through all repeater lines and recalculate them.
     */
    public static function updateAllLines(Get $get, Set $set)
    {
        $items = $get('items') ?: [];
        foreach ($items as $index => $item) {
            self::updateLineTotals($get, $set, "items.{$index}");
        }
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
        ];
    }
}
