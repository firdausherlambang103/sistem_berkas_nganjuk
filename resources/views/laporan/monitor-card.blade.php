@php
    // Logika Hitung
    $masuk = $user->total_masuk;
    $keluar = $user->total_keluar;
    $persen = $masuk > 0 ? round(($keluar / $masuk) * 100) : 0;
    
    // Warna Indikator
    $warnaTeks = $persen >= 80 ? 'text-emerald-600' : ($persen >= 50 ? 'text-amber-600' : 'text-rose-600');
    $bgBar = $persen >= 80 ? 'bg-gradient-to-r from-emerald-400 to-emerald-600' : ($persen >= 50 ? 'bg-gradient-to-r from-amber-400 to-amber-600' : 'bg-gradient-to-r from-rose-400 to-rose-600');
    $harian = $user->produktivitas_harian;
    
    // Styling Kartu (Kepala vs Biasa)
    $isHead = $isHead ?? false;
    $cardClass = $isHead
        ? 'bg-white border-2 border-amber-100 p-6 shadow-lg' 
        : 'bg-white border border-gray-100 p-4 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300';
@endphp

<div class="{{ $cardClass }} rounded-xl relative group">
    
    <div class="absolute -top-3 -right-2 {{ $harian > 0 ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-500/30' : 'bg-gray-200 text-gray-500' }} text-xs font-bold px-3 py-1 rounded-full z-10 transition-transform group-hover:scale-110">
        Harian: {{ $harian }}
    </div>

    <div class="flex items-center mb-4">
        <div class="h-12 w-12 flex-shrink-0 rounded-full bg-gradient-to-br from-slate-700 to-slate-900 text-white flex items-center justify-center font-bold text-xl shadow-md mr-3 ring-2 ring-offset-2 {{ $isHead ? 'ring-amber-400' : 'ring-indigo-100' }}">
            {{ substr($user->name, 0, 1) }}
        </div>
        
        <div class="overflow-hidden w-full">
            <h4 class="font-bold text-gray-800 {{ $isHead ? 'text-lg' : 'text-base' }} leading-tight truncate" title="{{ $user->name }}">
                {{ $user->name }}
            </h4>
            <div class="text-xs text-gray-400 font-medium truncate mt-0.5">
                NIP: {{ $user->id }}
            </div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-2 text-center mb-4">
        <div class="bg-blue-50 rounded-lg py-2 px-1 border border-blue-100">
            <span class="block text-[10px] text-blue-600 uppercase font-extrabold tracking-wider">Masuk</span>
            <span class="block text-lg font-black text-slate-700 leading-none mt-1">{{ $masuk }}</span>
        </div>
        <div class="bg-emerald-50 rounded-lg py-2 px-1 border border-emerald-100">
            <span class="block text-[10px] text-emerald-600 uppercase font-extrabold tracking-wider">Selesai</span>
            <span class="block text-lg font-black text-emerald-700 leading-none mt-1">{{ $keluar }}</span>
        </div>
        <div class="bg-rose-50 rounded-lg py-2 px-1 border border-rose-100">
            <span class="block text-[10px] text-rose-500 uppercase font-extrabold tracking-wider">Sisa</span>
            <span class="block text-lg font-black text-rose-600 leading-none mt-1">{{ $user->sisa_berkas }}</span>
        </div>
    </div>

    <div class="flex items-end justify-between gap-2">
        <div class="w-full">
            <div class="flex justify-between items-end mb-1.5">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Produktivitas</span>
                <span class="text-sm font-black {{ $warnaTeks }}">{{ $persen }}%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-3 shadow-inner overflow-hidden">
                <div class="h-3 rounded-full {{ $bgBar }} transition-all duration-1000 relative" style="width: {{ $persen }}%">
                    <div class="absolute inset-0 bg-white/20 w-full h-full"></div>
                </div>
            </div>
        </div>
    </div>
</div>