# ERD (Entity-Relationship Diagram)

Berikut adalah representasi tekstual dari ERD berdasarkan struktur database "RS Sehat Selalu".

- **Entitas:**
  - `users`: Menyimpan data pengguna (admin dan pasien).
    - `id` (Primary Key)
    - `username`
    - `password`
    - `role`
  - `dokter`: Menyimpan data dokter.
    - `id` (Primary Key)
    - `username`
    - `password`
    - `nama`
  - `dokter_jadwal`: Jadwal praktek dokter.
    - `id` (Primary Key)
    - `dokter` (Foreign Key ke `dokter.username`)
    - `hari`
    - `jam`
  - `booking`: Menyimpan data booking/janji temu.
    - `id` (Primary Key)
    - `pasien` (Foreign Key ke `users.username`)
    - `dokter` (Foreign Key ke `dokter.username`)
    - `tanggal`
    - `hari`
    - `jam`
    - `keluhan`
  - `pemeriksaan`: Menyimpan rekam medis hasil pemeriksaan.
    - `id` (Primary Key)
    - `pasien` (Foreign Key ke `users.username`)
    - `dokter` (Foreign Key ke `dokter.username`)
    - `diagnosa`
    - `obat`
    - `tanggal`
  - `rawat_inap`: Mengelola pasien rawat inap.
    - `id` (Primary Key)
    - `pasien` (Foreign Key ke `users.username`)
    - `dokter` (Foreign Key ke `dokter.username`)
    - `alasan`
    - `status`
    - `tanggal`
  - `pembayaran`: Menyimpan data transaksi pembayaran.
    - `id` (Primary Key)
    - `pasien` (Foreign Key ke `users.username`)
    - `jumlah`
    - `keterangan`
    - `tanggal`
  - `riwayat`: Riwayat medis pasien.
    - `id` (Primary Key)
    - `pasien` (Foreign Key ke `users.username`)
    - `dokter` (Foreign Key ke `dokter.username`)
    - `tanggal`
    - `diagnosa`

# Use Case

Berikut adalah Use Case Diagram yang menjelaskan interaksi antara aktor (pengguna) dan sistem.

**Aktor:**

- **Admin:** Pengelola sistem.
- **Dokter:** Tenaga medis yang melakukan pemeriksaan.
- **Pasien:** Pengguna yang mencari layanan kesehatan.

**Use Cases:**

- **Admin:**
  - Mengelola data dokter.
  - Mengelola jadwal dokter.
  - Melihat data booking.
  - Mengelola data rawat inap.
  - Mengelola pembayaran.
  - Melihat riwayat diagnosa pasien.
  - Mengelola pengguna (pasien).
- **Dokter:**
  - Login ke sistem.
  - Melihat jadwal praktek.
  - Melihat daftar booking pasien.
  - Mencatat hasil pemeriksaan (diagnosa dan obat).
  - Mengelola data pasien rawat inap.
- **Pasien:**
  - Mendaftar/Registrasi akun baru.
  - Login ke sistem.
  - Membuat janji temu/booking dengan dokter.
  - Melihat riwayat medis pribadi.
  - Melihat tagihan pembayaran.

