<div class="space-y-6 p-4 bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">Fulfillment History</h3>
    
    @php
        $histories = $getRecord()->statusHistories;
    @endphp

    @if($histories->isEmpty())
        <div class="text-sm text-gray-500 dark:text-gray-400 italic">No history logged yet.</div>
    @else
        <div class="relative pl-6 border-l-2 border-primary-500 dark:border-primary-400 space-y-6">
            @foreach($histories as $history)
                @php
                    $classMap = match($history->new_status) {
                        'pending' => [
                            'bg' => 'bg-amber-100 dark:bg-amber-950/50',
                            'text' => 'text-amber-600 dark:text-amber-400',
                            'badge' => 'bg-amber-50 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300 border-amber-200/50 dark:border-amber-800/50',
                            'icon' => '⏳'
                        ],
                        'processing' => [
                            'bg' => 'bg-sky-100 dark:bg-sky-950/50',
                            'text' => 'text-sky-600 dark:text-sky-400',
                            'badge' => 'bg-sky-50 dark:bg-sky-900/50 text-sky-700 dark:text-sky-300 border-sky-200/50 dark:border-sky-800/50',
                            'icon' => '⚙️'
                        ],
                        'shipped' => [
                            'bg' => 'bg-indigo-100 dark:bg-indigo-950/50',
                            'text' => 'text-indigo-600 dark:text-indigo-400',
                            'badge' => 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border-indigo-200/50 dark:border-indigo-800/50',
                            'icon' => '🚚'
                        ],
                        'delivered' => [
                            'bg' => 'bg-emerald-100 dark:bg-emerald-950/50',
                            'text' => 'text-emerald-600 dark:text-emerald-400',
                            'badge' => 'bg-emerald-50 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 border-emerald-200/50 dark:border-emerald-800/50',
                            'icon' => '✅'
                        ],
                        'cancelled' => [
                            'bg' => 'bg-rose-100 dark:bg-rose-950/50',
                            'text' => 'text-rose-600 dark:text-rose-400',
                            'badge' => 'bg-rose-50 dark:bg-rose-900/50 text-rose-700 dark:text-rose-300 border-rose-200/50 dark:border-rose-800/50',
                            'icon' => '❌'
                        ],
                        default => [
                            'bg' => 'bg-gray-100 dark:bg-gray-950/50',
                            'text' => 'text-gray-600 dark:text-gray-400',
                            'badge' => 'bg-gray-50 dark:bg-gray-900/50 text-gray-700 dark:text-gray-300 border-gray-200/50 dark:border-gray-800/50',
                            'icon' => '•'
                        ]
                    };
                @endphp
                
                <div class="relative group">
                    <!-- Marker Dot -->
                    <span class="absolute -left-[31px] top-1 flex items-center justify-center w-5 h-5 rounded-full {{ $classMap['bg'] }} {{ $classMap['text'] }} text-xs border-2 border-white dark:border-gray-900">
                        {{ $classMap['icon'] }}
                    </span>
                    
                    <div>
                        <div class="flex flex-wrap items-baseline justify-between gap-x-2">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                Transitioned to <span class="capitalize px-2 py-0.5 rounded text-xs {{ $classMap['badge'] }} border">{{ $history->new_status }}</span>
                            </h4>
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                {{ $history->created_at->format('M d, Y h:i A') }}
                            </span>
                        </div>
                        
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Changed from <span class="capitalize font-semibold">{{ $history->old_status }}</span> 
                            @if($history->changer)
                                by <span class="font-medium text-gray-700 dark:text-gray-300">{{ $history->changer->name }}</span>
                            @else
                                by <span class="font-medium italic">System</span>
                            @endif
                        </p>
                        
                        @if($history->notes)
                            <div class="mt-2 text-xs p-2 bg-gray-50 dark:bg-gray-800 rounded border border-gray-100 dark:border-gray-700 text-gray-600 dark:text-gray-400">
                                {{ $history->notes }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
