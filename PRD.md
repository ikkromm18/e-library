# PRD — Sistem Perpustakaan SMP

## Kalimat Pembuka

Kalau saya yang membangun sistem ini untuk SMP, maka saya akan selalu bertanya:

> "Apa saja fitur minimum agar perpustakaan bisa berhenti menggunakan buku tulis dan Excel?"

Itulah MVP (Minimum Viable Product).

Banyak sistem perpustakaan langsung membuat fitur seperti e-book, reservasi, QR Code, WA Gateway, OPAC, RFID, hingga denda otomatis. Menurut saya, itu belum diperlukan untuk tahap awal.

## Scope MVP

Target MVP adalah menyelesaikan seluruh proses operasional harian perpustakaan.

Yang bisa dilakukan setelah MVP selesai:

- Login petugas
- Mengelola buku
- Mengelola anggota
- Meminjam buku
- Mengembalikan buku
- Melihat laporan

Selesai.

Kalau keenam proses itu berjalan, maka perpustakaan sudah bisa meninggalkan pencatatan manual.

## Modul MVP

1. Authentication
2. Dashboard
3. Master Buku
4. Kategori Buku
5. Rak Buku
6. Master Anggota
7. Peminjaman
8. Pengembalian
9. Laporan
10. Setting

## 1. Authentication

### Tujuan

Mengamankan sistem.

### User

- Admin
- Petugas

### Login

#### Field

- Username
- Password

#### Fitur

- [ ] Login
- [ ] Logout
- [ ] Remember Login
- [ ] Ganti Password

#### Tidak perlu

- Register
- Forgot Password
- OTP

Karena pengguna dibuat oleh Admin.

## 2. Dashboard

Dashboard harus menjawab pertanyaan:

> "Bagaimana kondisi perpustakaan hari ini?"

### Widget

- Jumlah Buku
- Jumlah Judul
- Jumlah Anggota
- Sedang Dipinjam
- Terlambat
- Pengembalian Hari Ini

### Grafik

- Peminjaman 7 Hari Terakhir

### Shortcut

- Tambah Buku
- Pinjam Buku
- Kembalikan Buku

## 3. Master Buku

Ini modul terbesar.

### Data Buku

- Kode Buku
- ISBN
- Judul
- Sub Judul
- Kategori
- Pengarang
- Penerbit
- Tahun
- Bahasa
- Lokasi Rak
- Jumlah Eksemplar
- Deskripsi
- Cover
- Status Buku
  - Aktif
  - Tidak Aktif

### CRUD

- [x] Tambah
- [x] Edit
- [x] Hapus
- [x] Detail
- [x] Cari

### Filter

- Kategori
- Rak
- Pengarang
- Penerbit
- Tahun

### Pencarian

Bisa mencari berdasarkan:

- ISBN
- Kode
- Judul
- Pengarang

### Validasi

- ISBN tidak boleh sama.
- Kode Buku otomatis.
- Judul wajib.
- Kategori wajib.
- Rak wajib.

## 4. Kategori Buku

Master sederhana.

- Pelajaran
- Novel
- Agama
- Ensiklopedia
- Komik Edukasi
- Majalah

CRUD lengkap.

## 5. Rak Buku

Contoh:

- A-01
- A-02
- A-03
- B-01
- B-02

### Field

- Kode Rak
- Nama Rak
- Keterangan

## 6. Master Anggota

Biasanya siswa.

### Field

- NIS
- Nama
- Jenis Kelamin
- Kelas
- Alamat
- Nomor HP
- Tanggal Masuk
- Status

### Status

- Aktif
- Lulus
- Pindah
- Nonaktif

### CRUD

- Tambah
- Edit
- Hapus
- Detail
- Cari
- Import Excel

Saya justru memasukkan Import Excel ini ke MVP.

Kenapa?

Karena sekolah punya ratusan siswa.

Tidak mungkin input manual.

## 7. Peminjaman

Modul paling penting.

### Flow

1. Cari Anggota
2. Pilih Buku
3. Tambah ke Daftar
4. Simpan

### Data

- Nomor Transaksi
- Tanggal
- Tanggal Jatuh Tempo
- Petugas
- Anggota
- Daftar Buku

### Rule

Misalnya:

- Maksimal 3 buku.
- Tidak boleh meminjam jika status anggota nonaktif.
- Tidak boleh meminjam jika masih punya buku terlambat.
- Tidak boleh meminjam jika stok habis.

Saat simpan, stok otomatis berkurang.

## 8. Pengembalian

### Flow

1. Cari Transaksi
2. Pilih Buku
3. Kembalikan

Saat dikembalikan, stok otomatis bertambah.

Jika terlambat, hitung:

```
Tanggal Kembali - Jatuh Tempo
```

### Status

- Tepat Waktu
- Terlambat

Belum perlu pembayaran denda. Cukup tampilkan "Terlambat 5 Hari".

## 9. Laporan

### Minimal

**Buku**

- Seluruh Buku
- Buku per Kategori
- Buku per Rak

**Anggota**

- Semua Anggota
- Anggota Aktif

**Transaksi**

- Peminjaman
- Pengembalian
- Terlambat

### Export

- PDF
- Excel

## 10. Setting

Tidak banyak.

- Nama Perpustakaan
- Logo
- Lama Pinjam
- Maksimal Buku
- Tanggal Libur (opsional)
