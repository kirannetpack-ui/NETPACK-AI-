@extends('layouts.app')

@section('title', 'Inbound Data Sheet | ' . $manifest->manifest_number)

@section('content')
<div class="max-w-full mx-auto space-y-5 px-2">
    <!-- Action / Title Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('international.manifests.show', $manifest->id) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                    <i class="fas fa-arrow-left"></i> Back to Manifest {{ $manifest->manifest_number }}
                </a>
                <span class="text-slate-400">&bull;</span>
                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    Comprehensive Shipment Data Sheet
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white flex items-center gap-3">
                <span>Data Sheet: {{ $manifest->manifest_number }}</span>
                <span class="text-sm font-normal font-mono text-slate-500 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg">
                    MAWB: {{ $manifest->mawb_number ?? 'N/A' }}
                </span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Hub: <span class="font-bold text-slate-700 dark:text-slate-300">{{ $manifest->hub->name ?? 'Direct' }}</span> &bull; 
                Agency: <span class="font-bold text-slate-700 dark:text-slate-300">{{ $manifest->agency->name ?? 'Standard Schema' }}</span> &bull; 
                Total Lines: <span class="font-bold text-indigo-600">{{ count($dataSheetRows) }}</span> consignments
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if($manifest->agency)
                <a href="{{ route('international.agencies.format-settings', $manifest->agency->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 transition">
                    <i class="fas fa-sliders-h text-indigo-500"></i> Customize Columns
                </a>
            @endif
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 transition">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="{{ route('international.manifests.data-sheet', [$manifest->id, 'export' => 'csv']) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition">
                <i class="fas fa-download"></i> Export Clean CSV
            </a>
        </div>
    </div>

    <!-- Data Sheet Table Container -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto max-h-[75vh]">
            <table class="w-full text-left text-xs whitespace-nowrap">
                <thead class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 uppercase tracking-wider font-bold sticky top-0 z-10 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-3.5 py-3 text-center bg-slate-200/60 dark:bg-slate-800/90 w-10">#</th>
                        @if(!empty($dataSheetRows))
                            @foreach(array_keys($dataSheetRows[0]) as $columnHeader)
                                <th class="px-3.5 py-3 border-r border-slate-200 dark:border-slate-700 font-bold">
                                    {{ $columnHeader }}
                                </th>
                            @endforeach
                        @else
                            <th class="px-4 py-3">No Data Columns</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-sans">
                    @forelse($dataSheetRows as $rowIndex => $row)
                        <tr class="hover:bg-indigo-50/40 dark:hover:bg-indigo-950/20 transition">
                            <td class="px-3.5 py-2.5 text-center font-bold text-slate-400 bg-slate-50/50 dark:bg-slate-800/30">
                                {{ $rowIndex + 1 }}
                            </td>
                            @foreach($row as $key => $cellValue)
                                <td class="px-3.5 py-2.5 border-r border-slate-100 dark:border-slate-800/60 font-mono text-[11px]">
                                    @if(in_array($key, ['HAWB Number', 'Tracking Number', 'MAWB Number']))
                                        <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $cellValue }}</span>
                                    @elseif(str_contains(strtolower($key), 'weight'))
                                        <span class="font-bold text-slate-900 dark:text-white">{{ $cellValue }}</span>
                                    @elseif(str_contains(strtolower($key), 'mode'))
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $cellValue === 'DDP' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ $cellValue }}
                                        </span>
                                    @else
                                        {{ $cellValue ?: '-' }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-8 text-center text-slate-400">
                                No shipments attached to this manifest.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
