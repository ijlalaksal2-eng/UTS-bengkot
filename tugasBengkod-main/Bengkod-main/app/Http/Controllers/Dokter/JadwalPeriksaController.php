<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class JadwalPeriksaController extends Controller
{
    public function index()
    {
        $dokter = Auth::user();
        $jadwalPeriksas = JadwalPeriksa::where('id_dokter', $dokter->id)->orderBy('hari')->get();

        return view('dokter.jadwal-periksa.index', compact('jadwalPeriksas'));
    }

    public function create()
    {
        return view('dokter.jadwal-periksa.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateSchedule($request);
        $data['id_dokter'] = Auth::id();

        JadwalPeriksa::create($data);

        return redirect()->route('jadwal-periksa.index')
            ->with('message', 'Data Berhasil di Simpan')
            ->with('type', 'success');
    }

    public function edit(string $id)
    {
        $jadwalPeriksa = $this->findForCurrentDoctor($id);

        return view('dokter.jadwal-periksa.edit', compact('jadwalPeriksa'));
    }

    public function update(Request $request, string $id)
    {
        $data = $this->validateSchedule($request);
        $jadwalPeriksa = $this->findForCurrentDoctor($id);
        $jadwalPeriksa->update($data);

        return redirect()->route('jadwal-periksa.index')
            ->with('message', 'Berhasil Melakukan Update Data')
            ->with('type', 'success');
    }

    public function destroy(string $id)
    {
        $jadwalPeriksa = $this->findForCurrentDoctor($id);
        $jadwalPeriksa->delete();

        return redirect()->route('jadwal-periksa.index')
            ->with('message', 'Berhasil Melakukan Hapus Data')
            ->with('type', 'success');
    }

    private function validateSchedule(Request $request): array
    {
        return $request->validate([
            'hari' => ['required', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ]);
    }

    private function findForCurrentDoctor(string $id): JadwalPeriksa
    {
        return JadwalPeriksa::where('id_dokter', Auth::id())->findOrFail($id);
    }
}