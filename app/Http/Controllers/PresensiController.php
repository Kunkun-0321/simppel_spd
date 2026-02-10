<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Apel;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresensiController extends Controller
{
    public function show($apel_id)
    {
        $apel = Apel::findOrFail($apel_id);
        $recentPresensis = Presensi::where('apel_id', $apel_id)
            ->where('status', 'hadir')
            ->with('mahasiswa')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.presensi.show', compact('apel', 'recentPresensis'));
    }

    public function updateStatus(Request $request, $apel_id, $nim)
    {
        $request->validate([
            'status' => 'required|in:hadir,izin,sakit,tidak_hadir',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $presensi = Presensi::where('apel_id', $apel_id)
            ->where('nim', $nim)
            ->firstOrFail();

        $presensi->status = $request->status;
        $presensi->keterangan = $request->keterangan;
        $presensi->nama_petugas = Auth::user()->name;
        $presensi->save();

        return redirect()->back()->with('success', 'Presensi updated successfully.');
    }

    public function showAlfas($apel_id)
    {
        $apel = Apel::findOrFail($apel_id);
        $alfas = Presensi::where('apel_id', $apel_id)
            ->where('status', 'tidak_hadir')
            ->with('mahasiswa')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.presensi.alfas', compact('apel', 'alfas'));
    }
}
