@extends('layouts/conquer')

@section('content')
    <div class="container mx-auto px-6 py-8">
        <div class="mb-6">
            <h1 class="text-4xl font-light text-gray-600">Sales Profit/Loss Analysis</h1>
        </div>

        <!-- Search Forms -->
        <div class="mb-8 bg-white p-6 rounded-lg shadow-md">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Search by Product -->
                <div>
                    <form action="{{ route('sales.profit-loss') }}" method="GET" class="flex items-end">
                        @if (request('search_invoice'))
                            <input type="hidden" name="search_invoice" value="{{ request('search_invoice') }}">
                        @endif
                        <div class="flex-grow mr-2">
                            <label for="search_product" class="block text-sm font-medium text-gray-700 mb-1">Search by
                                Product Name</label>
                            <input type="text" id="search_product" name="search_product"
                                value="{{ $searchProduct ?? '' }}"
                                class="px-4 py-2 border border-gray-300 rounded-md w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Enter product name...">
                        </div>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Search
                        </button>
                        @if ($searchProduct)
                            <a href="{{ url()->current() }}?{{ http_build_query(request()->except('search_product')) }}"
                                class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Search by Invoice -->
                <div>
                    <form action="{{ route('sales.profit-loss') }}" method="GET" class="flex items-end">
                        @if (request('search_product'))
                            <input type="hidden" name="search_product" value="{{ request('search_product') }}">
                        @endif
                        <div class="flex-grow mr-2">
                            <label for="search_invoice" class="block text-sm font-medium text-gray-700 mb-1">Search by
                                Invoice Number</label>
                            <input type="text" id="search_invoice" name="search_invoice"
                                value="{{ $searchInvoice ?? '' }}"
                                class="px-4 py-2 border border-gray-300 rounded-md w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Enter invoice number...">
                        </div>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Search
                        </button>
                        @if ($searchInvoice)
                            <a href="{{ url()->current() }}?{{ http_build_query(request()->except('search_invoice')) }}"
                                class="ml-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            @if ($searchProduct || $searchInvoice)
                <div class="mt-4 bg-blue-50 p-3 rounded-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-medium">Active filters:</span>
                            @if ($searchProduct)
                                <span
                                    class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Product: {{ $searchProduct }}
                                </span>
                            @endif
                            @if ($searchInvoice)
                                <span
                                    class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Invoice: {{ $searchInvoice }}
                                </span>
                            @endif
                        </div>
                        <a href="{{ route('sales.profit-loss') }}" class="text-sm text-blue-600 hover:text-blue-800">Clear
                            all filters</a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Summary Section -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-semibold mb-4">Summary</h2>
            <div class="grid grid-cols-4 gap-4">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <div class="text-sm text-blue-700">Total Revenue</div>
                    <div class="text-2xl font-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                </div>
                <div class="p-4 bg-red-50 rounded-lg">
                    <div class="text-sm text-red-700">Total Cost</div>
                    <div class="text-2xl font-bold">Rp {{ number_format($totalCost, 0, ',', '.') }}</div>
                </div>
                <div class="p-4 bg-green-50 rounded-lg">
                    <div class="text-sm text-green-700">Total Profit</div>
                    <div class="text-2xl font-bold">Rp {{ number_format($totalProfit, 0, ',', '.') }}</div>
                </div>
                <div class="p-4 bg-purple-50 rounded-lg">
                    <div class="text-sm text-purple-700">Overall Margin</div>
                    <div class="text-2xl font-bold">{{ number_format($overallMargin, 2, ',', '.') }}%</div>
                </div>
            </div>
        </div>

        <!-- Product Profit Analysis -->
        <div class="bg-white">
            <h2 class="text-2xl font-semibold p-6">Profit by Product</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-y border-gray-200">
                            <th class="py-4 px-4 text-left text-sm font-medium text-gray-500">Product Name</th>
                            <th class="py-4 px-4 text-right text-sm font-medium text-gray-500">Quantity Sold</th>
                            <th class="py-4 px-4 text-right text-sm font-medium text-gray-500">Revenue (IDR)</th>
                            <th class="py-4 px-4 text-right text-sm font-medium text-gray-500">Cost (IDR)</th>
                            <th class="py-4 px-4 text-right text-sm font-medium text-gray-500">Profit (IDR)</th>
                            <th class="py-4 px-4 text-right text-sm font-medium text-gray-500">Profit Margin (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @if ($productProfits->count() > 0)
                            @foreach ($productProfits as $product)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-4 px-4">
                                        <div class="text-gray-800 font-medium">{{ $product['product_name'] }}</div>
                                    </td>
                                    <td class="py-4 px-4 text-right font-medium text-gray-600">
                                        {{ number_format($product['total_quantity'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-medium text-gray-600">
                                        {{ number_format($product['total_revenue'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-medium text-gray-600">
                                        {{ number_format($product['total_cost'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        <span
                                            class="font-medium {{ $product['total_profit'] < 0 ? 'text-red-500' : 'text-emerald-500' }}">
                                            {{ number_format($product['total_profit'], 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        <span
                                            class="font-medium {{ $product['profit_margin'] < 0 ? 'text-red-500' : 'text-emerald-500' }}">
                                            {{ number_format($product['profit_margin'], 2, ',', '.') }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="py-4 px-4 text-center text-gray-500">
                                    No product data found with the selected filters.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sales Invoice Analysis -->
        <div class="bg-white mt-8">
            <h2 class="text-2xl font-semibold p-6">Profit by Sales Invoice</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-y border-gray-200">
                            <th class="py-4 px-4 text-left text-sm font-medium text-gray-500">Invoice</th>
                            <th class="py-4 px-4 text-left text-sm font-medium text-gray-500">Date</th>
                            <th class="py-4 px-4 text-right text-sm font-medium text-gray-500">Revenue</th>
                            <th class="py-4 px-4 text-right text-sm font-medium text-gray-500">Cost</th>
                            <th class="py-4 px-4 text-right text-sm font-medium text-gray-500">Discount</th>
                            <th class="py-4 px-4 text-right text-sm font-medium text-gray-500">Profit</th>
                            <th class="py-4 px-4 text-right text-sm font-medium text-gray-500">Margin (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @if (count($salesResults) > 0)
                            @foreach ($salesResults as $sale)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-4 px-4 font-medium">{{ $sale['invoice_number'] }}</td>
                                    <td class="py-4 px-4">{{ \Carbon\Carbon::parse($sale['date'])->format('d M Y') }}</td>
                                    <td class="py-4 px-4 text-right">{{ number_format($sale['revenue'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-right">{{ number_format($sale['cost'], 0, ',', '.') }}</td>
                                    <td class="py-4 px-4 text-right">
                                        {{ number_format($sale['discount'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="py-4 px-4 text-right">
                                        <span
                                            class="{{ $sale['profit'] < 0 ? 'text-red-500' : 'text-emerald-500' }} font-medium">
                                            {{ number_format($sale['profit'], 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        <span
                                            class="{{ $sale['profit_margin'] < 0 ? 'text-red-500' : 'text-emerald-500' }} font-medium">
                                            {{ number_format($sale['profit_margin'], 2, ',', '.') }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="py-4 px-4 text-center text-gray-500">
                                    No sales data found with the selected filters.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
