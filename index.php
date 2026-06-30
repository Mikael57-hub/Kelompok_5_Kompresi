<?php
// Koneksi ke Database
$conn = new mysqli("localhost", "root", "", "db_arsip_digital");
if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

// 1. Ambil data metrik total akumulasi untuk widget dashboard
$stats_query = "SELECT 
                    COUNT(*) as total_berkas, 
                    SUM(ukuran_awal_kb) as total_awal, 
                    SUM(ukuran_akhir_kb) as total_akhir,
                    AVG(rasio_kompresi) as avg_cr
                FROM berkas_media";
$stats_res = $conn->query($stats_query)->fetch_assoc();

$total_berkas = $stats_res['total_berkas'] ?? 0;
$total_saving_mb = 0;
if ($total_berkas > 0) {
    $total_saving_mb = ($stats_res['total_awal'] - $stats_res['total_akhir']) / 1024; // Konversi ke MB
}
$avg_space_saving = 0;
if (($stats_res['total_awal'] ?? 0) > 0) {
    $avg_space_saving = (1 - ($stats_res['total_akhir'] / $stats_res['total_awal'])) * 100;
}

// 2. Ambil daftar semua berkas
$query = "SELECT * FROM berkas_media ORDER BY waktu_upload DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ArsipDigital - Dashboard Media Compressor Suite</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #020617; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #10b981; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 font-sans min-h-screen flex overflow-hidden">

    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col justify-between hidden md:flex">
        <div>
            <div class="p-6 border-b border-slate-800 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                    <i class="fa-solid fa-box-archive text-slate-950 font-bold"></i>
                </div>
                <div>
                    <h2 class="font-bold text-sm tracking-wide text-slate-100">ArsipDigital</h2>
                    <span class="text-[10px] text-emerald-400 font-semibold tracking-widest uppercase">ISB Studio</span>
                </div>
            </div>
            <nav class="p-4 space-y-1.5" id="sidebar-nav">
                <button onclick="switchMenu('dashboard', this)" class="nav-btn w-full flex items-center gap-3 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500/10 to-teal-500/5 border border-emerald-500/20 text-emerald-400 font-medium text-xs transition text-left">
                    <i class="fa-solid fa-chart-pie text-sm"></i> Dashboard Konsol
                </button>
                <button onclick="switchMenu('manager', this)" class="nav-btn w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-slate-200 text-xs transition text-left">
                    <i class="fa-solid fa-folder-open text-sm"></i> Manajer Berkas
                </button>
                <button onclick="switchMenu('security', this)" class="nav-btn w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-slate-200 text-xs transition text-left">
                    <i class="fa-solid fa-shield-halved text-sm"></i> Gerbang Proteksi
                </button>
                <button onclick="switchMenu('settings', this)" class="nav-btn w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-slate-200 text-xs transition text-left">
                    <i class="fa-solid fa-gear text-sm"></i> Pengaturan Sistem
                </button>
            </nav>
        </div>
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            <div class="flex items-center gap-2.5 px-2 py-1.5">
                <div class="w-7 h-7 rounded-full bg-slate-800 flex items-center justify-center text-[10px] font-bold text-slate-300 border border-slate-700">M</div>
                <div class="truncate">
                    <p class="text-[11px] font-bold text-slate-300 truncate">Mikael & Oktaria</p>
                    <p class="text-[9px] text-slate-500 truncate">S1 Teknologi Informasi</p>
                </div>
            </div>
        </div>
    </aside>

    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <header class="h-16 border-b border-slate-800 bg-slate-900/40 backdrop-blur px-6 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-4">
                <h1 class="text-sm font-bold text-slate-200" id="header-title">Dashboard Konsol</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-[10px] bg-slate-800 text-slate-400 px-3 py-1.5 rounded-full font-semibold border border-slate-700/60"><i class="fa-solid fa-university text-slate-500 mr-1"></i> Institut Shanti Bhuana</span>
            </div>
        </header>

        <div class="p-6 max-w-7xl w-full mx-auto space-y-6">

            <div id="menu-dashboard" class="menu-content space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-slate-900 border border-slate-800/80 rounded-xl p-4 shadow-md flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                            <i class="fa-solid fa-file-invoice text-lg"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider block">Total Berkas</span>
                            <span class="text-xl font-extrabold text-slate-100"><?php echo $total_berkas; ?> <span class="text-xs font-normal text-slate-500">File</span></span>
                        </div>
                    </div>
                    <div class="bg-slate-900 border border-slate-800/80 rounded-xl p-4 shadow-md flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                            <i class="fa-solid fa-hard-drive text-lg"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider block">Memori Dihemat</span>
                            <span class="text-xl font-extrabold text-emerald-400"><?php echo number_format($total_saving_mb, 2); ?> <span class="text-xs font-normal text-slate-500">MB</span></span>
                        </div>
                    </div>
                    <div class="bg-slate-900 border border-slate-800/80 rounded-xl p-4 shadow-md flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
                            <i class="fa-solid fa-chart-line text-lg"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider block">Rata-Rata Rasio (CR)</span>
                            <span class="text-xl font-extrabold text-slate-100"><?php echo number_format($stats_res['avg_cr'] ?? 0, 1); ?>x <span class="text-xs font-normal text-slate-500">Lipat</span></span>
                        </div>
                    </div>
                    <div class="bg-slate-900 border border-slate-800/80 rounded-xl p-4 shadow-md flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                            <i class="fa-solid fa-percent text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <span class="text-[10px] uppercase font-bold text-slate-500 tracking-wider block">Efisiensi Global</span>
                            <span class="text-xl font-extrabold text-amber-400"><?php echo round($avg_space_saving, 1); ?>%</span>
                            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-1 overflow-hidden">
                                <div class="bg-gradient-to-r from-amber-500 to-emerald-400 h-1.5 rounded-full" style="width: <?php echo $avg_space_saving; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <div class="lg:col-span-4 space-y-4">
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-xl">
                            <h2 class="text-sm font-bold mb-4 flex items-center gap-2 text-emerald-400">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i> Modul Eksekusi Kompresi
                            </h2>
                            <form action="upload.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                                <div>
                                    <div class="border-2 border-dashed border-slate-700 hover:border-emerald-500 rounded-xl p-5 text-center cursor-pointer transition relative group bg-slate-950/40">
                                        <input type="file" name="media_file" id="media_file" required class="absolute inset-0 opacity-0 cursor-pointer">
                                        <div class="space-y-2">
                                            <i class="fa-solid fa-file-shield text-2xl text-slate-500 group-hover:text-emerald-400 transition"></i>
                                            <p class="text-[11px] text-slate-400 font-medium" id="file_name_preview">Ketuk untuk telusuri berkas</p>
                                            <p class="text-[9px] text-slate-500">Mendukung: JPG, PNG, PDF, MP4</p>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 mb-2 uppercase tracking-wider">Konfigurasi Hak Akses Metadata</label>
                                    <select name="status_akses" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-emerald-500">
                                        <option value="read-only">Pratinjau Terkunci (Read-Only)</option>
                                        <option value="full-access">Akses Terbuka (Full-Access)</option>
                                    </select>
                                </div>
                                <button type="submit" name="btn_upload" class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 text-slate-950 font-black py-3 rounded-xl text-xs transition shadow-lg flex justify-center items-center gap-2">
                                    <i class="fa-solid fa-bolt"></i> OPTIMALKAN SEKARANG
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="lg:col-span-8 bg-gradient-to-br from-slate-900 to-emerald-950/10 border border-slate-800 rounded-xl p-6 shadow-xl flex flex-col justify-center min-h-[250px]">
                        <h3 class="text-sm font-bold text-slate-200 mb-2"><i class="fa-solid fa-wand-magic-sparkles text-emerald-400 mr-2"></i>Selamat Datang di Workspace v2</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">Aplikasi ini siap mengarsip dan mereduksi ukuran file media hasil syuting Anda secara cerdas. Pilih menu di sebelah kiri untuk eksplorasi lebih lanjut.</p>
                        <div class="flex gap-3">
                            <button onclick="document.getElementById('media_file').click()" class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[11px] px-4 py-2 rounded-lg font-bold hover:bg-emerald-500/20 transition">Mulai Upload File</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="menu-manager" class="menu-content space-y-6 hidden">
                <div class="bg-slate-900 border border-slate-800 rounded-xl shadow-xl overflow-hidden">
                    <div class="p-4 border-b border-slate-800 bg-slate-900/50 flex flex-col sm:flex-row justify-between items-center gap-3">
                        <div class="relative w-full sm:w-64">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-xs text-slate-500"></i>
                            <input type="text" id="search_input" onkeyup="searchTable()" placeholder="Cari arsip nama berkas..." class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-8 pr-3 py-1.5 text-xs text-slate-300 focus:outline-none focus:border-emerald-500 placeholder-slate-600">
                        </div>
                        <span class="text-[10px] bg-slate-950 text-slate-400 border border-slate-800 px-3 py-1 rounded-full font-bold">Total Terdaftar: <?php echo $result->num_rows; ?> Berkas</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="data_table">
                            <thead>
                                <tr class="bg-slate-950/80 text-slate-400 text-[10px] tracking-wider uppercase font-bold border-b border-slate-800">
                                    <th class="py-3 px-4">Metadata File</th>
                                    <th class="py-3 px-4 text-center">Kompresi Pemampatan</th>
                                    <th class="py-3 px-4 text-center">Status Gerbang</th>
                                    <th class="py-3 px-4 text-right">Opsi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 text-xs">
                                <?php if ($result->num_rows > 0): 
                                    $result->data_seek(0); // Reset pointer loop database
                                    while($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-slate-850/40 transition">
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-slate-200 max-w-[200px] truncate table-file-name"><?php echo $row['nama_file']; ?></div>
                                            <div class="text-[9px] text-slate-500 mt-0.5"><i class="fa-solid fa-file text-[8px] mr-1"></i><?php echo strtoupper(str_replace('image/', '', $row['tipe_file'])); ?></div>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <div class="text-slate-300 font-medium"><del class="text-slate-500 text-[10px] mr-1"><?php echo number_format($row['ukuran_awal_kb'], 1); ?> KB</del> ➔ <b><?php echo number_format($row['ukuran_akhir_kb'], 1); ?> KB</b></div>
                                            <div class="text-[9px] text-emerald-400 font-bold mt-0.5"><?php echo $row['rasio_kompresi']; ?>x Lipat Lebih Rapat</div>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <?php if($row['status_akses'] == 'read-only'): ?>
                                                <span class="bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2 py-0.5 rounded-md text-[9px] font-bold"><i class="fa-solid fa-lock text-[8px] mr-1"></i>Read-Only</span>
                                            <?php else: ?>
                                                <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded-md text-[9px] font-bold"><i class="fa-solid fa-lock-open text-[8px] mr-1"></i>Full Access</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <a href="cek_akses.php?id=<?php echo $row['id']; ?>" target="_blank" class="bg-slate-800 hover:bg-emerald-400 hover:text-slate-950 text-slate-300 font-extrabold py-1.5 px-3 rounded-lg text-[10px] transition">
                                                <i class="fa-solid fa-download mr-1"></i> Ambil File
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="py-12 text-center text-slate-500 italic">Belum ada file multimedia di dalam server.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="menu-security" class="menu-content space-y-6 hidden">
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-xl max-w-2xl">
                    <h2 class="text-sm font-bold text-amber-400 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved"></i> Penjelasan Logika Keamanan (Security Gateway)
                    </h2>
                    <p class="text-xs text-slate-400 leading-relaxed mb-4">
                        [cite_start]Sistem memisahkan antara file asli beresolusi tinggi dengan duplikat data kompresi[cite: 14]. [cite_start]Melalui tabel pangkalan data, apabila metadata berkas terdeteksi berstatus <span class="text-amber-400 font-bold">Read-Only</span>, skrip `cek_akses.php` secara aktif akan memblokir dan mengunci tautan *unduhan langsung* bagi pihak luar atau klien yang belum menyelesaikan administrasi pembayaran pelunasan proyek[cite: 14, 92].
                    </p>
                    <div class="bg-slate-950 p-4 border border-slate-800 rounded-lg text-[11px] text-slate-400 space-y-2">
                        <div class="flex items-center gap-2 text-slate-300 font-bold"><i class="fa-solid fa-code text-emerald-400"></i> Alur Kerja Validasi:</div>
                        <p>1. Pengguna/Klien menekan tombol [Ambil File] pada repositori.</p>
                        <p>2. [cite_start]Server membaca parameter ID berkas dan mengevaluasi kolom `status_akses`[cite: 91, 92].</p>
                        <p>3. Jika lolos (`full-access`), file ditransfer. [cite_start]Jika gagal (`read-only`), sistem melempar peringatan enkripsi aman[cite: 92].</p>
                    </div>
                </div>
            </div>

            <div id="menu-settings" class="menu-content space-y-6 hidden">
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-xl max-w-xl">
                    <h2 class="text-sm font-bold text-rose-400 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i> Zona Pemeliharaan Repositori
                    </h2>
                    <p class="text-xs text-slate-400 mb-4">Menu ini digunakan untuk melakukan pembersihan data sistem secara menyeluruh jika ruang penyimpanan harddisk lokal komputer/server sudah penuh.</p>
                    
                    <form action="upload.php" method="POST">
                        <button type="submit" name="btn_reset" onclick="return confirm('Apakah Anda yakin ingin menghapus seluruh file fisik di server dan records pangkalan data?')" class="bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-slate-950 border border-rose-500/20 font-bold text-xs py-3 px-5 rounded-xl transition flex items-center gap-2">
                            <i class="fa-solid fa-dumpster-fire"></i> KOSONGKAN RIWAYAT & SELURUH HARDDISK SERVER
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </main>

    <script>
        // Fungsi utama perpindahan menu dinamis secara instan
        function switchMenu(menuId, element) {
            // 1. Sembunyikan semua kontainer menu konten
            const contents = document.querySelectorAll('.menu-content');
            contents.forEach(content => content.classList.add('hidden'));

            // 2. Tampilkan kontainer menu yang dipilih
            document.getElementById('menu-' + menuId).classList.remove('hidden');

            // 3. Ubah teks judul header atas secara dinamis
            const titles = {
                'dashboard': 'Dashboard Konsol',
                'manager': 'Manajer Penyimpanan Berkas',
                'security': 'Gerbang Proteksi Keamanan',
                'settings': 'Pengaturan Sistem Utama'
            };
            document.getElementById('header-title').innerText = titles[menuId];

            // 4. Ubah visual status active/non-active pada tombol sidebar menu
            const buttons = document.querySelectorAll('.nav-btn');
            buttons.forEach(btn => {
                btn.className = "nav-btn w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-slate-200 text-xs transition text-left";
            });

            // Set tombol yang aktif saat ini menjadi bergaya Emerald Gradient Premium
            element.className = "nav-btn w-full flex items-center gap-3 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500/10 to-teal-500/5 border border-emerald-500/20 text-emerald-400 font-medium text-xs transition text-left";
        }

        // Sinkronisasi Nama File Upload Pratinjau
        document.getElementById('media_file').onchange = function () {
            document.getElementById('file_name_preview').innerHTML = "<span class='text-emerald-400 font-bold block truncate max-w-[180px] mx-auto'>" + this.files[0].name + "</span><span class='text-slate-500 text-[10px]'>(" + (this.files[0].size/1024).toFixed(1) + " KB)</span>";
        };

        // Live Search untuk Manajer Berkas tanpa re-loading halaman
        function searchTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("search_input");
            filter = input.value.toUpperCase();
            table = document.getElementById("data_table");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByClassName("table-file-name")[0];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }       
            }
        }
    </script>
</body>
</html>