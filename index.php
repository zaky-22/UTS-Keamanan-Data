<?php
require_once 'includes/crypto.php';
require_once 'config/database.php';

// Ambil koneksi database
$database = new Database();
$db = $database->getConnection();

$crypto = new HybridCrypto();

// Ambil data admin dari database MySQL/phpMyAdmin (ID = 1)
$query = "SELECT * FROM admin_users WHERE id = 1 LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$adminData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$adminData) {
    $adminData = [
        "username" => "admin_utama",
        "secret_note" => "Confidential: Anggaran Proyek Kriptografi Tahun 2026 Rp 500.000.000"
    ];
}

// Cek apakah user mengirim catatan baru melalui form input
$currentNote = $adminData['secret_note'];
$successMessage = "";

if (isset($_POST['update_note']) && !empty($_POST['new_secret_note'])) {
    $currentNote = trim($_POST['new_secret_note']);
    
    // Simpan catatan baru ke database phpMyAdmin secara otomatis
    $updateQuery = "UPDATE admin_users SET secret_note = :note WHERE id = 1";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->bindParam(':note', $currentNote);
    $updateStmt->execute();
    
    $successMessage = "Catatan rahasia berhasil diperbarui dan dienkripsi ulang!";
}

// Proses Enkripsi Konten (berdasarkan catatan aktif saat ini)
$encryptedNote = $crypto->encryptContent($currentNote);
$digitalSignature = $crypto->signData($currentNote);

// Aksi Dekripsi via POST
$decryptedNote = "";
$isVerified = false;
if (isset($_POST['decrypt_action'])) {
    $decryptedNote = $crypto->decryptContent($encryptedNote);
    $isVerified = $crypto->verifySignature($decryptedNote, $digitalSignature);
}

// Menentukan halaman aktif dari URL (default: dashboard)
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Admin - Kriptografi Hibrida</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased min-h-screen flex">

    <!-- Sidebar Navigasi -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col hidden md:flex">
        <div class="p-5 border-b border-slate-800">
            <h2 class="text-lg font-bold text-indigo-400 flex items-center gap-2">
                <span>🔒</span> UTS Kriptografi
            </h2>
            <p class="text-xs text-slate-400 mt-1">Sistem Informasi Hibrida</p>
        </div>
        <nav class="p-4 space-y-2 flex-grow">
            <a href="index.php?page=dashboard" class="flex items-center gap-3 px-4 py-2.5 rounded-lg <?= $page == 'dashboard' ? 'bg-indigo-600 text-white font-medium' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' ?> transition text-sm">
                <span>📊</span> Dashboard Admin
            </a>
            <a href="index.php?page=config" class="flex items-center gap-3 px-4 py-2.5 rounded-lg <?= $page == 'config' ? 'bg-indigo-600 text-white font-medium' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' ?> transition text-sm">
                <span>⚙️</span> Konfigurasi AES & RSA
            </a>
            <a href="index.php?page=database" class="flex items-center gap-3 px-4 py-2.5 rounded-lg <?= $page == 'database' ? 'bg-indigo-600 text-white font-medium' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' ?> transition text-sm">
                <span>📁</span> Database (phpMyAdmin)
            </a>
        </nav>
        <div class="p-4 border-t border-slate-800 text-xs text-slate-500">
            PHP 8.x &bull; OpenSSL Engine
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="flex-grow flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-slate-900 border-b border-slate-800 p-4 shadow-md flex justify-between items-center">
            <div class="flex items-center gap-3">
                <span class="bg-indigo-500/10 text-indigo-400 border border-indigo-500/30 text-xs px-3 py-1 rounded-full font-semibold">Active Session</span>
                <h1 class="text-sm text-slate-300 hidden sm:inline">Selamat Datang, <strong class="text-white"><?= htmlspecialchars($adminData['username']) ?></strong></h1>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs text-emerald-400 font-medium">Database Connected</span>
            </div>
        </header>

        <!-- Content Area -->
        <main class="p-6 md:p-8 max-w-6xl mx-auto w-full flex-grow">
            
            <?php if ($page == 'dashboard'): ?>
                <!-- Notifikasi Berhasil Update -->
                <?php if (!empty($successMessage)): ?>
                <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-2">
                    <span>✨</span> <?= htmlspecialchars($successMessage) ?>
                </div>
                <?php endif; ?>

                <!-- Top Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
                    <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg">
                        <div class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Kriptografi Simetris</div>
                        <div class="text-2xl font-bold text-cyan-400 mt-2">AES-256-CBC</div>
                        <p class="text-xs text-slate-400 mt-1">Mengamankan konten utama admin dengan kecepatan tinggi.</p>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg">
                        <div class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Kriptografi Asimetris</div>
                        <div class="text-2xl font-bold text-purple-400 mt-2">RSA-2048 Bit</div>
                        <p class="text-xs text-slate-400 mt-1">Melindungi integritas kunci dan validasi identitas.</p>
                    </div>
                    <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-lg">
                        <div class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Integritas Data</div>
                        <div class="text-2xl font-bold text-emerald-400 mt-2">Digital Signature</div>
                        <p class="text-xs text-slate-400 mt-1">SHA-256 digital signing untuk otentikasi pengirim.</p>
                    </div>
                </div>

                <!-- Form Ubah Catatan Rahasia -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl p-6 mb-8">
                    <h3 class="text-md font-bold text-white mb-3">✍️ Input / Ubah Pesan Rahasia Admin</h3>
                    <form method="POST" class="space-y-4">
                        <div>
                            <textarea name="new_secret_note" rows="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-slate-200 text-sm focus:outline-none focus:border-indigo-500" placeholder="Ketik pesan rahasia baru di sini..."><?= htmlspecialchars($currentNote) ?></textarea>
                        </div>
                        <button type="submit" name="update_note" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
                            Simpan & Enkripsi Ulang ke Database
                        </button>
                    </form>
                </div>

                <!-- Dashboard Konten Admin Terenkripsi -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-slate-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-white">Dashboard Konten Admin</h2>
                            <p class="text-xs text-slate-400">Data diambil secara *real-time* dari tabel phpMyAdmin (`db_kriptografi_uts`).</p>
                        </div>
                        <form method="POST">
                            <button type="submit" name="decrypt_action" class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2.5 rounded-xl text-sm font-semibold shadow-lg transition flex items-center gap-2">
                                <span>🔓</span> Dekripsi & Verifikasi Tanda Tangan
                            </button>
                        </form>
                    </div>

                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Username Administrator (Database)</label>
                            <div class="bg-slate-950 p-3.5 rounded-xl border border-slate-800 text-slate-200 font-medium">
                                <?= htmlspecialchars($adminData['username']) ?>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Catatan Rahasia (Terenkripsi AES-256)</label>
                            <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 text-amber-400 font-mono text-xs break-all leading-relaxed">
                                <?= htmlspecialchars($encryptedNote) ?>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Tanda Tangan Digital (RSA-2048 Signature)</label>
                            <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 text-purple-300 font-mono text-xs break-all leading-relaxed">
                                <?= htmlspecialchars($digitalSignature) ?>
                            </div>
                        </div>

                        <?php if (isset($_POST['decrypt_action'])): ?>
                        <div class="p-5 rounded-xl bg-slate-950 border border-emerald-500/40 shadow-inner">
                            <h3 class="text-emerald-400 font-bold text-sm mb-2 flex items-center gap-2">
                                <span>✅</span> Hasil Dekripsi & Validasi Integritas:
                            </h3>
                            <p class="text-sm text-slate-300 mb-2">
                                <strong class="text-white">Pesan Asli (Plaintext):</strong> 
                                <span class="text-indigo-300"><?= htmlspecialchars($decryptedNote) ?></span>
                            </p>
                            <p class="text-sm">
                                <strong class="text-white">Status Verifikasi RSA:</strong> 
                                <?php if ($isVerified): ?>
                                    <span class="text-emerald-400 font-bold bg-emerald-500/10 px-2.5 py-1 rounded-lg border border-emerald-500/20 inline-block mt-1">VALID (Data 100% Asli & Tidak Dimanipulasi)</span>
                                <?php else: ?>
                                    <span class="text-rose-400 font-bold bg-rose-500/10 px-2.5 py-1 rounded-lg border border-rose-500/20 inline-block mt-1">TIDAK VALID</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($page == 'config'): ?>
                <!-- KONTEN HALAMAN: KONFIGURASI AES & RSA -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl p-6 md:p-8 space-y-6">
                    <h2 class="text-xl font-bold text-indigo-400">⚙️ Penjelasan Konfigurasi AES-256 & RSA-2048</h2>
                    <p class="text-slate-300 text-sm leading-relaxed">
                        Sistem ini menerapkan konsep **Kriptografi Hibrida**, yaitu menggabungkan keunggulan kriptografi simetris dan asimetris demi mencapai keamanan tingkat tinggi pada panel admin.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                        <div class="bg-slate-950 p-5 rounded-xl border border-slate-800">
                            <h3 class="font-bold text-cyan-400 mb-2">1. AES-256-CBC (Simetris)</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Digunakan untuk mengenkripsi data/konten teks sensitif di database. Menggunakan satu kunci rahasia yang sama untuk proses enkripsi dan dekripsi dengan kecepatan komputasi yang sangat cepat.
                            </p>
                        </div>
                        <div class="bg-slate-950 p-5 rounded-xl border border-slate-800">
                            <h3 class="font-bold text-purple-400 mb-2">2. RSA-2048 & Digital Signature (Asimetris)</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Menggunakan sepasang kunci (Public Key dan Private Key). Private Key digunakan untuk membuat tanda tangan digital (*Digital Signature*) melalui algoritma SHA-256 guna membuktikan bahwa data berasal dari server yang sah dan bebas dari pemalsuan.
                            </p>
                        </div>
                    </div>
                </div>

            <?php elseif ($page == 'database'): ?>
                <!-- KONTEN HALAMAN: DATABASE -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl p-6 md:p-8 space-y-6">
                    <h2 class="text-xl font-bold text-indigo-400">📁 Informasi Koneksi Database phpMyAdmin</h2>
                    <p class="text-slate-300 text-sm leading-relaxed">
                        Aplikasi ini terhubung langsung ke basis data lokal MySQL melalui ekstensi PHP PDO (*PHP Data Objects*).
                    </p>
                    <div class="bg-slate-950 p-5 rounded-xl border border-slate-800 space-y-3 font-mono text-xs">
                        <p class="text-slate-300"><strong class="text-indigo-400">Database Name:</strong> db_kriptografi_uts</p>
                        <p class="text-slate-300"><strong class="text-indigo-400">Tabel Utama:</strong> admin_users</p>
                        <p class="text-slate-300"><strong class="text-indigo-400">Struktur Kolom:</strong> id, username, secret_note, digital_signature</p>
                        <p class="text-slate-300"><strong class="text-indigo-400">Metode Koneksi:</strong> PDO MySQL (host=localhost)</p>
                    </div>
                    <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-xs">
                        Status: Koneksi aktif dan berhasil mengambil data teks rahasia admin secara real-time dari phpMyAdmin.
                    </div>
                </div>
            <?php endif; ?>

        </main>

        <!-- Footer -->
        <footer class="bg-slate-900 border-t border-slate-800 text-center py-4 text-xs text-slate-500">
            Tugas UTS Kriptografi & Keamanan Sistem Informasi &bull; Hibrida AES-256 & RSA-2048
        </footer>
    </div>

</body>
</html>