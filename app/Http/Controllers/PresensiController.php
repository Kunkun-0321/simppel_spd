<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Apel;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresensiController extends Controller
{
    public function storeScan(Request $request, $apel_id){
        $request->validate([
            'nim' => 'required|exists:mahasiswas,nim',
        ]);

        $presensi = Presensi::where('apel_id', $apel_id)
                            ->where('nim', $request->nim)
                            ->first();

        if (!$presensi) {
            return response()->json(['message' => 'Presensi not found.'], 404);
        }

        if ($presensi->status === 'hadir') {
            return response()->json(['message' => 'Mahasiswa sudah hadir.'], 400);
        }

        if ($waktu_apel = Apel::find($apel_id)->waktu_apel) {
            $current_time = now();
            if ($current_time != $waktu_apel){
                $presensi->status = 'terlambat';
                $presensi->nama_petugas = Auth::user()->name;
                $presensi->save();
                return redirect()->back()->with('message', 'Presensi terlambat dicatat.');
            }
        }

        $presensi->status = 'hadir';
        $presensi->nama_petugas = Auth::user()->name;
        $presensi->save();

        return redirect()->back()->with('message', 'Presensi berhasil dicatat.');  
    }

    public function reportIndex(Request $request) {
        $apel_id = $request->query('apel_id');
        $search = $request->query('search');

        $query = Presensi::with('apel', 'mahasiswa')
                        ->where('apel_id', $apel_id);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nim', 'like', "%$search%")
                  ->orWhere('nama', 'like', "%$search%")
                  ->orWhere('kelas', 'like', "%$search%");
            });
        }

        $presensis = $query->orderBy('nim')->paginate(10)->appends(['search' => $search]);

        return view('presensi.report', compact('presensis'));
    }

    public function pencatatanIndex(){
        $apels = Apel::orderBy('tanggal_apel', 'desc')->get();
        return view('presensi.petugas.index', compact('apels'));
    }

    public function scanPage($apel_id){
        $apel = Apel::findOrFail($apel_id);
        $recentScans = Presensi::where('apel_id', $apel_id)
                            ->where('status', 'hadir')
                            ->orderBy('updated_at', 'desc')
                            ->take(5)
                            ->get();
        return view('presensi.petugas.scan', compact('apel', 'recentScans'));
    }

    public function updateStatusInline(Request $request, $id) {
        $request->validate([
            'status' => 'required|in:hadir,izin,kurang_cukup_bukti_izin,sakit,kurang_cukup_bukti_sakit,tidak_hadir',
        ]);

        $presensi = Presensi::findOrFail($id);
        $presensi->status = $request->status;
        $presensi->nama_petugas = Auth::user()->name;
        $presensi->save();

        return response()->json(['message' => 'Status presensi berhasil diperbarui.']);
    }

    public function destroy($id) {
        $presensi = Presensi::findOrFail($id);
        $presensi->delete();

        return response()->json(['message' => 'Presensi berhasil dihapus.']);
    }
}
