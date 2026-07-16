<x-filament-panels::page>
    <form wire:submit="generateReport">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button type="submit">
                Generate Report
            </x-filament::button>

            @if($reportOrders)
                <x-filament::button wire:click="exportCsv" color="gray" class="ml-2">
                    <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                    Export CSV
                </x-filament::button>
            @endif
        </div>
    </form>

    @if($reportSummary)
        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-900">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Orders</div>
                <div class="mt-1 text-2xl font-bold">{{ $reportSummary['count'] }}</div>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-900">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</div>
                <div class="mt-1 text-2xl font-bold">₹{{ number_format($reportSummary['total_revenue'], 0) }}</div>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-900">
                <div class="text-sm text-gray-500 dark:text-gray-400">Avg Order Value</div>
                <div class="mt-1 text-2xl font-bold">₹{{ number_format($reportSummary['average_order_value'], 0) }}</div>
            </div>
            <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-gray-900">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Tax</div>
                <div class="mt-1 text-2xl font-bold">₹{{ number_format($reportSummary['total_tax'], 0) }}</div>
            </div>
        </div>
    @endif

    @if($reportOrders !== null && count($reportOrders) > 0)
        <div class="mt-6">
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Sales Rep</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Shop</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Items</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        @foreach($reportOrders as $order)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">{{ $order['order_date'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">{{ $order['salesperson']['name'] ?? '' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">{{ $order['shop']['name'] ?? $order['shop_name_snapshot'] ?? 'N/A' }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-center text-sm">{{ count($order['lines'] ?? []) }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium">₹{{ number_format($order['grand_total'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>
