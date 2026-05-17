# UPA Laboratorium Biosains — Sistem Informasi Penyewaan & Pelayanan
**Semester 2 Project** | Politeknik Negeri Jember

---

## 🗂️ Arsitektur Proyek

```
project-smt2/
│
├── 📄 index.php              ← Halaman Home (Frontend)
├── 📄 shop.php               ← Katalog Layanan (Frontend)
├── 📄 cart.php               ← Keranjang Pemesanan (Frontend)
├── 📄 checkout.php           ← Proses Checkout (Frontend)
├── 📄 pesanan.php            ← Status Pesanan User (Frontend)
├── 📄 detail_transaksi.php   ← Detail Transaksi (Frontend)
├── 📄 invoice.php            ← Cetak Invoice (Frontend)
│
├── 📁 admin/                 ← Modul Admin (Backend)
│   ├── index.php             ← Dashboard Admin
│   ├── sewa.php              ← Manajemen Katalog Sewa Alat & Ruangan
│   ├── pengujian.php         ← Manajemen Katalog Pengujian
│   ├── pesanan.php           ← Manajemen Daftar Pesanan
│   └── includes/             ← Komponen UI Admin
│       ├── header.php        ← Header + CSS Admin
│       ├── sidebar.php       ← Sidebar Navigasi
│       ├── footer.php        ← Footer + JS Admin
│       └── auth.php          ← Guard Autentikasi Admin
│
├── 📁 login/                 ← Modul Autentikasi (Authentication)
│   ├── signin.php            ← Halaman Login
│   ├── signup.php            ← Halaman Registrasi
│   ├── logout.php            ← Proses Logout
│   ├── auth.php              ← Logika Autentikasi
│   ├── session.php           ← Manajemen Sesi
│   └── database.php          ← Koneksi Database (MySQLi OOP)
│
├── 📁 css/                   ← Stylesheet Frontend
├── 📁 js/                    ← JavaScript Frontend
├── 📁 images/                ← Aset Gambar
├── 📁 fonts/                 ← Font Lokal
└── 📁 scss/                  ← SCSS Source (untuk development)
```

---

## 🛠️ Teknologi

| Layer | Teknologi |
|-------|-----------|
| Frontend | HTML5, CSS3, Bootstrap 4, jQuery |
| Backend | PHP 8.x (Procedural + OOP untuk DB) |
| Database | MySQL (via MySQLi) |
| Server | Apache (Laragon) |

---

## 🌐 URL Akses

| Halaman | URL |
|---------|-----|
| Home | `http://localhost/wppplTerbaru/project-smt2/` |
| Katalog | `http://localhost/wppplTerbaru/project-smt2/shop.php` |
| Login | `http://localhost/wppplTerbaru/project-smt2/login/signin.php` |
| Admin Dashboard | `http://localhost/wppplTerbaru/project-smt2/admin/` |

---

## 🗄️ Skema Database Utama

| Tabel | Fungsi |
|-------|--------|
| `users` | Data pengguna & role (Admin, Mahasiswa, Peneliti, Dosen) |
| `products` | Katalog layanan (Sewa Alat, Sewa Ruangan, Pengujian) |
| `orders` | Header pesanan (status: Pending / Confirmed / Canceled) |
| `order_items` | Detail item per pesanan + `waktu_item` per kategori |

---

## 🔐 Role & Akses

| Role | id_role | Akses |
|------|---------|-------|
| Admin | 1 | Full access + Dashboard Admin |
| Ketua Lab | 2 | Dashboard Admin (read + confirm) |
| Mahasiswa | 3 | Frontend user |
| Peneliti Eksternal | 4 | Frontend user |
| Dosen | 5 | Frontend user |

---

## ⚙️ Logika Bisnis Utama

### Sewa Alat
- Slot waktu terbatas: **08:00, 11:00, 14:00**
- Jeda antar-pesanan: **3 jam**
- Slot terkunci otomatis jika sudah dipesan

### Sewa Ruangan (Ruang Lab Biosains)
- Satuan: **per Orang** (kapasitas maks **25 orang/bulan**)
- Tampilkan sisa slot real-time
- Terkunci dengan pesan "Kapasitas Penuh" jika kuota habis

### Pengujian
- Jeda antar-pesanan item yang sama: **3 hari**
- Contoh: pesan tgl 6 → tgl 6–8 terkunci → terbuka tgl 9

---

## 📦 Alur Pemesanan

```
User → Katalog (shop.php)
     → Pilih Jadwal (Modal Booking)
     → Keranjang (cart.php)
     → Checkout + Upload Bukti Transfer
     → Pesanan Tersimpan (status: Pending)
     → Admin Konfirmasi/Tolak (admin/pesanan.php)
     → User Lihat Status (pesanan.php)
     → Jika Confirmed → Cetak Invoice
```
