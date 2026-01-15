<div class="fixed bottom-4 right-4 z-50 flex flex-col items-end">
    
    {{-- WINDOW CHAT (Percakapan) --}}
    @if($selectedUserId && $activeUser)
    <div wire:poll.2000ms="markAsRead" class="bg-white w-80 h-[30rem] shadow-2xl rounded-t-lg border border-gray-200 flex flex-col mb-4 ring-1 ring-black/5">
        
        {{-- Header --}}
        <div class="bg-blue-600 p-3 rounded-t-lg flex justify-between items-center text-white shadow-sm">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-9 h-9 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white font-bold text-sm border border-white/30">
                        {{ substr($activeUser->name, 0, 1) }}
                    </div>
                    @if($activeUser->isOnline())
                        <span class="absolute bottom-0 right-0 block w-2.5 h-2.5 bg-green-400 border-2 border-blue-600 rounded-full"></span>
                    @else
                        <span class="absolute bottom-0 right-0 block w-2.5 h-2.5 bg-gray-400 border-2 border-blue-600 rounded-full"></span>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-bold leading-tight">{{ Str::limit($activeUser->name, 15) }}</p>
                    <p class="text-[10px] text-blue-100 opacity-90">{{ $activeUser->isOnline() ? 'Online' : 'Offline' }}</p>
                </div>
            </div>
            <button wire:click="$set('selectedUserId', null)" class="text-white/80 hover:text-white transition p-1 hover:bg-white/10 rounded">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Body Chat --}}
        <div class="flex-1 p-3 overflow-y-auto bg-gray-50 flex flex-col gap-3" id="chat-container">
            @forelse($currentMessages as $msg)
                <div class="flex flex-col max-w-[85%] {{ $msg->sender_id === auth()->id() ? 'self-end items-end' : 'self-start items-start' }}">
                    
                    <div class="p-2.5 rounded-2xl shadow-sm text-sm 
                        {{ $msg->sender_id === auth()->id() 
                            ? 'bg-blue-600 text-white rounded-br-none' 
                            : 'bg-white border border-gray-200 text-gray-800 rounded-bl-none' }}">
                        
                        {{-- Tampilkan Attachment --}}
                        @if($msg->attachment)
                            <div class="mb-2">
                                @php
                                    $ext = pathinfo($msg->attachment, PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                @endphp

                                @if($isImage)
                                    <a href="{{ asset('storage/'.$msg->attachment) }}" target="_blank">
                                        <img src="{{ asset('storage/'.$msg->attachment) }}" class="rounded-lg max-h-32 w-auto object-cover border border-black/10 hover:opacity-90 transition">
                                    </a>
                                @else
                                    <a href="{{ asset('storage/'.$msg->attachment) }}" target="_blank" class="flex items-center gap-2 bg-black/10 p-2 rounded hover:bg-black/20 transition {{ $msg->sender_id === auth()->id() ? 'text-white' : 'text-gray-700' }}">
                                        <i class="fas fa-file-alt text-lg"></i>
                                        <div class="flex flex-col overflow-hidden">
                                            <span class="text-xs font-semibold truncate w-32">{{ $msg->attachment_original_name }}</span>
                                            <span class="text-[9px] uppercase opacity-70">{{ $ext }} File</span>
                                        </div>
                                        <i class="fas fa-download ml-auto opacity-70"></i>
                                    </a>
                                @endif
                            </div>
                        @endif

                        {{-- Tampilkan Pesan Teks --}}
                        @if($msg->message)
                            <p class="break-words leading-relaxed">{{ $msg->message }}</p>
                        @endif
                    </div>

                    {{-- Waktu & Status Read --}}
                    <span class="text-[10px] text-gray-400 mt-1 px-1 flex items-center gap-1">
                        {{ $msg->created_at->format('H:i') }}
                        @if($msg->sender_id === auth()->id())
                            <span class="{{ $msg->is_read ? 'text-blue-500' : 'text-gray-300' }}">
                                <i class="fas fa-check-double text-[10px]"></i>
                            </span>
                        @endif
                    </span>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-full text-gray-400 gap-2">
                    <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-xl">
                        <i class="fas fa-comments text-gray-400"></i>
                    </div>
                    <p class="text-xs">Mulai percakapan dengan {{ explode(' ', $activeUser->name)[0] }}</p>
                </div>
            @endforelse
        </div>

        {{-- Footer Input --}}
        <div class="p-3 bg-white border-t border-gray-100">
            
            {{-- [BARU] Menampilkan Error Validasi (Penting agar tahu jika file terlalu besar) --}}
            @error('attachment') 
                <div class="mb-2 bg-red-50 border border-red-200 text-red-600 text-[10px] px-2 py-1 rounded flex items-center gap-1">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </div> 
            @enderror
            @error('messageText') 
                <div class="text-red-500 text-[10px] mb-1 ml-2">{{ $message }}</div> 
            @enderror

            {{-- Preview File yang akan diupload --}}
            @if($attachment)
                <div class="mb-2 flex items-center gap-2 bg-blue-50 p-2 rounded text-xs text-blue-800 border border-blue-100 animate-fade-in">
                    <i class="fas fa-paperclip"></i>
                    <span class="truncate max-w-[150px]">{{ $attachment->getClientOriginalName() }}</span>
                    <button wire:click="$set('attachment', null)" class="ml-auto text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <form wire:submit.prevent="sendMessage" class="flex items-end gap-2 relative">
                
                {{-- Tombol Attach File --}}
                <div>
                    <input type="file" wire:model="attachment" id="fileInput" class="hidden">
                    <button type="button" onclick="document.getElementById('fileInput').click()" 
                        class="p-2 text-gray-400 hover:text-blue-600 transition rounded-full hover:bg-gray-100 focus:outline-none active:bg-gray-200">
                        <i class="fas fa-paperclip"></i>
                    </button>
                </div>

                {{-- Input Teks --}}
                <div class="flex-1 relative">
                    <textarea wire:model="messageText" rows="1" placeholder="Tulis pesan..." 
                        class="w-full text-sm border-gray-200 focus:border-blue-500 focus:ring-blue-500 rounded-2xl px-4 py-2 resize-none bg-gray-50 focus:bg-white transition"
                        style="min-height: 40px; max-height: 100px;"></textarea>
                </div>

                {{-- Tombol Kirim --}}
                <button type="submit" class="p-2 bg-blue-600 text-white rounded-full w-10 h-10 flex items-center justify-center shadow-lg hover:bg-blue-700 hover:scale-105 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                    @if(!$messageText && !$attachment) disabled @endif>
                    
                    <span wire:loading.remove wire:target="sendMessage">
                        <i class="fas fa-paper-plane text-sm ml-0.5"></i>
                    </span>
                    <span wire:loading wire:target="sendMessage">
                        <i class="fas fa-spinner fa-spin text-xs"></i>
                    </span>
                </button>
            </form>
            
            {{-- Loading Indicator saat upload file --}}
            <div wire:loading wire:target="attachment" class="text-[10px] text-blue-500 mt-1 pl-2">
                <i class="fas fa-spinner fa-spin"></i> Sedang mengupload file...
            </div>
        </div>
    </div>
    @endif

    {{-- LIST USER WINDOW --}}
    @if($showChatList && !$selectedUserId)
    <div class="bg-white w-80 h-[28rem] shadow-2xl rounded-xl border border-gray-200 flex flex-col mb-4 ring-1 ring-black/5 overflow-hidden animate-fade-in-up">
        <div class="bg-gray-900 p-4 flex justify-between items-center text-white">
            <h3 class="font-bold text-lg">Pesan</h3>
            <button wire:click="toggleChatList" class="hover:text-gray-300 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-3 border-b bg-gray-50">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                <input wire:model.live="search" type="text" placeholder="Cari teman..." class="w-full pl-8 pr-4 py-2 text-xs rounded-lg border-gray-200 focus:border-blue-500 focus:ring-blue-500 bg-white shadow-sm">
            </div>
        </div>

        <div class="flex-1 overflow-y-auto" wire:poll.5000ms>
            @forelse($users as $user)
                <div wire:click="selectUser({{ $user->id }})" class="group flex items-center p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-50 transition relative">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 text-blue-600 flex items-center justify-center font-bold text-sm shadow-sm group-hover:scale-105 transition">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 {{ $user->isOnline() ? 'bg-green-500' : 'bg-gray-400' }} border-2 border-white rounded-full"></span>
                    </div>
                    
                    <div class="ml-3 flex-1 min-w-0">
                        <div class="flex justify-between items-center mb-0.5">
                            <p class="text-sm font-semibold text-gray-800 truncate group-hover:text-blue-700">{{ Str::limit($user->name, 15) }}</p>
                        </div>
                        <p class="text-xs text-gray-500 truncate">{{ $user->jabatan->nama ?? 'User' }}</p>
                    </div>

                    {{-- Notifikasi Badge Per User --}}
                    @if($user->unread_count > 0)
                        <div class="ml-2 bg-red-500 text-white text-[10px] font-bold h-5 min-w-[1.25rem] px-1 flex items-center justify-center rounded-full shadow-sm animate-pulse">
                            {{ $user->unread_count }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-6 text-center text-gray-400 text-xs">
                    Tidak ada user ditemukan.
                </div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- TOMBOL UTAMA (FLOATING BUTTON) --}}
    @if(!$selectedUserId && !$showChatList)
        <button wire:click="toggleChatList" class="group relative bg-blue-600 hover:bg-blue-700 text-white w-14 h-14 rounded-full shadow-xl flex items-center justify-center text-2xl transition-all transform hover:scale-110 hover:rotate-3 active:scale-95 focus:outline-none ring-4 ring-blue-600/20">
            <i class="fas fa-comments"></i>
            
            {{-- LINGKARAN NOTIFIKASI DI LUAR TOMBOL UTAMA --}}
            @if($totalUnreadGlobal > 0)
                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold w-6 h-6 flex items-center justify-center rounded-full border-2 border-white shadow-md animate-bounce">
                    {{ $totalUnreadGlobal }}
                </span>
            @endif
        </button>
    @endif

    <script>
        // Auto scroll ke bawah
        document.addEventListener('livewire:initialized', () => {
            const scrollToBottom = () => {
                const chatContainer = document.getElementById('chat-container');
                if(chatContainer) {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            };

            Livewire.hook('morph.updated', ({ el, component }) => {
                scrollToBottom();
            });
            
            // Scroll saat pertama kali load jika chat window terbuka
            scrollToBottom();
        });
    </script>
</div>