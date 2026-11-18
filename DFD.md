# Data Flow Diagram (DFD)
## Sistem Informasi RS Sehat Selalu

---

## Diagram Konteks (Context Diagram / DFD Level 0)

```mermaid
flowchart TB
    %% External Entities
    Pasien([Pasien])
    Dokter([Dokter])
    Admin([Admin])
    
    %% Main System
    System[Sistem Informasi<br/>RS Sehat Selalu]
    
    %% Pasien Flows
    Pasien -->|Data Pendaftaran| System
    Pasien -->|Data Login| System
    Pasien -->|Data Booking| System
    System -->|Info Akun| Pasien
    System -->|Konfirmasi Booking| Pasien
    System -->|Riwayat Medis| Pasien
    System -->|Info Pembayaran| Pasien
    
    %% Dokter Flows
    Dokter -->|Data Login| System
    Dokter -->|Data Pemeriksaan| System
    Dokter -->|Data Jadwal| System
    System -->|Info Booking Pasien| Dokter
    System -->|Data Pasien| Dokter
    System -->|Jadwal Praktek| Dokter
    
    %% Admin Flows
    Admin -->|Data Login| System
    Admin -->|Data Booking| System
    Admin -->|Data Pembayaran| System
    Admin -->|Data Rawat Inap| System
    System -->|Laporan Booking| Admin
    System -->|Laporan Pembayaran| Admin
    System -->|Laporan Rawat Inap| Admin
    System -->|Security Logs| Admin
    
    style System fill:#4facfe,stroke:#333,stroke-width:3px,color:#fff
    style Pasien fill:#90EE90,stroke:#333,stroke-width:2px
    style Dokter fill:#FFB6C1,stroke:#333,stroke-width:2px
    style Admin fill:#FFD700,stroke:#333,stroke-width:2px
```

---

## DFD Level 1 - Dekomposisi Sistem Utama

```mermaid
flowchart TB
    %% External Entities
    Pasien([Pasien])
    Dokter([Dokter])
    Admin([Admin])
    
    %% Main Processes
    P1[1.0<br/>Manajemen<br/>Autentikasi]
    P2[2.0<br/>Manajemen<br/>Pasien]
    P3[3.0<br/>Manajemen<br/>Dokter]
    P4[4.0<br/>Manajemen<br/>Admin]
    P5[5.0<br/>Manajemen<br/>Keamanan]
    
    %% Data Stores
    DS1[(D1: Users)]
    DS2[(D2: Dokter)]
    DS3[(D3: Booking)]
    DS4[(D4: Pemeriksaan)]
    DS5[(D5: Pembayaran)]
    DS6[(D6: Rawat Inap)]
    DS7[(D7: Riwayat)]
    DS8[(D8: Security Logs)]
    DS9[(D9: Audit Logs)]
    
    %% Pasien Flows
    Pasien -->|Data Pendaftaran| P1
    Pasien -->|Data Login| P1
    P1 -->|Info Login| Pasien
    
    Pasien -->|Request Booking| P2
    P2 -->|Konfirmasi Booking| Pasien
    P2 -->|Riwayat Medis| Pasien
    
    %% Dokter Flows
    Dokter -->|Data Login| P1
    P1 -->|Info Login| Dokter
    
    Dokter -->|Request Data| P3
    P3 -->|Info Booking| Dokter
    P3 -->|Data Pasien| Dokter
    
    Dokter -->|Data Pemeriksaan| P3
    Dokter -->|Data Jadwal| P3
    
    %% Admin Flows
    Admin -->|Data Login| P1
    P1 -->|Info Login| Admin
    
    Admin -->|Kelola Data| P4
    P4 -->|Laporan| Admin
    
    %% Process to Data Store
    P1 -->|Data User| DS1
    P1 -->|Data Dokter| DS2
    DS1 -->|Validasi User| P1
    DS2 -->|Validasi Dokter| P1
    
    P2 -->|Data Booking| DS3
    P2 -->|Data Riwayat| DS7
    DS3 -->|Info Booking| P2
    DS7 -->|Info Riwayat| P2
    
    P3 -->|Data Pemeriksaan| DS4
    P3 -->|Data Riwayat| DS7
    DS3 -->|Info Booking| P3
    DS4 -->|Info Pemeriksaan| P3
    
    P4 -->|Data Booking| DS3
    P4 -->|Data Pembayaran| DS5
    P4 -->|Data Rawat Inap| DS6
    DS3 -->|Info Booking| P4
    DS5 -->|Info Pembayaran| P4
    DS6 -->|Info Rawat Inap| P4
    
    %% Security Process
    P1 -->|Log Keamanan| P5
    P2 -->|Log Aktivitas| P5
    P3 -->|Log Aktivitas| P5
    P4 -->|Log Aktivitas| P5
    
    P5 -->|Security Events| DS8
    P5 -->|Audit Trail| DS9
    
    style P1 fill:#4facfe,stroke:#333,stroke-width:2px,color:#fff
    style P2 fill:#4facfe,stroke:#333,stroke-width:2px,color:#fff
    style P3 fill:#4facfe,stroke:#333,stroke-width:2px,color:#fff
    style P4 fill:#4facfe,stroke:#333,stroke-width:2px,color:#fff
    style P5 fill:#ff6b6b,stroke:#333,stroke-width:2px,color:#fff
```

---

## DFD Level 2 - Proses 1.0: Manajemen Autentikasi

```mermaid
flowchart TB
    %% External Entities
    User([User/Pasien/Admin])
    Dokter([Dokter])
    
    %% Sub Processes
    P11[1.1<br/>Pendaftaran<br/>Akun]
    P12[1.2<br/>Login<br/>User]
    P13[1.3<br/>Login<br/>Dokter]
    P14[1.4<br/>Verifikasi<br/>2FA]
    P15[1.5<br/>Reset<br/>Password]
    P16[1.6<br/>Logout]
    
    %% Data Stores
    DS1[(D1: Users)]
    DS2[(D2: Dokter)]
    DS10[(D10: User Sessions)]
    DS11[(D11: Login Attempts)]
    DS12[(D12: Password Reset Tokens)]
    DS13[(D13: Backup Codes)]
    DS8[(D8: Security Logs)]
    
    %% User Registration
    User -->|Data Pendaftaran| P11
    P11 -->|Validasi Username| DS1
    DS1 -->|Status Username| P11
    P11 -->|Hash Password| P11
    P11 -->|Simpan User Baru| DS1
    P11 -->|Konfirmasi| User
    P11 -->|Log Registrasi| DS8
    
    %% User Login
    User -->|Username & Password| P12
    P12 -->|Query User| DS1
    DS1 -->|Data User| P12
    P12 -->|Cek Rate Limit| DS11
    DS11 -->|Status Attempts| P12
    P12 -->|Validasi Password| P12
    P12 -->|Request 2FA| P14
    P14 -->|Verify Code| DS13
    DS13 -->|Backup Code Status| P14
    P14 -->|2FA Valid| P12
    P12 -->|Buat Session| DS10
    P12 -->|Token Session| User
    P12 -->|Log Login| DS8
    
    %% Dokter Login
    Dokter -->|Username & Password| P13
    P13 -->|Query Dokter| DS2
    DS2 -->|Data Dokter| P13
    P13 -->|Cek Rate Limit| DS11
    DS11 -->|Status Attempts| P13
    P13 -->|Validasi Password| P13
    P13 -->|Request 2FA| P14
    P14 -->|2FA Valid| P13
    P13 -->|Buat Session| DS10
    P13 -->|Token Session| Dokter
    P13 -->|Log Login| DS8
    
    %% Password Reset
    User -->|Request Reset| P15
    P15 -->|Cek User| DS1
    DS1 -->|User Data| P15
    P15 -->|Generate Token| DS12
    P15 -->|Email Link| User
    User -->|Token & New Password| P15
    P15 -->|Validate Token| DS12
    DS12 -->|Token Status| P15
    P15 -->|Update Password| DS1
    P15 -->|Log Reset| DS8
    
    %% Logout
    User -->|Request Logout| P16
    Dokter -->|Request Logout| P16
    P16 -->|Update Session| DS10
    P16 -->|Destroy Session| P16
    P16 -->|Log Logout| DS8
    P16 -->|Konfirmasi| User
    P16 -->|Konfirmasi| Dokter
    
    style P11 fill:#90EE90,stroke:#333,stroke-width:2px
    style P12 fill:#90EE90,stroke:#333,stroke-width:2px
    style P13 fill:#FFB6C1,stroke:#333,stroke-width:2px
    style P14 fill:#FFA07A,stroke:#333,stroke-width:2px
    style P15 fill:#87CEEB,stroke:#333,stroke-width:2px
    style P16 fill:#DDA0DD,stroke:#333,stroke-width:2px
```

---

## DFD Level 2 - Proses 2.0: Manajemen Pasien

```mermaid
flowchart TB
    %% External Entity
    Pasien([Pasien])
    
    %% Sub Processes
    P21[2.1<br/>Booking<br/>Janji Dokter]
    P22[2.2<br/>Lihat<br/>Riwayat Medis]
    P23[2.3<br/>Lihat<br/>Jadwal Dokter]
    P24[2.4<br/>Kelola<br/>Profil]
    
    %% Data Stores
    DS1[(D1: Users)]
    DS2[(D2: Dokter)]
    DS3[(D3: Booking)]
    DS7[(D7: Riwayat)]
    DS14[(D14: Dokter Jadwal)]
    DS9[(D9: Audit Logs)]
    
    %% Booking Flow
    Pasien -->|Request Jadwal| P23
    P23 -->|Query Jadwal| DS14
    DS14 -->|List Jadwal| P23
    P23 -->|Tampil Jadwal| Pasien
    
    Pasien -->|Data Booking| P21
    P21 -->|Validasi Jadwal| DS14
    DS14 -->|Status Jadwal| P21
    P21 -->|Cek Dokter| DS2
    DS2 -->|Info Dokter| P21
    P21 -->|Simpan Booking| DS3
    P21 -->|Konfirmasi| Pasien
    P21 -->|Log Audit| DS9
    
    %% Riwayat Flow
    Pasien -->|Request Riwayat| P22
    P22 -->|Query Riwayat| DS7
    DS7 -->|Data Riwayat| P22
    P22 -->|Tampil Riwayat| Pasien
    
    %% Profile Flow
    Pasien -->|Update Profil| P24
    P24 -->|Query User| DS1
    DS1 -->|Data User| P24
    P24 -->|Update Data| DS1
    P24 -->|Konfirmasi| Pasien
    P24 -->|Log Audit| DS9
    
    style P21 fill:#90EE90,stroke:#333,stroke-width:2px
    style P22 fill:#90EE90,stroke:#333,stroke-width:2px
    style P23 fill:#90EE90,stroke:#333,stroke-width:2px
    style P24 fill:#90EE90,stroke:#333,stroke-width:2px
```

---

## DFD Level 2 - Proses 3.0: Manajemen Dokter

```mermaid
flowchart TB
    %% External Entity
    Dokter([Dokter])
    
    %% Sub Processes
    P31[3.1<br/>Lihat<br/>Booking]
    P32[3.2<br/>Catat<br/>Pemeriksaan]
    P33[3.3<br/>Kelola<br/>Jadwal]
    P34[3.4<br/>Rawat<br/>Inap]
    
    %% Data Stores
    DS2[(D2: Dokter)]
    DS3[(D3: Booking)]
    DS4[(D4: Pemeriksaan)]
    DS6[(D6: Rawat Inap)]
    DS7[(D7: Riwayat)]
    DS14[(D14: Dokter Jadwal)]
    DS9[(D9: Audit Logs)]
    
    %% Lihat Booking
    Dokter -->|Request Booking| P31
    P31 -->|Query Booking| DS3
    DS3 -->|List Booking| P31
    P31 -->|Filter by Dokter| DS2
    DS2 -->|Info Dokter| P31
    P31 -->|Tampil Booking| Dokter
    
    %% Catat Pemeriksaan
    Dokter -->|Data Pemeriksaan| P32
    P32 -->|Validasi Booking| DS3
    DS3 -->|Info Booking| P32
    P32 -->|Simpan Pemeriksaan| DS4
    P32 -->|Simpan Riwayat| DS7
    P32 -->|Check Rawat Inap| P34
    P32 -->|Konfirmasi| Dokter
    P32 -->|Log Audit| DS9
    
    %% Rawat Inap
    P34 -->|Data Rawat Inap| DS6
    P34 -->|Log Audit| DS9
    
    %% Kelola Jadwal
    Dokter -->|Request Jadwal| P33
    P33 -->|Query Jadwal| DS14
    DS14 -->|List Jadwal| P33
    P33 -->|Tampil Jadwal| Dokter
    
    Dokter -->|Tambah/Hapus Jadwal| P33
    P33 -->|Update Jadwal| DS14
    P33 -->|Validasi Dokter| DS2
    DS2 -->|Info Dokter| P33
    P33 -->|Konfirmasi| Dokter
    P33 -->|Log Audit| DS9
    
    style P31 fill:#FFB6C1,stroke:#333,stroke-width:2px
    style P32 fill:#FFB6C1,stroke:#333,stroke-width:2px
    style P33 fill:#FFB6C1,stroke:#333,stroke-width:2px
    style P34 fill:#FFB6C1,stroke:#333,stroke-width:2px
```

---

## DFD Level 2 - Proses 4.0: Manajemen Admin

```mermaid
flowchart TB
    %% External Entity
    Admin([Admin])
    
    %% Sub Processes
    P41[4.1<br/>Kelola<br/>Booking]
    P42[4.2<br/>Kelola<br/>Pembayaran]
    P43[4.3<br/>Kelola<br/>Rawat Inap]
    P44[4.4<br/>Lihat<br/>Laporan]
    P45[4.5<br/>Security<br/>Dashboard]
    
    %% Data Stores
    DS3[(D3: Booking)]
    DS4[(D4: Pemeriksaan)]
    DS5[(D5: Pembayaran)]
    DS6[(D6: Rawat Inap)]
    DS7[(D7: Riwayat)]
    DS8[(D8: Security Logs)]
    DS9[(D9: Audit Logs)]
    
    %% Kelola Booking
    Admin -->|Request Booking| P41
    P41 -->|Query All Booking| DS3
    DS3 -->|List Booking| P41
    P41 -->|Tampil Data| Admin
    
    Admin -->|Hapus Booking| P41
    P41 -->|Delete Booking| DS3
    P41 -->|Konfirmasi| Admin
    P41 -->|Log Audit| DS9
    
    %% Kelola Pembayaran
    Admin -->|Request Pembayaran| P42
    P42 -->|Query Pembayaran| DS5
    DS5 -->|List Pembayaran| P42
    P42 -->|Tampil Data| Admin
    
    Admin -->|Tambah/Hapus Pembayaran| P42
    P42 -->|Update Pembayaran| DS5
    P42 -->|Konfirmasi| Admin
    P42 -->|Log Audit| DS9
    
    %% Kelola Rawat Inap
    Admin -->|Request Rawat Inap| P43
    P43 -->|Query Rawat Inap| DS6
    DS6 -->|List Rawat Inap| P43
    P43 -->|Tampil Data| Admin
    
    Admin -->|Update Status| P43
    P43 -->|Update Status| DS6
    P43 -->|Konfirmasi| Admin
    P43 -->|Log Audit| DS9
    
    %% Laporan
    Admin -->|Request Laporan| P44
    P44 -->|Query Data| DS3
    P44 -->|Query Data| DS4
    P44 -->|Query Data| DS5
    P44 -->|Query Data| DS6
    P44 -->|Query Data| DS7
    DS3 -->|Data Booking| P44
    DS4 -->|Data Pemeriksaan| P44
    DS5 -->|Data Pembayaran| P44
    DS6 -->|Data Rawat Inap| P44
    DS7 -->|Data Riwayat| P44
    P44 -->|Generate Laporan| Admin
    
    %% Security Dashboard
    Admin -->|Request Security Info| P45
    P45 -->|Query Logs| DS8
    P45 -->|Query Audit| DS9
    DS8 -->|Security Events| P45
    DS9 -->|Audit Trail| P45
    P45 -->|Tampil Dashboard| Admin
    
    style P41 fill:#FFD700,stroke:#333,stroke-width:2px
    style P42 fill:#FFD700,stroke:#333,stroke-width:2px
    style P43 fill:#FFD700,stroke:#333,stroke-width:2px
    style P44 fill:#FFD700,stroke:#333,stroke-width:2px
    style P45 fill:#FF6347,stroke:#333,stroke-width:2px
```

---

## DFD Level 2 - Proses 5.0: Manajemen Keamanan

```mermaid
flowchart TB
    %% Sub Processes
    P51[5.1<br/>Rate<br/>Limiting]
    P52[5.2<br/>Security<br/>Logging]
    P53[5.3<br/>Audit<br/>Trail]
    P54[5.4<br/>Session<br/>Management]
    P55[5.5<br/>2FA<br/>Management]
    
    %% Data Stores
    DS8[(D8: Security Logs)]
    DS9[(D9: Audit Logs)]
    DS10[(D10: User Sessions)]
    DS11[(D11: Login Attempts)]
    DS13[(D13: Backup Codes)]
    DS15[(D15: Encrypted Data)]
    
    %% From Other Processes
    P1[1.0<br/>Autentikasi]
    P2[2.0<br/>Pasien]
    P3[3.0<br/>Dokter]
    P4[4.0<br/>Admin]
    
    %% Rate Limiting
    P1 -->|Login Attempt| P51
    P51 -->|Check Attempts| DS11
    DS11 -->|Attempt Count| P51
    P51 -->|Update Count| DS11
    P51 -->|Allow/Block| P1
    
    %% Security Logging
    P1 -->|Security Event| P52
    P2 -->|Security Event| P52
    P3 -->|Security Event| P52
    P4 -->|Security Event| P52
    P52 -->|Log Event| DS8
    P52 -->|Get IP & UserAgent| P52
    
    %% Audit Trail
    P2 -->|Data Change| P53
    P3 -->|Data Change| P53
    P4 -->|Data Change| P53
    P53 -->|Log Change| DS9
    P53 -->|Old/New Values| DS9
    
    %% Session Management
    P1 -->|Create Session| P54
    P54 -->|Store Session| DS10
    P54 -->|Session ID| P1
    P54 -->|Check Expiry| DS10
    DS10 -->|Session Status| P54
    P54 -->|Cleanup Expired| DS10
    
    %% 2FA Management
    P1 -->|Setup 2FA| P55
    P55 -->|Generate Secret| P55
    P55 -->|Generate Backup Codes| DS13
    P55 -->|Store Encrypted| DS15
    P55 -->|Verify Code| DS13
    DS13 -->|Code Status| P55
    P55 -->|2FA Status| P1
    
    style P51 fill:#ff6b6b,stroke:#333,stroke-width:2px,color:#fff
    style P52 fill:#ff6b6b,stroke:#333,stroke-width:2px,color:#fff
    style P53 fill:#ff6b6b,stroke:#333,stroke-width:2px,color:#fff
    style P54 fill:#ff6b6b,stroke:#333,stroke-width:2px,color:#fff
    style P55 fill:#ff6b6b,stroke:#333,stroke-width:2px,color:#fff
```

---

## Kamus Data (Data Dictionary)

### Data Flows:

| Nama Data Flow | Deskripsi | Struktur Data |
|----------------|-----------|---------------|
| **Data Pendaftaran** | Data untuk registrasi akun baru | username + password + confirm_password + csrf_token |
| **Data Login** | Kredensial untuk autentikasi | username + password + csrf_token |
| **Data Booking** | Informasi booking appointment | pasien + dokter + tanggal + hari + jam + keluhan + csrf_token |
| **Data Pemeriksaan** | Hasil pemeriksaan medis | pasien + dokter + diagnosa + obat + tanggal + csrf_token |
| **Data Pembayaran** | Transaksi pembayaran | pasien + jumlah + keterangan + tanggal + csrf_token |
| **Data Rawat Inap** | Informasi rawat inap | pasien + dokter + alasan + status + tanggal |
| **Data Jadwal** | Jadwal praktek dokter | dokter + hari + jam |
| **Riwayat Medis** | History pemeriksaan pasien | pasien + dokter + tanggal + diagnosa |
| **Security Event** | Event keamanan sistem | user_id + event_type + details + ip_address + user_agent + timestamp |
| **Audit Trail** | Log perubahan data | table_name + record_id + action + old_values + new_values + user_id + timestamp |
| **Token Session** | Session identifier | session_id + user_id + expires_at + ip_address |
| **2FA Code** | Kode Two-Factor Auth | code (6 digit) + timestamp |

### Data Stores:

| ID | Nama Data Store | Deskripsi | Struktur |
|----|-----------------|-----------|----------|
| **D1** | Users | Data pengguna sistem | id + username + password + role + 2fa_secret + 2fa_enabled + created_at + updated_at + last_login + login_attempts + account_locked |
| **D2** | Dokter | Data dokter | id + username + password + nama + 2fa_secret + 2fa_enabled + created_at + updated_at + last_login |
| **D3** | Booking | Data booking appointment | id + pasien + dokter + tanggal + hari + jam + keluhan |
| **D4** | Pemeriksaan | Data hasil pemeriksaan | id + pasien + dokter + diagnosa + obat + tanggal |
| **D5** | Pembayaran | Data transaksi pembayaran | id + pasien + jumlah + keterangan + tanggal |
| **D6** | Rawat Inap | Data pasien rawat inap | id + pasien + dokter + alasan + status + tanggal |
| **D7** | Riwayat | Riwayat medis pasien | id + pasien + dokter + tanggal + diagnosa |
| **D8** | Security Logs | Log event keamanan | id + user_id + event_type + details + ip_address + user_agent + timestamp |
| **D9** | Audit Logs | Log perubahan data | id + table_name + record_id + action + old_values + new_values + user_id + timestamp |
| **D10** | User Sessions | Sesi aktif pengguna | id + user_id + session_id + ip_address + user_agent + created_at + expires_at + is_active |
| **D11** | Login Attempts | Percobaan login | id + identifier + ip_address + username + success + timestamp + user_agent |
| **D12** | Password Reset Tokens | Token reset password | id + user_id + token + expires_at + used + created_at |
| **D13** | Backup Codes | Kode backup 2FA | id + user_id + code + used + created_at + used_at |
| **D14** | Dokter Jadwal | Jadwal praktek dokter | id + dokter + hari + jam |
| **D15** | Encrypted Data | Data terenkripsi | id + table_name + record_id + field_name + encrypted_value + created_at |

### External Entities:

| Nama Entity | Deskripsi | Interaksi |
|-------------|-----------|-----------|
| **Pasien** | Pengguna yang mendaftar sebagai pasien | Pendaftaran, Login, Booking, Lihat Riwayat |
| **Dokter** | Tenaga medis yang menangani pasien | Login, Lihat Booking, Catat Pemeriksaan, Kelola Jadwal |
| **Admin** | Administrator sistem | Login, Kelola Booking, Kelola Pembayaran, Kelola Rawat Inap, Lihat Laporan, Security Dashboard |

---

## Proses Utama (Main Processes):

### Level 1:

| ID | Nama Proses | Deskripsi | Input | Output |
|----|-------------|-----------|-------|--------|
| **1.0** | Manajemen Autentikasi | Mengelola login, registrasi, 2FA | Data Login, Data Pendaftaran | Token Session, Konfirmasi |
| **2.0** | Manajemen Pasien | Mengelola aktivitas pasien | Request Booking, Request Riwayat | Konfirmasi, Data Riwayat |
| **3.0** | Manajemen Dokter | Mengelola aktivitas dokter | Data Pemeriksaan, Data Jadwal | Konfirmasi, Info Booking |
| **4.0** | Manajemen Admin | Mengelola data sistem | Data CRUD | Laporan, Konfirmasi |
| **5.0** | Manajemen Keamanan | Mengelola keamanan sistem | Security Events, Data Changes | Logs, Audit Trail |

### Level 2 - Proses 1.0:

| ID | Nama Proses | Deskripsi |
|----|-------------|-----------|
| **1.1** | Pendaftaran Akun | Registrasi user baru dengan validasi |
| **1.2** | Login User | Autentikasi user/admin dengan 2FA |
| **1.3** | Login Dokter | Autentikasi dokter dengan 2FA |
| **1.4** | Verifikasi 2FA | Validasi kode Two-Factor Authentication |
| **1.5** | Reset Password | Reset password dengan token email |
| **1.6** | Logout | Destroy session dan cleanup |

### Level 2 - Proses 2.0:

| ID | Nama Proses | Deskripsi |
|----|-------------|-----------|
| **2.1** | Booking Janji Dokter | Pasien membuat appointment dengan dokter |
| **2.2** | Lihat Riwayat Medis | Pasien melihat history pemeriksaan |
| **2.3** | Lihat Jadwal Dokter | Pasien melihat jadwal praktek dokter |
| **2.4** | Kelola Profil | Pasien update data profil |

### Level 2 - Proses 3.0:

| ID | Nama Proses | Deskripsi |
|----|-------------|-----------|
| **3.1** | Lihat Booking | Dokter melihat daftar booking pasien |
| **3.2** | Catat Pemeriksaan | Dokter mencatat hasil pemeriksaan |
| **3.3** | Kelola Jadwal | Dokter manage jadwal praktek |
| **3.4** | Rawat Inap | Dokter request rawat inap untuk pasien |

### Level 2 - Proses 4.0:

| ID | Nama Proses | Deskripsi |
|----|-------------|-----------|
| **4.1** | Kelola Booking | Admin CRUD data booking |
| **4.2** | Kelola Pembayaran | Admin CRUD data pembayaran |
| **4.3** | Kelola Rawat Inap | Admin CRUD data rawat inap |
| **4.4** | Lihat Laporan | Admin generate laporan sistem |
| **4.5** | Security Dashboard | Admin monitoring keamanan |

### Level 2 - Proses 5.0:

| ID | Nama Proses | Deskripsi |
|----|-------------|-----------|
| **5.1** | Rate Limiting | Mencegah brute force attack |
| **5.2** | Security Logging | Mencatat semua security events |
| **5.3** | Audit Trail | Mencatat semua perubahan data |
| **5.4** | Session Management | Mengelola sesi pengguna aktif |
| **5.5** | 2FA Management | Mengelola Two-Factor Authentication |

---

## Karakteristik DFD:

### Notasi yang Digunakan:
- **External Entity (Oval):** Entitas luar sistem (Pasien, Dokter, Admin)
- **Process (Rectangle):** Proses/transformasi data
- **Data Store (Cylinder):** Penyimpanan data (Database tables)
- **Data Flow (Arrow):** Aliran data antar komponen

### Aturan DFD:
1. ✅ Setiap proses harus memiliki input dan output
2. ✅ Data flow harus memiliki nama yang deskriptif
3. ✅ Data store harus diakses minimal satu proses
4. ✅ External entity tidak berkomunikasi langsung
5. ✅ Dekomposisi dilakukan secara konsisten
6. ✅ Balanced DFD (input/output konsisten antar level)

### Fitur Keamanan dalam DFD:
- 🔒 CSRF Token di setiap data flow input
- 🔒 Rate Limiting untuk mencegah abuse
- 🔒 Security Logging untuk audit
- 🔒 Session Management untuk tracking
- 🔒 2FA untuk autentikasi kuat
- 🔒 Audit Trail untuk compliance
- 🔒 Password Hashing (tidak ditampilkan plain text)
- 🔒 Encrypted Data Storage

---

**Dibuat untuk:** RS Sehat Selalu  
**Format:** Mermaid Flowchart  
**Tanggal:** 2025  
**Versi:** 1.0
