<x-layout>
    <div class="p-4 sm:ml-64 mt-9">
        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="scanTab" data-tabs-toggle="#scanTabContent" role="tablist">
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="cam-tab" data-tabs-target="#cam" type="button" role="tab">Kamera Scanner</button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="res-tab" data-tabs-target="#res" type="button" role="tab">Hasil Scan Anda</button>
                </li>
            </ul>
        </div>
        
        <div id="scanTabContent">
            <div class="hidden p-4 rounded-lg bg-yellow-300 shadow-xl" id="cam" role="tabpanel">
                <div id="reader" class="mx-auto w-full max-w-sm bg-white rounded-xl overflow-hidden shadow-inner"></div>
                <div id="status-text" class="text-center mt-4 font-bold text-gray-800 animate-pulse">Menunggu QR Code...</div>
                
                <form id="form-scan" action="{{ route('presensi.store', $apel->id) }}" method="POST" class="hidden">
                    @csrf
                    <input type="hidden" name="nim" id="scan_nim">
                    <input type="hidden" name="apel_id" value="{{ $apel->id }}">
                </form>
            </div>

            <div class="hidden p-4 rounded-lg bg-white border" id="res" role="tabpanel">
                <h3 class="font-bold text-blue-700 mb-4">Mahasiswa yang Baru Anda Scan:</h3>
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr><th class="px-4 py-2">Waktu</th><th class="px-4 py-2">NIM</th><th class="px-4 py-2">Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($recentScans as $s)
                            <tr class="border-b">
                                <td class="px-4 py-2 font-mono text-xs">{{ $s->updated_at->format('H:i:s') }}</td>
                                <td class="px-4 py-2 font-bold">{{ $s->nim }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black {{ $s->status == 'hadir' ? 'bg-green-100 text-green-700' : ($s->status == 'terlambat' ? 'bg-yellow-100 text-yellow-700' : 'bg-orange-100 text-orange-700') }}">
                                        {{ strtoupper($s->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const html5QrCode = new Html5Qrcode("reader");
            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                // Berikan feedback visual sederhana
                document.getElementById('status-text').innerText = "NIM Terdeteksi: " + decodedText;
                document.getElementById('scan_nim').value = decodedText;
                
                // Hentikan kamera sebelum pindah halaman agar tidak crash di RAM 8GB Anda
                html5QrCode.stop().then((ignore) => {
                    document.getElementById('form-scan').submit();
                });
            };

            const config = { fps: 10, qrbox: { width: 250, height: 250 } };

            // Memulai kamera belakang secara otomatis
            html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
                .catch((err) => {
                    console.error("Gagal start kamera: ", err);
                    document.getElementById('status-text').innerText = "Kamera Gagal Aktif: Pastikan izin diberikan & gunakan HTTPS/Localhost";
                    document.getElementById('status-text').classList.add('text-red-600');
                });
        });
    </script>
</x-layout>