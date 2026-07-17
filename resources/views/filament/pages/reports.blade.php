<x-filament-panels::page>
    <style>
        .sales-report-page { display: grid; gap: 1.5rem; }
        .sales-report-heading { display: flex; align-items: end; justify-content: space-between; gap: 1rem; }
        .sales-report-heading h1 { margin: 0; font-size: 1.5rem; line-height: 2rem; }
        .sales-report-heading p { margin: .25rem 0 0; color: #9ca3af; }
        .sales-report-status { border: 1px solid rgba(34, 197, 94, .3); border-radius: 999px; padding: .35rem .75rem; color: #86efac; white-space: nowrap; }
        .sales-report-form, .sales-report-table-card, .sales-report-stat { border: 1px solid #374151; border-radius: .75rem; background: #171717; }
        .sales-report-form { padding: 1.25rem; }
        .sales-report-form h2, .sales-report-table-card h2 { margin: 0; font-size: 1rem; }
        .sales-report-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid #374151; }
        .sales-report-actions-label { color: #9ca3af; font-size: .875rem; }
        .sales-report-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; }
        .sales-report-stat { padding: 1.25rem; }
        .sales-report-stat-label { color: #9ca3af; font-size: .875rem; }
        .sales-report-stat-value { margin: .5rem 0 0; font-size: 1.6rem; font-weight: 700; }
        .sales-report-table-header { padding: 1.25rem; border-bottom: 1px solid #374151; }
        .sales-report-table-header p { margin: .25rem 0 0; color: #9ca3af; font-size: .875rem; }
        .sales-report-table-wrap { overflow-x: auto; }
        .sales-report-table { width: 100%; min-width: 720px; border-collapse: collapse; }
        .sales-report-table th { padding: .85rem 1.25rem; background: #202020; color: #a1a1aa; font-size: .75rem; font-weight: 700; letter-spacing: .05em; text-align: left; text-transform: uppercase; white-space: nowrap; }
        .sales-report-table td { padding: 1rem 1.25rem; border-top: 1px solid #2d2d2d; vertical-align: middle; white-space: nowrap; }
        .sales-report-table tbody tr:hover { background: #202020; }
        .sales-report-table .center { text-align: center; }
        .sales-report-table .right { text-align: right; }
        .sales-report-table .amount { font-weight: 700; }
        .sales-report-empty { padding: 3.5rem 1.5rem; text-align: center; }
        .sales-report-empty p { margin: 0; }
        .sales-report-empty p + p { margin-top: .25rem; color: #9ca3af; font-size: .875rem; }
        @media (max-width: 900px) { .sales-report-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 640px) { .sales-report-heading { align-items: start; flex-direction: column; } .sales-report-stats { grid-template-columns: 1fr; } }
    </style>

    <div class="sales-report-page">
        <div class="sales-report-heading">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">Sales reports</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Filter orders by representative and date, then export the results.</p>
            </div>
            @if($reportOrders !== null)
                <div class="sales-report-status">
                    {{ number_format($reportSummary['count']) }} {{ Str::plural('order', $reportSummary['count']) }} loaded
                </div>
            @endif
        </div>

        <form wire:submit="generateReport" class="sales-report-form">
            <div>
                <div>
                    <h2 class="font-semibold text-gray-950 dark:text-white">Report filters</h2>
                </div>
            </div>

            {{ $this->form }}

            <div class="sales-report-actions">
                <x-filament::button type="submit" icon="heroicon-m-chart-bar-square">
                    Generate report
                </x-filament::button>

                @if($reportOrders !== null)
                    <span class="sales-report-actions-label">Export as</span>
                    <x-filament::button wire:click="exportExcel" color="gray" icon="heroicon-m-table-cells">
                        Excel
                    </x-filament::button>
                    <x-filament::button wire:click="exportPdf" color="gray" icon="heroicon-m-document-arrow-down">
                        PDF
                    </x-filament::button>
                @endif
            </div>
        </form>

        @if($reportSummary)
            <div class="sales-report-stats">
                <div class="sales-report-stat">
                    <span class="sales-report-stat-label">Total orders</span>
                    <p class="sales-report-stat-value">{{ number_format($reportSummary['count']) }}</p>
                </div>
                <div class="sales-report-stat">
                    <span class="sales-report-stat-label">Total revenue</span>
                    <p class="sales-report-stat-value">₹{{ number_format($reportSummary['total_revenue'], 0) }}</p>
                </div>
                <div class="sales-report-stat">
                    <span class="sales-report-stat-label">Average order value</span>
                    <p class="sales-report-stat-value">₹{{ number_format($reportSummary['average_order_value'], 0) }}</p>
                </div>
                <div class="sales-report-stat">
                    <span class="sales-report-stat-label">Total tax</span>
                    <p class="sales-report-stat-value">₹{{ number_format($reportSummary['total_tax'], 0) }}</p>
                </div>
            </div>
        @endif

        @if($reportOrders !== null)
            <div class="sales-report-table-card">
                <div class="sales-report-table-header">
                    <div>
                        <h2 class="font-semibold text-gray-950 dark:text-white">Order breakdown</h2>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ count($reportOrders) ? 'All matching orders, newest first.' : 'No orders matched these filters.' }}</p>
                    </div>
                </div>

                @if(count($reportOrders))
                    <div class="sales-report-table-wrap">
                        <table class="sales-report-table">
                            <thead>
                                <tr>
                                    <th>Date</th><th>Sales rep</th><th>Shop</th><th class="center">Items</th><th class="right">Grand total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reportOrders as $order)
                                    <tr>
                                        <td>{{ \Illuminate\Support\Carbon::parse($order['order_date'])->format('d M Y') }}</td>
                                        <td>{{ $order['salesperson']['name'] ?? 'Unassigned' }}</td>
                                        <td>{{ $order['shop']['name'] ?? $order['shop_name_snapshot'] ?? 'N/A' }}</td>
                                        <td class="center">{{ count($order['lines'] ?? []) }}</td>
                                        <td class="right amount">₹{{ number_format($order['grand_total'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="sales-report-empty">
                        <p>No orders found</p>
                        <p>Try widening the date range or changing the sales representative.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>
