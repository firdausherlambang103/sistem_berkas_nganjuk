<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads; // Import ini untuk handle file
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatBox extends Component
{
    use WithFileUploads; // Gunakan trait ini

    public $search = '';
    public $selectedUserId = null;
    public $messageText = '';
    public $attachment = null; // Property untuk file
    public $showChatList = false;

    // ... (Fungsi getUsersProperty tetap sama) ...
    public function getUsersProperty()
    {
        return User::where('id', '!=', Auth::id())
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->get()
            ->map(function($user) {
                $user->online = $user->isOnline();
                $user->unread_count = Message::where('sender_id', $user->id)
                    ->where('receiver_id', Auth::id())
                    ->where('is_read', false)
                    ->count();
                return $user;
            })
            ->sortByDesc('online'); 
    }

    // ... (Fungsi selectUser, closeChat, toggleChatList tetap sama) ...
    public function selectUser($userId)
    {
        $this->selectedUserId = $userId;
        $this->markAsRead();
    }

    public function closeChat()
    {
        $this->selectedUserId = null;
        $this->showChatList = false;
    }

    public function toggleChatList()
    {
        $this->showChatList = !$this->showChatList;
    }

    public function sendMessage()
    {
        $this->validate([
            'messageText' => 'required_without:attachment|nullable|string|max:1000',
            'attachment' => 'nullable|file|max:10240', // Max 10MB
        ]);

        if ($this->selectedUserId) {
            $data = [
                'sender_id' => Auth::id(),
                'receiver_id' => $this->selectedUserId,
                'message' => $this->messageText ?? '', // Bisa kosong jika kirim file saja
            ];

            // Handle File Upload
            if ($this->attachment) {
                $path = $this->attachment->store('chat-attachments', 'public');
                $data['attachment'] = $path;
                $data['attachment_original_name'] = $this->attachment->getClientOriginalName();
            }

            Message::create($data);

            // Reset Input
            $this->messageText = '';
            $this->attachment = null;
        }
    }

    public function markAsRead()
    {
        if ($this->selectedUserId) {
            Message::where('sender_id', $this->selectedUserId)
                ->where('receiver_id', Auth::id())
                ->update(['is_read' => true]);
        }
    }

    public function render()
    {
        $messages = [];
        $selectedUser = null;

        // Hitung total pesan belum terbaca untuk badge tombol utama
        $totalUnread = Message::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();

        if ($this->selectedUserId) {
            $selectedUser = User::find($this->selectedUserId);
            
            $messages = Message::where(function($q) {
                    $q->where('sender_id', Auth::id())
                      ->where('receiver_id', $this->selectedUserId);
                })
                ->orWhere(function($q) {
                    $q->where('sender_id', $this->selectedUserId)
                      ->where('receiver_id', Auth::id());
                })
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('livewire.chat-box', [
            'users' => $this->getUsersProperty(),
            'currentMessages' => $messages,
            'activeUser' => $selectedUser,
            'totalUnreadGlobal' => $totalUnread // Kirim variabel ini ke view
        ]);
    }
}