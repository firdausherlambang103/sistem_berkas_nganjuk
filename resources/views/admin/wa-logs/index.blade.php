<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Riwayat Pengiriman WhatsApp') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pengirim</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tujuan</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Berkas</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Pesan</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($logs as $log)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $log->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $log->user->name ?? 'Sistem' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $log->target_phone }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                                            @if($log->berkas)
                                                <a href="{{ route('berkas.show', $log->berkas_id) }}" class="hover:underline">
                                                    {{ $log->berkas->nomer_berkas }}
                                                </a>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $log->pesan }}">
                                            {{ Str::limit($log->pesan, 50) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if(strtolower($log->status) == 'sukses' || $log->status == 1 || $log->status == true)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Terikirim
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ $log->status }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada riwayat pengiriman.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>