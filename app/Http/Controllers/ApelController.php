<?php

namespace App\Http\Controllers;

use App\Models\Apel;
use Illuminate\Http\Request;

class ApelController extends Controller
{
    public function index()
    {
        $apels = Apel::orderBy('tanggal_apel', 'desc')->get();
        return view('admin.apel.index', compact('apels'));
    }

    public function create()
    {
        return view('admin.apel.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_apel' => 'required|string|max:255',
            'tingkat' => 'required|integer|between:0,4',
            'tanggal_apel' => 'required|date',
        ]);

        Apel::create($request->all());

        $query = Mahasiswa::query();
        if ($request->tingkat >= 0 && $request->tingkat <= 4) {
            $query->where('tingkat', $request->tingkat);
        }
        $mahasiswas = $query->get();
        
        foreach ($mahasiswas as $mahasiswa) {
            Absensi::create([
                'apel_id' => Apel::latest()->first()->id,
                'nim' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
                'status' => 'tidak_hadir',
            ]);
        }

        return redirect()->route('admin.apel.index')->with('success', 'Apel created successfully.');
    }

    public function edit($id)
    {
        $apel = Apel::findOrFail($id);
        return view('admin.apel.edit', compact('apel'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_apel' => 'required|string|max:255',
            'tingkat' => 'required|integer|between:0,4',
            'tanggal_apel' => 'required|date',
        ]);

        $apel = Apel::findOrFail($id);
        $apel->update($request->all());

        return redirect()->route('admin.apel.index')->with('success', 'Apel updated successfully.');
    }

    public function destroy($id)
    {
        $apel = Apel::findOrFail($id);
        $apel->delete();

        return redirect()->route('admin.apel.index')->with('success', 'Apel deleted successfully.');
    }   
}
