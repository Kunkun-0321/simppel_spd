<x-layout></x-layout>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

{{-- <style>
    .custom-alert {
        position: fixed;
        top: 40px;
        left: 50%;
        /* Posisi horizontal di tengah */
        transform: translateX(-50%);
        /* Pindahkan setengah dari lebar elemen */
        z-index: 1050;
        /* Pastikan di atas elemen lain */
        padding: 15px;
        background-color: #d1e7dd;
        color: #0f5132;
        border: 1px solid #badbcc;
        border-radius: 5px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        max-width: 100%;
        /* Sesuaikan dengan lebar layar, maksimal 90% */
        text-align: center;
        box-sizing: border-box;
        /* Pastikan padding dihitung dalam lebar elemen */
    }
</style> --}}

{{-- @if (session('success'))
    <div id="successAlert" class="custom-alert">
        {{ session('success') }}
    </div>
@endif --}}

{{-- <script>
    // Menghilangkan pesan setelah 3 detik
    setTimeout(() => {
        const alertBox = document.getElementById('successAlert');
        if (alertBox) {
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 500); // Hapus elemen setelah animasi
        }
    }, 3000);
</script> --}}

<div class="p-4 sm:ml-64 mt-5">
    <!-- Container untuk Greeting dan Quotes -->
    <div id="greeting-quote-container" class="text-left flex flex-col items-start mb-6">
        <!-- Greeting -->
        <h2 id="greeting" class="text-4xl font-extrabold text-gray-900 dark:text-white mb-2">
            Selamat
            datang,
            @if (Auth::user()->role === 'admin')
                {{ \App\Models\Admin::where('admin_id', Auth::user()->username)->value('nama_admin') ?? 'Admin Tidak Ditemukan' }}
            @elseif (Auth::user()->role === 'spd')
                {{ \App\Models\SPD::where('nas', Auth::user()->username)->value('nama_anggota') ?? 'Anggota SPD Tidak Ditemukan' }}
            @elseif (Auth::user()->role === 'pemonitor')
                {{ \App\Models\Pemonitor::where('pemonitor_id', Auth::user()->username)->value('nama_pemonitor') ?? 'Pemonitor Tidak Ditemukan' }}
            @else
                Peran tidak dikenali
            @endif

        </h2>
        <!-- Quotes -->
        <div id="quote-container" class="text-gray-500">
            <p class="text-lg italic font-medium">
                "Memuat quotes..."
            </p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Filter Data Operasi -->
        <div class="mb-6 bg-gray-50 border rounded-lg p-4 shadow-md">
            <label for="filter" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Pilih Data
                Operasi:</label>
            <select id="filter"
                class="mt-2 block w-full py-2 px-4 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="all">Semua Operasi</option>
                <option value="operasi_rutin">Operasi Rutin</option>
                <option value="operasi_umums">Operasi Umum</option>
                <option value="penindakan_harian">Penindakan Harian</option>
            </select>
        </div>

        <!-- Filter Waktu -->
        <div class="mb-6 bg-gray-50 border rounded-lg p-4 shadow-md">
            <label for="time-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Filter
                Waktu:</label>
            <select id="time-filter"
                class="mt-2 block w-full py-2 px-4 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="">Semua Waktu</option>
                <option value="today">Hari Ini</option>
                <option value="this_month">Bulan Ini</option>
                <option value="this_year">Tahun Ini</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Bar Chart Section -->
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200 mb-4">Laporan Pelanggaran - Bar Chart
            </h2>
            <div id="bar-chart-container">
                <canvas id="bar-chart" class="rounded shadow-md" style="max-height: 300px;"></canvas>
                <p id="bar-chart-empty" class="text-center text-gray-500 hidden">Tidak ada data untuk ditampilkan.</p>
            </div>
        </div>

        <!-- Pie Chart Section -->
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200 mb-4">Laporan Pelanggaran - Pie Chart
            </h2>
            <div id="pie-chart-container">
                <canvas id="pie-chart" class="rounded shadow-md" style="max-height: 300px;"></canvas>
                <p id="pie-chart-empty" class="text-center text-gray-500 hidden">Tidak ada data untuk ditampilkan.</p>
            </div>
        </div>
    </div>

    <script>
        const filterElement = document.getElementById('filter');
        const timeFilterElement = document.getElementById('time-filter');
        let barChartInstance = null;
        let pieChartInstance = null;

        // Fungsi untuk memuat quotes
        function fetchQuote() {
            fetch(`/dashboard/data`) // Endpoint untuk data quotes
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const quoteContainer = document.getElementById('quote-container');
                    quoteContainer.innerHTML = `
                    <p class="text-lg italic font-medium">
                        "${data.quote}"
                    </p>
                `;
                })
                .catch(error => {
                    console.error("Error fetching quote:", error);
                    const quoteContainer = document.getElementById('quote-container');
                    quoteContainer.innerHTML = `
                    <p class="text-lg italic font-medium text-red-500">
                        "Tidak dapat memuat quotes. Silakan coba lagi nanti."
                    </p>
                `;
                });
        }

        function fetchAndRenderCharts(filter, timeFilter) {
            fetch(`/dashboard/data?filter=${filter}&time_filter=${timeFilter}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    fetchQuote(); // Panggil fetchQuote untuk memperbarui quotes setiap refresh

                    // Mengelompokkan data untuk Bar Chart
                    const groupedBarData = data.barChartData.reduce((acc, item) => {
                        acc[item.pelanggaran] = (acc[item.pelanggaran] || 0) + item.total;
                        return acc;
                    }, {});

                    const barChartLabels = Object.keys(groupedBarData);
                    const barChartValues = Object.values(groupedBarData);

                    if (barChartInstance) {
                        barChartInstance.destroy();
                    }

                    if (barChartLabels.length === 0) {
                        document.getElementById("bar-chart").style.display = "none";
                        document.getElementById("bar-chart-empty").classList.remove("hidden");
                    } else {
                        document.getElementById("bar-chart").style.display = "block";
                        document.getElementById("bar-chart-empty").classList.add("hidden");

                        const barChartCtx = document.getElementById("bar-chart").getContext("2d");
                        barChartInstance = new Chart(barChartCtx, {
                            type: "bar",
                            data: {
                                labels: barChartLabels,
                                datasets: [{
                                    label: "Jumlah Pelanggaran",
                                    data: barChartValues,
                                    backgroundColor: "rgba(54, 162, 235, 0.6)",
                                    borderColor: "rgba(54, 162, 235, 1)",
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1,
                                            callback: function(value) {
                                                return Number.isInteger(value) ? value : null;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    const pieChartLabels = data.pieChartData.map(item => `Tingkat ${item.tingkat}`);
                    const pieChartValues = data.pieChartData.map(item => item.total);

                    if (pieChartInstance) {
                        pieChartInstance.destroy();
                    }

                    if (pieChartLabels.length === 0) {
                        document.getElementById("pie-chart").style.display = "none";
                        document.getElementById("pie-chart-empty").classList.remove("hidden");
                    } else {
                        document.getElementById("pie-chart").style.display = "block";
                        document.getElementById("pie-chart-empty").classList.add("hidden");

                        const pieChartCtx = document.getElementById("pie-chart").getContext("2d");
                        pieChartInstance = new Chart(pieChartCtx, {
                            type: "pie",
                            data: {
                                labels: pieChartLabels,
                                datasets: [{
                                    data: pieChartValues,
                                    backgroundColor: [
                                        "rgba(75, 192, 192, 0.6)",
                                        "rgba(255, 99, 132, 0.6)",
                                        "rgba(255, 206, 86, 0.6)",
                                        "rgba(153, 102, 255, 0.6)"
                                    ]
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: "right"
                                    }
                                }
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                    alert("Terjadi kesalahan saat memuat data. Silakan coba lagi.");
                });
        }

        filterElement.addEventListener('change', () => {
            const filterValue = filterElement.value;
            const timeFilterValue = timeFilterElement.value;
            fetchAndRenderCharts(filterValue, timeFilterValue);
        });

        timeFilterElement.addEventListener('change', () => {
            const filterValue = filterElement.value;
            const timeFilterValue = timeFilterElement.value;
            fetchAndRenderCharts(filterValue, timeFilterValue);
        });

        // Fetch initial data
        fetchAndRenderCharts('all', '');

        function updateGreeting() {
            const greetingElement = document.getElementById('greeting');
            const now = new Date();
            const hours = now.getHours();

            let greetingMessage = "";

            if (hours >= 5 && hours < 12) {
                greetingMessage = "Selamat Pagi";
            } else if (hours >= 12 && hours < 15) {
                greetingMessage = "Selamat Siang";
            } else if (hours >= 15 && hours < 18) {
                greetingMessage = "Selamat Sore";
            } else {
                greetingMessage = "Selamat Malam";
            }

            let userRole = "{{ Auth::user()->role }}";
            let userName = "Peran tidak dikenali"; // Default jika peran tidak ditemukan

            if (userRole === "admin") {
                userName =
                    `{{ \App\Models\Admin::where('admin_id', Auth::user()->username)->value('nama_admin') ?? 'Admin Tidak Ditemukan' }}`;
            } else if (userRole === "spd") {
                userName =
                    `{{ \App\Models\SPD::where('nas', Auth::user()->username)->value('nama_anggota') ?? 'Anggota SPD Tidak Ditemukan' }}`;
            } else if (userRole === "pemonitor") {
                userName =
                    `{{ \App\Models\Pemonitor::where('pemonitor_id', Auth::user()->username)->value('nama_pemonitor') ?? 'Pemonitor Tidak Ditemukan' }}`;
            }

            greetingElement.innerHTML = `${greetingMessage}, ${userName}`;
        }

        // Panggil fungsi updateGreeting saat halaman dimuat
        updateGreeting();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    title: '{{ session('success') }}',
                    // text: '{{ session('success') }}', // Pesan dari session flash
                    icon: 'success',
                    timer: 2000, // Popup otomatis hilang dalam 3 detik
                    showConfirmButton: false, // Tidak ada tombol "OK"
                    allowOutsideClick: false, // Tidak bisa klik di luar
                    allowEscapeKey: false // Tidak bisa tutup dengan ESC
                });
            @endif
        });
    </script>


</div>

<x-footer></x-footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
