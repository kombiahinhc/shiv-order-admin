<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class ReportsPage extends Page implements HasForms
{
    use InteractsWithForms;

    public function getView(): string
    {
        return 'filament.pages.reports';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-chart-bar';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Sales';
    }

    public ?array $formData = [
        'salesperson_id' => null,
        'from_date' => null,
        'to_date' => null,
    ];

    public ?array $reportOrders = null;

    public ?array $reportSummary = null;

    public function mount(): void
    {
        $this->form->fill([
            'from_date' => now()->subDays(7)->format('Y-m-d'),
            'to_date' => now()->format('Y-m-d'),
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('salesperson_id')
                    ->label('Sales Person')
                    ->options(fn () => User::pluck('name', 'id'))
                    ->placeholder('All Sales Persons')
                    ->searchable(),
                DatePicker::make('from_date')
                    ->label('From Date')
                    ->required(),
                DatePicker::make('to_date')
                    ->label('To Date')
                    ->required(),
            ])
            ->statePath('formData');
    }

    public function generateReport(): void
    {
        $data = $this->form->getState();

        $query = Order::with('salesperson', 'shop', 'lines')
            ->whereDate('order_date', '>=', $data['from_date'])
            ->whereDate('order_date', '<=', $data['to_date']);

        if (!empty($data['salesperson_id'])) {
            $query->where('salesperson_id', $data['salesperson_id']);
        }

        $orders = $query->orderByDesc('order_date')->get();

        $this->reportOrders = $orders->toArray();

        $totalRevenue = $orders->sum('grand_total');
        $count = $orders->count();

        $this->reportSummary = [
            'count' => $count,
            'total_revenue' => $totalRevenue,
            'average_order_value' => $count > 0 ? $totalRevenue / $count : 0,
            'total_tax' => $orders->sum('tax_total'),
            'total_discount' => $orders->sum('discount_value'),
        ];
    }

    public function getTable(): Table
    {
        return Table::make()
            ->records($this->reportOrders ?? [])
            ->columns([
                Tables\Columns\TextColumn::make('order_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('salesperson.name')
                    ->label('Sales Rep')
                    ->searchable(),
                Tables\Columns\TextColumn::make('shop.name')
                    ->label('Shop')
                    ->getStateUsing(fn (array $record) => $record['shop']['name'] ?? $record['shop_name_snapshot'] ?? 'N/A'),
                Tables\Columns\TextColumn::make('lines_count')
                    ->label('Items')
                    ->getStateUsing(fn (array $record) => count($record['lines'] ?? [])),
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('INR')
                    ->sortable(),
            ])
            ->defaultSort('order_date', 'desc')
            ->emptyStateHeading('No orders found')
            ->emptyStateDescription('Click "Generate Report" to load data.');
    }

    public function exportCsv(): void
    {
        $orders = $this->reportOrders ?? [];

        $headers = ['Date', 'Sales Rep', 'Shop', 'Items', 'Subtotal', 'Tax', 'Discount', 'Grand Total'];

        $callback = function () use ($orders, $headers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order['order_date'],
                    $order['salesperson']['name'] ?? '',
                    $order['shop']['name'] ?? $order['shop_name_snapshot'] ?? '',
                    count($order['lines'] ?? []),
                    $order['subtotal'],
                    $order['tax_total'],
                    $order['discount_value'],
                    $order['grand_total'],
                ]);
            }

            fclose($handle);
        };

        $csvHeaders = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales_report_' . now()->format('Ymd') . '.csv"',
        ];

        response()->stream($callback, 200, $csvHeaders)->send();
    }
}
