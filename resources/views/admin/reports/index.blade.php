<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Reports & Analytics</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Activity Report -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Activity Log</h3>
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500 mb-4">Track all system activities including deliveries, payments, and user actions.</p>
                            <a href="{{ route('admin.reports.activity') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                View Activity Log
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>

                        <!-- Performance Report -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Performance Metrics</h3>
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500 mb-4">Analyze driver performance, delivery success rates, and zone efficiency.</p>
                            <a href="{{ route('admin.reports.performance') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                View Performance Report
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>

                        <!-- Export Report -->
                        <div class="bg-white p-6 rounded-lg shadow">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Export Data</h3>
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500 mb-4">Generate and download comprehensive reports in various formats.</p>
                            <a href="{{ route('admin.reports.export') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                Export Reports
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="mt-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Statistics</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-500">Today's Deliveries</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $todayDeliveries ?? 0 }}</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-500">Active Drivers</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $activeDrivers ?? 0 }}</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-500">Success Rate</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $successRate ?? '0%' }}</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-500">Total Collections</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900">₵{{ number_format($totalCollections ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
