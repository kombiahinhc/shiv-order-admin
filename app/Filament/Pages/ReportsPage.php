<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

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
        'salesperson_ids' => [],
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
                Select::make('salesperson_ids')
                    ->label('Sales Representatives')
                    ->options(fn () => User::pluck('name', 'id'))
                    ->placeholder('All Sales Representatives')
                    ->searchable()
                    ->multiple()
                    ->preload(),
                DatePicker::make('from_date')
                    ->label('From Date')
                    ->required(),
                DatePicker::make('to_date')
                    ->label('To Date')
                    ->required(),
            ])
            ->columns(3)
            ->statePath('formData');
    }

    public function generateReport(): void
    {
        $data = $this->form->getState();

        $query = Order::with('salesperson', 'shop', 'lines')
            ->whereDate('order_date', '>=', $data['from_date'])
            ->whereDate('order_date', '<=', $data['to_date']);

        if (! empty($data['salesperson_ids'])) {
            $query->whereIn('salesperson_id', $data['salesperson_ids']);
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

    public function exportExcel()
    {
        $orders = $this->reportOrders ?? [];
        $filename = $this->exportFilename('xlsx');
        $path = tempnam(sys_get_temp_dir(), 'sales-report-');

        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(['Sales report']));
        $writer->addRow(Row::fromValues([
            'Period',
            Carbon::parse($this->formData['from_date'])->format('d M Y').' – '.Carbon::parse($this->formData['to_date'])->format('d M Y'),
        ]));
        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValues(['Date', 'Sales Rep', 'Shop', 'Items', 'Subtotal', 'Tax', 'Discount', 'Grand Total']));

        foreach ($orders as $order) {
            $writer->addRow(Row::fromValues($this->exportRow($order)));
        }

        $writer->close();

        return response()->streamDownload(function () use ($path): void {
            readfile($path);
            unlink($path);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportPdf()
    {
        $pdf = Pdf::loadView('reports.sales-pdf', [
            'orders' => $this->reportOrders ?? [],
            'summary' => $this->reportSummary ?? [],
            'fromDate' => Carbon::parse($this->formData['from_date']),
            'toDate' => Carbon::parse($this->formData['to_date']),
            'salesperson' => ! empty($this->formData['salesperson_ids'])
                ? User::whereIn('id', $this->formData['salesperson_ids'])->pluck('name')->join(', ')
                : null,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf->output();
        }, $this->exportFilename('pdf'), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function exportRow(array $order): array
    {
        return [
            Carbon::parse($order['order_date'])->format('d M Y'),
            $order['salesperson']['name'] ?? '',
            $order['shop']['name'] ?? $order['shop_name_snapshot'] ?? 'N/A',
            count($order['lines'] ?? []),
            (float) $order['subtotal'],
            (float) $order['tax_total'],
            (float) $order['discount_value'],
            (float) $order['grand_total'],
        ];
    }

    private function exportFilename(string $extension): string
    {
        return 'sales-report-'.Carbon::parse($this->formData['from_date'])->format('Ymd')
            .'-to-'.Carbon::parse($this->formData['to_date'])->format('Ymd')
            .'.'.$extension;
    }
}
