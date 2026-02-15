<x-layout>
    <div class="p-4 sm:ml-64 mt-9">
        <h2 class="text-2xl font-bold mb-6">Pelaporan Presensi Mahasiswa</h2>

        <div class="bg-white p-5 rounded-xl border shadow-sm mb-6 flex flex-wrap gap-4 items-end">
            <form action="{{ route('presensi.report') }}" method="GET" class="flex flex-wrap gap-2 w-full">
                <div class="flex flex-col">
                    <label class="text-xs font-bold mb-1">Pilih Tanggal</label>
                    <input type="date" name="tanggal" class="rounded-lg border-gray-300 text-sm" value="{{ request('tanggal') }}">
                </div>
                <div class="flex flex-col">
                    <label class="text-xs font-bold mb-1">Pilih Tingkat</label>
                    <select name="tingkat" class="rounded-lg border-gray-300 text-sm">
                        <option value="">Semua</option>
                        @foreach([1,2,3,4] as $t) <option value="{{ $t }}" {{ request('tingkat') == $t ? 'selected' : '' }}>Tingkat {{ $t }}</option> @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-blue-700 text-white font-bold px-6 py-2 rounded-lg text-sm mb-[2px]">Filter Data</button>
            </form>
        </div>

        <div class="overflow-x-auto shadow-md rounded-lg border bg-white">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">Hari/Tanggal</th><th class="px-4 py-3">NIM</th><th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Status</th><th class="px-4 py-3">Petugas Scanner</th>
                        @if(Auth::user()->role == 'admin') 
                        <th class="px-4 py-3 text-center">Aksi</th> @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($presensis as $r)
                    <tr class="border-b hover:bg-gray-50 text-gray-900">
                        <td class="px-4 py-4 text-xs font-semibold">{{ $r->apel->tanggal_apel }}</td>
                        <td class="px-4 py-4 font-mono text-blue-700">{{ $r->nim }}</td>
                        <td class="px-4 py-4 font-bold uppercase">{{ $r->nama }}</td>
                        <td class="px-4 py-4">
                            @if(Auth::user()->role == 'admin')
                                <form action="{{ route('admin.presensi.update_status', $r->id) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="text-[10px] font-black rounded-lg border-gray-300 py-1 bg-gray-50">
                                        @foreach(['hadir', 'tidak_hadir', 'terlambat', 'izin', 'kurang_cukup_bukti_izin', 'sakit', 'kurang_cukup_bukti_sakit'] as $st)
                                            <option value="{{ $st }}" {{ $r->status == $st ? 'selected' : '' }}>{{ strtoupper(str_replace('_', ' ', $st)) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @else
                                <span class="font-black {{ $r->status == 'hadir' ? 'text-green-600' : ($r->status == 'terlambat' ? 'text-yellow-600' : 'text-red-600') }}">{{ strtoupper($r->status) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 italic text-[10px] text-gray-400">{{ $r->nama_petugas ?? 'Sistem' }}</td>
                        @if(Auth::user()->role == 'admin')
                        <td class="px-4 py-4 text-center">
                            <form action="{{ route('admin.presensi.delete', $r->id) }}" method="POST" onsubmit="return confirm('Hapus record ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 font-bold hover:underline text-xs">Hapus</button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layout>