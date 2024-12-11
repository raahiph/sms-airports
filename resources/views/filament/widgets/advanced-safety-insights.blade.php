<x-filament::widget>
    <x-filament::card>
        <div class="space-y-6">
            {{-- Occurrence Stats --}}
            <div class="rounded-lg bg-white p-4 shadow">
                <h3 class="text-lg font-medium text-gray-900">Safety Event Statistics</h3>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($occurrence_stats as $key => $value)
                        <div class="rounded-lg bg-gray-50 p-4">
                            <h4 class="text-sm font-medium text-gray-500">{{ ucwords(str_replace('_', ' ', $key)) }}</h4>
                            <p class="mt-2 text-lg font-semibold text-gray-900">{{ $value }}</p>
                        </div>
                    @empty
                        <div class="rounded-lg bg-gray-50 p-4">
                            <p class="text-sm text-gray-500">No statistics available</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Predictions --}}
            <div class="rounded-lg bg-white p-4 shadow">
                <h3 class="text-lg font-medium text-gray-900">Risk Assessment</h3>
                <div class="mt-4">
                    <div class="flex items-center">
                        <div @class([
                            'h-4 w-4 rounded-full mr-2',
                            'bg-red-500' => $predictions['risk_level'] === 'high',
                            'bg-yellow-500' => $predictions['risk_level'] === 'medium',
                            'bg-green-500' => $predictions['risk_level'] === 'low',
                            'bg-gray-500' => $predictions['risk_level'] === 'unknown',
                        ])></div>
                        <span class="text-sm font-medium text-gray-900">
                            Risk Level: {{ ucfirst($predictions['risk_level']) }}
                        </span>
                    </div>
                    <div class="mt-1 text-sm text-gray-500">
                        Confidence: {{ number_format($predictions['confidence'] * 100, 1) }}%
                    </div>
                </div>
            </div>

            {{-- Hotspots --}}
            <div class="rounded-lg bg-white p-4 shadow">
                <h3 class="text-lg font-medium text-gray-900">Risk Hotspots</h3>
                <div class="mt-4">
                    <div class="space-y-4">
                        @forelse($hotspots as $hotspot)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900">{{ $hotspot['location'] ?? 'Unknown' }}</span>
                                <span @class([
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    'bg-red-100 text-red-800' => ($hotspot['risk_level'] ?? '') === 'high',
                                    'bg-yellow-100 text-yellow-800' => ($hotspot['risk_level'] ?? '') === 'medium',
                                    'bg-green-100 text-green-800' => ($hotspot['risk_level'] ?? '') === 'low',
                                ])>
                                    {{ ucfirst($hotspot['risk_level'] ?? 'unknown') }} Risk
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No hotspot data available</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Recommendations --}}
            <div class="rounded-lg bg-white p-4 shadow">
                <h3 class="text-lg font-medium text-gray-900">Safety Recommendations</h3>
                <div class="mt-4 space-y-4">
                    @forelse($recommendations as $recommendation)
                        <p class="text-sm text-gray-600">{{ $recommendation }}</p>
                    @empty
                        <p class="text-sm text-gray-500">No recommendations available</p>
                    @endforelse
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>