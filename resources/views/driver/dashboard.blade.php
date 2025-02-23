<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Total Deliveries</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $performanceMetrics['total_deliveries'] }}</div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Successful Deliveries</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">{{ $performanceMetrics['successful_deliveries'] }}</div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Today's Collections</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900">₵{{ number_format($todayCollections, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Assigned Zones -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Assigned Zones</h3>
                    <div class="space-y-4">
                        @foreach($zones as $zone)
                            <div class="border rounded-lg p-4">
                                <h4 class="text-lg font-medium text-gray-900">{{ $zone->name }}</h4>
                                <p class="mt-1 text-sm text-gray-500">{{ $zone->locations->count() }} pending deliveries</p>
                                
                                @if($zone->locations->isNotEmpty())
                                    <div class="mt-4 space-y-3">
                                        @foreach($zone->locations as $location)
                                            <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $location->shop_name }}</p>
                                                    <p class="text-sm text-gray-500">{{ $location->address }}</p>
                                                    @if($location->payment_required)
                                                        <p class="mt-1 text-sm font-medium text-indigo-600">
                                                            Collection: ₵{{ number_format($location->payment_amount, 2) }}
                                                        </p>
                                                    @endif
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <a href="#" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        View Details
                                                    </a>
                                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        Start Delivery
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mt-4 text-sm text-gray-500">No pending deliveries in this zone.</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activities</h3>
                    <div class="space-y-4">
                        @foreach($recentActivities as $activity)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $activity->description }}
                                    </p>
                                    <p class="mt-0.5 text-sm text-gray-500">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
