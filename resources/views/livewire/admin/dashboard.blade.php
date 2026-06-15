<div>
    <h1 class="text-2xl font-bold mb-6">{{ __('admin.dashboard.title') }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="rounded-lg bg-white dark:bg-gray-800 p-5 shadow">
            <div class="text-sm text-gray-500">{{ __('admin.dashboard.revenue_30d') }}</div>
            <div class="text-2xl font-semibold mt-1">
                {{ config('ranga.defaults.currency_symbol') }}{{ number_format((float) ($kpis['sales_30d']['gross_sales'] ?? 0), 2) }}
            </div>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-5 shadow">
            <div class="text-sm text-gray-500">{{ __('admin.dashboard.orders_30d') }}</div>
            <div class="text-2xl font-semibold mt-1">{{ $kpis['sales_30d']['orders'] ?? 0 }}</div>
        </div>
        <div class="rounded-lg bg-white dark:bg-gray-800 p-5 shadow">
            <div class="text-sm text-gray-500">{{ __('admin.dashboard.customers') }}</div>
            <div class="text-2xl font-semibold mt-1">{{ $kpis['customers']['total'] ?? 0 }}</div>
        </div>
    </div>

    <div class="rounded-lg bg-white dark:bg-gray-800 p-5 shadow">
        <h2 class="font-semibold mb-3">{{ __('admin.dashboard.top_products') }}</h2>
        <ul class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse ($kpis['top_products'] ?? [] as $product)
                <li class="py-2 flex justify-between">
                    <span>{{ $product['name'] }}</span>
                    <span class="text-gray-500">{{ $product['quantity'] }}</span>
                </li>
            @empty
                <li class="py-2 text-gray-500">{{ __('admin.dashboard.no_sales') }}</li>
            @endforelse
        </ul>
    </div>
</div>
