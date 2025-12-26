<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PetugasUkur;
use App\Models\Kecamatan;
use App\Models\User;

class PilihPetugasUkurModal extends Component
{
    public bool $showModal = false;
    public ?int $berkasId = null; // Untuk menyimpan ID berkas jika diperlukan nanti
    public ?int $kecamatanId = null;
    public string $search = '';

    protected $listeners = ['openModal'];

    /**
     * Membuka modal dan menerima data dari event.
     */
    public function openModal($data = [])
    {
        $this->berkasId = $data['berkasId'] ?? null;
        $this->kecamatanId = $data['kecamatanId'] ?? null;
        $this->showModal = true;
    }

    /**
     * Menutup modal dan mereset properti.
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['berkasId', 'kecamatanId', 'search']);
    }

    /**
     * Aksi saat seorang petugas dipilih dari daftar.
     * Mengirim event kembali ke JavaScript di halaman.
     */
    class PilihPetugasUkurModal extends Component
    {
        // Wajib public agar bisa dibaca di view/modal
        public $petugas; 
        public $selectedPetugasId;
        
        public function mount()
        {
            // Isi data awal jika perlu
            $this->petugas = User::where('jabatan', 'Petugas Ukur')->get();
        }

        public function render()
        {
            return view('livewire.pilih-petugas-ukur-modal');
        }
    }

    /**
     * Merender komponen dan mengambil data petugas.
     */
    public function render()
    {
        $query = PetugasUkur::with(['user.jabatan', 'areaKerja'])
                    ->withCount('jadwalUkur') // Menghitung total jadwal
                    ->whereHas('user', function ($q) {
                        // Filter berdasarkan pencarian nama user
                        $q->where('name', 'like', '%'.$this->search.'%');
                    });

        // Jika kecamatan dipilih di form, prioritaskan petugas yang memiliki area kerja di sana
        if ($this->kecamatanId) {
            $query->orderByRaw('EXISTS (SELECT 1 FROM area_kerja_petugas_ukur WHERE petugas_ukur_id = petugas_ukur.id AND kecamatan_id = ?) DESC', [$this->kecamatanId]);
        }

        $semuaPetugas = $query->get();

        return view('livewire.pilih-petugas-ukur-modal', [
            'semuaPetugas' => $semuaPetugas
        ]);
    }
}

