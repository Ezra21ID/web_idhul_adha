<?php
session_start();
require_once '../include/config.php';

// Simulasi login untuk development
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Query untuk mengambil semua data user beserta data warga terkait
$sql = "SELECT u.user_id, u.nama, u.nik, u.role, w.alamat, w.is_panitia, w.is_berqurban
        FROM users u
        LEFT JOIN warga w ON u.user_id = w.user_id
        ORDER BY u.nama ASC";
$result = $conn->query($sql);

// Masukkan header
include 'partials/header.php';
?>

<!-- Setel Judul Halaman -->
<title>Manajemen User - Admin Panel</title>

<!-- Header Halaman -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="m-0">Manajemen User</h1>
    <button type="button" class="btn btn-primary" id="tombolTambahUser">
        <i class="bi bi-plus-circle-fill me-2"></i>Tambah User Baru
    </button>
</div>

<?php
if (isset($_SESSION['sukses'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
            ' . $_SESSION['sukses'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['sukses']); // Hapus session setelah ditampilkan
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . $_SESSION['error'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['error']); // Hapus session setelah ditampilkan
}
?>


<!-- Tabel Daftar User -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nama</th>
                        <th scope="col">NIK</th>
                        <th scope="col">Role</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php $i = 1; ?>
                        <?php while ($user = $result->fetch_assoc()): ?>
                            <tr id="user-row-<?php echo $user['user_id']; ?>">
                                <th scope="row"><?php echo $i++; ?></th>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['nama']); ?></strong>
                                    <small class="d-block text-muted"><?php echo htmlspecialchars($user['alamat'] ?? 'Alamat belum diisi'); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($user['nik']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                                <td>
                                    <?php if ($user['is_panitia']): ?>
                                        <span class="badge bg-info">Panitia</span>
                                    <?php endif; ?>
                                    <?php if ($user['is_berqurban']): ?>
                                        <span class="badge bg-success">Peserta Qurban</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <!-- Tombol Edit -->
                                    <button type="button" class="btn btn-sm btn-warning tombol-edit-user"
                                        data-id="<?php echo $user['user_id']; ?>"
                                        data-nama="<?php echo htmlspecialchars($user['nama']); ?>"
                                        data-nik="<?php echo htmlspecialchars($user['nik']); ?>"
                                        data-alamat="<?php echo htmlspecialchars($user['alamat'] ?? ''); ?>"
                                        data-role="<?php echo htmlspecialchars($user['role']); ?>">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger tombol-hapus-user"
                                        data-id="<?php echo $user['user_id']; ?>"
                                        data-nama="<?php echo htmlspecialchars($user['nama']); ?>">
                                        <i class="bi bi-trash-fill"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data user.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 2. TAMBAHKAN KODE MODAL TAMBAH USER DI SINI -->
<div class="modal fade" id="tambahUserModal" tabindex="-1" aria-labelledby="tambahUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahUserModalLabel">Formulir Tambah User Baru</h5>
                <button type="button" class="btn-close" id="tombolCloseModal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulir akan mengirim data ke file proses_tambah_user.php -->
                <form action="proses/proses_tambah_user.php" method="POST">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label for="nik" class="form-label">NIK</label>
                        <input type="text" class="form-control" id="nik" name="nik" placeholder="Masukkan NIK" required>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat (Contoh: Blok A-01)</label>
                        <input type="text" class="form-control" id="alamat" name="alamat" placeholder="Masukkan alamat" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option selected disabled value="">Pilih Role...</option>
                            <option value="admin">Admin</option>
                            <option value="panitia">Panitia</option>
                            <option value="berqurban">Peserta Qurban</option>
                            <option value="warga">Warga</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Buat password untuk user baru" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="tombolBatalModal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal edit user -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Formulir Edit User</h5>
                <button type="button" class="btn-close" id="tombolCloseModalEdit" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulir akan mengirim data ke file proses_edit_user.php -->
                <form id="formEditUser" action="proses/proses_edit_user.php" method="POST">
                    <!-- Input tersembunyi untuk menyimpan user_id -->
                    <input type="hidden" id="edit_user_id" name="user_id">

                    <div class="mb-3">
                        <label for="edit_nama" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="edit_nama" name="nama" placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_nik" class="form-label">NIK</label>
                        <input type="text" class="form-control" id="edit_nik" name="nik" placeholder="Masukkan NIK" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_alamat" class="form-label">Alamat (Contoh: Blok A-01)</label>
                        <input type="text" class="form-control" id="edit_alamat" name="alamat" placeholder="Masukkan alamat" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_role" class="form-label">Role</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option selected disabled value="">Pilih Role...</option>
                            <option value="admin">Admin</option>
                            <option value="panitia">Panitia</option>
                            <option value="berqurban">Peserta Qurban</option>
                            <option value="warga">Warga</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Password Baru (Opsional)</label>
                        <input type="password" class="form-control" id="edit_password" name="password" placeholder="Isi jika ingin mengubah password">
                        <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="tombolBatalModalEdit">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalHapusLabel">Konfirmasi Hapus User</h5>
                <button type="button" class="btn-close" id="tombolCloseModalHapus" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="hapusErrorAlert" class="alert alert-danger d-none" role="alert"></div>
                Apakah Anda yakin ingin menghapus user <strong id="namaUserHapus"></strong>? Tindakan ini tidak dapat dibatalkan.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="tombolBatalModalHapus">Batal</button>
                <button type="button" id="tombolKonfirmasiHapus" class="btn btn-danger">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>


<!-- JavaScript untuk modal hapus -->
<script>
    const modalHapus = document.getElementById('modalHapus');
    const semuaTombolHapus = document.querySelectorAll('.tombol-hapus-user');
    const tombolCloseModalHapus = document.getElementById('tombolCloseModalHapus');
    const tombolBatalModalHapus = document.getElementById('tombolBatalModalHapus');
    const tombolKonfirmasiHapus = document.getElementById('tombolKonfirmasiHapus');
    const namaUserHapus = document.getElementById('namaUserHapus');
    const hapusErrorAlert = document.getElementById('hapusErrorAlert');

    let userIdToDelete = null;

    function tampilkanModalHapus() {
        hapusErrorAlert.classList.add('d-none');
        modalHapus.style.display = 'block';
        modalHapus.classList.add('show');

        // Cek dulu apakah backdrop sudah ada sebelum membuat yang baru
        if (!document.querySelector('.modal-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
        }
    }

    function sembunyikanModalHapus() {
        modalHapus.style.display = 'none';
        modalHapus.classList.remove('show');

        // Pastikan untuk menghapus backdrop
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }

    // Event listener untuk semua tombol hapus di tabel
    semuaTombolHapus.forEach(button => {
        button.addEventListener('click', function() {
            userIdToDelete = this.getAttribute('data-id');
            const userName = this.getAttribute('data-nama');
            namaUserHapus.textContent = userName;
            tampilkanModalHapus();
        });
    });

    // Event listener untuk tombol konfirmasi "Ya, Hapus"
    tombolKonfirmasiHapus.addEventListener('click', function() {
        if (!userIdToDelete) return;

        const originalButtonText = this.innerHTML;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menghapus...';
        hapusErrorAlert.classList.add('d-none'); // Sembunyikan error lama

        const formData = new FormData();
        formData.append('user_id', userIdToDelete);

        fetch('proses/proses_hapus_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Cek jika respons tidak ok (misal error 500 dari server)
                if (!response.ok) {
                    throw new Error('Respons jaringan tidak OK.');
                }
                return response.json();
            })
            .then(data => {
                // ---- BLOK DEBUGGING ----
                console.log("Respons dari server:", data);
                console.log("ID yang akan dihapus (userIdToDelete):", userIdToDelete);
                // ---- AKHIR BLOK DEBUGGING ----

                if (data.status === 'success') {
                    sembunyikanModalHapus();

                    const rowId = 'user-row-' + userIdToDelete;
                    console.log("Mencari elemen dengan ID:", rowId); // Debug: Lihat ID yang dicari

                    const rowToDelete = document.getElementById(rowId);
                    console.log("Elemen yang ditemukan:", rowToDelete); // Debug: Lihat apakah elemennya ditemukan

                    if (rowToDelete) {
                        console.log("Elemen ditemukan! Menjalankan animasi hapus..."); // Debug
                        rowToDelete.style.transition = 'opacity 0.5s ease';
                        rowToDelete.style.opacity = '0';
                        setTimeout(() => {
                            rowToDelete.remove();
                            console.log("Elemen telah dihapus dari DOM."); // Debug
                        }, 500);
                    } else {
                        // Jika elemen tidak ditemukan, ini akan muncul di console
                        console.error("GAGAL: Elemen dengan ID '" + rowId + "' tidak ditemukan di halaman. Cek kembali ID pada <tr> Anda.");
                    }

                } else {
                    hapusErrorAlert.textContent = data.message;
                    hapusErrorAlert.classList.remove('d-none');
                }
            })
            .catch(error => {
                // Jika ada error jaringan atau JSON
                hapusErrorAlert.textContent = 'Gagal terhubung ke server. Silakan coba lagi.';
                hapusErrorAlert.classList.remove('d-none');
                console.error('Fetch Error:', error);
            })
            .finally(() => {
                // Selalu jalankan blok ini, baik sukses maupun gagal
                // Kembalikan tombol ke keadaan semula
                this.disabled = false;
                this.innerHTML = originalButtonText;

                // Jika modal masih terbuka (karena error), jangan lakukan apa-apa
                // Jika sudah tertutup (karena sukses), tidak ada masalah
            });
    });

    // Event listener untuk menutup modal
    tombolCloseModalHapus.addEventListener('click', sembunyikanModalHapus);
    tombolBatalModalHapus.addEventListener('click', sembunyikanModalHapus);
    window.addEventListener('click', (event) => {
        if (event.target == modalHapus) {
            sembunyikanModalHapus();
        }
    });
</script>

<!-- JavaScript untuk modal tambah user -->
<script>
    const tombolBuka = document.getElementById('tombolTambahUser');
    const modal = document.getElementById('tambahUserModal');
    const tombolTutup = document.getElementById('tombolCloseModal');
    const tombolBatal = document.getElementById('tombolBatalModal');

    // Fungsi untuk menampilkan modal
    function tampilkanModal() {
        // Bootstrap menggunakan class 'show' dan 'd-block' untuk menampilkan modal
        modal.style.display = 'block';
        modal.classList.add('show');

        // Membuat backdrop (latar belakang gelap) secara manual
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }

    // Fungsi untuk menyembunyikan modal
    function sembunyikanModal() {
        modal.style.display = 'none';
        modal.classList.remove('show');

        // Menghapus backdrop dari body
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            document.body.removeChild(backdrop);
        }
    }

    // 2. Tambahkan event listener untuk setiap aksi

    // Ketika tombol 'Tambah User Baru' di-klik, panggil fungsi tampilkanModal
    tombolBuka.addEventListener('click', function() {
        tampilkanModal();
    });

    // Ketika tombol 'X' di-klik, panggil fungsi sembunyikanModal
    tombolTutup.addEventListener('click', function() {
        sembunyikanModal();
    });

    // Ketika tombol 'Batal' di-klik, panggil fungsi sembunyikanModal
    tombolBatal.addEventListener('click', function() {
        sembunyikanModal();
    });

    // Opsional: Menutup modal jika user meng-klik di luar area modal
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            sembunyikanModal();
        }
    });
</script>

<!-- JavaScript untuk modal edit user (Versi Lengkap dan Benar) -->
<script>
    // --- Seleksi semua elemen yang dibutuhkan ---
    const modalEdit = document.getElementById('editUserModal');
    const formEdit = document.getElementById('formEditUser'); // Pastikan <form> punya id="formEditUser"
    const tombolTutupEdit = document.getElementById('tombolCloseModalEdit');
    const tombolBatalEdit = document.getElementById('tombolBatalModalEdit');
    // Pastikan tombol edit punya class "tombol-edit-user"
    const semuaTombolEdit = document.querySelectorAll('.tombol-edit-user'); 
    // Pastikan ada div ini di dalam modal untuk pesan error
    const editErrorAlert = document.getElementById('editErrorAlert'); 

    // Variabel untuk menyimpan ID user yang sedang aktif diedit
    let currentEditingUserId = null;

    // --- Fungsi untuk menampilkan modal ---
    function tampilkanModalEdit() {
        if(editErrorAlert) editErrorAlert.classList.add('d-none'); // Sembunyikan error lama
        modalEdit.style.display = 'block';
        modalEdit.classList.add('show');
        // Buat backdrop (latar belakang gelap)
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }

    // --- Fungsi untuk menyembunyikan modal dan backdrop ---
    function sembunyikanModalEdit() {
        modalEdit.style.display = 'none';
        modalEdit.classList.remove('show');
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            // Menggunakan .remove() lebih modern dan aman
            backdrop.remove(); 
        }
    }

    // --- Event listener untuk SEMUA tombol edit di tabel ---
    semuaTombolEdit.forEach(button => {
        button.addEventListener('click', function() {
            // Simpan ID user yang diklik
            currentEditingUserId = this.getAttribute('data-id');

            // Isi form di dalam modal dengan data dari tombol
            document.getElementById('edit_user_id').value = currentEditingUserId;
            document.getElementById('edit_nama').value = this.getAttribute('data-nama');
            document.getElementById('edit_nik').value = this.getAttribute('data-nik');
            document.getElementById('edit_alamat').value = this.getAttribute('data-alamat');
            document.getElementById('edit_role').value = this.getAttribute('data-role').toLowerCase();
            document.getElementById('edit_password').value = '';

            // Tampilkan modal yang sudah terisi
            tampilkanModalEdit();
        });
    });

    // --- INI BAGIAN LOGIKA AJAX YANG HILANG DARI KODE ANDA ---
    // Pastikan formEdit tidak null (artinya id form sudah benar)
    if (formEdit) {
        formEdit.addEventListener('submit', function(event) {
            // 1. Mencegah form melakukan reload/redirect (SANGAT PENTING)
            event.preventDefault(); 

            const formData = new FormData(formEdit);
            const submitButton = formEdit.querySelector('button[type="submit"]');
            
            // UI feedback untuk user
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
            if(editErrorAlert) editErrorAlert.classList.add('d-none');

            // 2. Mengirim data ke server di latar belakang
            fetch('proses/proses_edit_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Mengubah respons server menjadi objek JSON
            .then(data => {
                // 3. Memproses respons dari server
                if (data.status === 'success') {
                    // Jika sukses, tutup modal dan backdrop
                    sembunyikanModalEdit();
                    
                    // 4. Perbarui data di baris tabel secara otomatis
                    const updatedRow = document.getElementById('user-row-' + currentEditingUserId);
                    if(updatedRow) {
                        const d = data.updated_data;
                        updatedRow.cells[1].innerHTML = `<strong>${d.nama}</strong><small class="d-block text-muted">${d.alamat}</small>`;
                        updatedRow.cells[2].textContent = d.nik;
                        updatedRow.cells[3].textContent = d.role;
                        
                        let statusHtml = '';
                        if (d.is_panitia) statusHtml += '<span class="badge bg-info">Panitia</span> ';
                        if (d.is_berqurban) statusHtml += '<span class="badge bg-success">Peserta Qurban</span>';
                        updatedRow.cells[4].innerHTML = statusHtml.trim();

                        // Update juga data-* atribut di tombol edit agar data tetap sinkron
                        const editBtn = updatedRow.querySelector('.tombol-edit-user');
                        editBtn.setAttribute('data-nama', d.nama);
                        editBtn.setAttribute('data-nik', d.nik);
                        editBtn.setAttribute('data-alamat', d.alamat);
                        editBtn.setAttribute('data-role', d.role.toLowerCase());
                    }
                } else {
                    // Jika ada error dari PHP, tampilkan di dalam modal
                    if(editErrorAlert) {
                        editErrorAlert.textContent = data.message;
                        editErrorAlert.classList.remove('d-none');
                    }
                }
            })
            .catch(error => {
                // Jika ada error jaringan
                if(editErrorAlert) {
                    editErrorAlert.textContent = 'Terjadi kesalahan jaringan.';
                    editErrorAlert.classList.remove('d-none');
                }
                console.error('Error:', error);
            })
            .finally(() => {
                // Apapun hasilnya, aktifkan kembali tombol submit
                submitButton.disabled = false;
                submitButton.innerHTML = 'Simpan Perubahan';
            });
        });
    }

    // --- Event listener untuk tombol tutup dan batal ---
    tombolTutupEdit.addEventListener('click', sembunyikanModalEdit);
    tombolBatalEdit.addEventListener('click', sembunyikanModalEdit);
    window.addEventListener('click', (event) => {
        if (event.target == modalEdit) {
            sembunyikanModalEdit();
        }
    });
</script>

<?php
// Masukkan footer
include 'partials/footer.php';
?>