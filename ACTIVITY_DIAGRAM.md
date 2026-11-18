# Activity Diagram
## Sistem Informasi RS Sehat Selalu

---

## 1. Activity Diagram - Pendaftaran Akun Pasien

```mermaid
flowchart TD
    Start([Mulai]) --> AccessDaftar[Akses Halaman Pendaftaran]
    AccessDaftar --> InputData[Input Username dan Password]
    InputData --> ConfirmPass[Input Konfirmasi Password]
    ConfirmPass --> SubmitForm[Submit Form]
    
    SubmitForm --> ValidateCSRF{Validasi CSRF Token}
    ValidateCSRF -->|Invalid| ErrorCSRF[Tampilkan Error CSRF]
    ErrorCSRF --> End1([Selesai])
    
    ValidateCSRF -->|Valid| CheckEmpty{Semua Field Terisi?}
    CheckEmpty -->|Tidak| ErrorEmpty[Error: Field Harus Diisi]
    ErrorEmpty --> BackDaftar1[Kembali ke Form Pendaftaran]
    BackDaftar1 --> InputData
    
    CheckEmpty -->|Ya| CheckPassMatch{Password Cocok?}
    CheckPassMatch -->|Tidak| ErrorMatch[Error: Password Tidak Cocok]
    ErrorMatch --> BackDaftar2[Kembali ke Form Pendaftaran]
    BackDaftar2 --> InputData
    
    CheckPassMatch -->|Ya| ValidateUsername{Username Valid?}
    ValidateUsername -->|Tidak| ErrorUsername[Error: Format Username Salah]
    ErrorUsername --> BackDaftar3[Kembali ke Form Pendaftaran]
    BackDaftar3 --> InputData
    
    ValidateUsername -->|Ya| ValidatePassword{Password Kuat?}
    ValidatePassword -->|Tidak| ErrorPassword[Error: Password Terlalu Lemah]
    ErrorPassword --> BackDaftar4[Kembali ke Form Pendaftaran]
    BackDaftar4 --> InputData
    
    ValidatePassword -->|Ya| CheckUsernameExist{Username Sudah Ada?}
    CheckUsernameExist -->|Ya| ErrorExist[Error: Username Sudah Digunakan]
    ErrorExist --> BackDaftar5[Kembali ke Form Pendaftaran]
    BackDaftar5 --> InputData
    
    CheckUsernameExist -->|Tidak| HashPassword[Hash Password dengan Bcrypt]
    HashPassword --> InsertDB[Insert ke Database users]
    InsertDB --> LogRegister[Log Security Event]
    LogRegister --> LogAudit[Log Audit Trail]
    LogAudit --> SuccessRegister[Tampilkan Pesan Berhasil]
    SuccessRegister --> RedirectLogin[Redirect ke Halaman Login]
    RedirectLogin --> End2([Selesai])
```

---

## 2. Activity Diagram - Login Pasien/Admin

```mermaid
flowchart TD
    Start([Mulai]) --> AccessLogin[Akses Halaman Login]
    AccessLogin --> InputCred[Input Username dan Password]
    InputCred --> SubmitLogin[Submit Form Login]
    
    SubmitLogin --> ValidateCSRF{Validasi CSRF Token}
    ValidateCSRF -->|Invalid| ErrorCSRF[Tampilkan Error CSRF]
    ErrorCSRF --> End1([Selesai])
    
    ValidateCSRF -->|Valid| CheckRateLimit{Rate Limit OK?}
    CheckRateLimit -->|Exceeded| ErrorRateLimit[Error: Terlalu Banyak Percobaan]
    ErrorRateLimit --> End2([Selesai])
    
    CheckRateLimit -->|OK| CheckAccountLocked{Akun Terkunci?}
    CheckAccountLocked -->|Ya| ErrorLocked[Error: Akun Terkunci]
    ErrorLocked --> End3([Selesai])
    
    CheckAccountLocked -->|Tidak| QueryUser[Query User dari Database]
    QueryUser --> UserExist{User Ditemukan?}
    
    UserExist -->|Tidak| IncAttemptFail1[Increment Login Attempts]
    IncAttemptFail1 --> LogFailAttempt1[Log Failed Login Attempt]
    LogFailAttempt1 --> ErrorCred1[Error: Username Tidak Ditemukan]
    ErrorCred1 --> BackLogin1[Kembali ke Login]
    BackLogin1 --> InputCred
    
    UserExist -->|Ya| VerifyPassword{Password Valid?}
    VerifyPassword -->|Tidak| IncAttemptFail2[Increment Login Attempts]
    IncAttemptFail2 --> CheckLockout{Attempts >= 5?}
    CheckLockout -->|Ya| LockAccount[Lock Account]
    LockAccount --> LogLockout[Log Account Locked]
    LogLockout --> ErrorLocked2[Error: Akun Dikunci]
    ErrorLocked2 --> End4([Selesai])
    
    CheckLockout -->|Tidak| LogFailAttempt2[Log Failed Login Attempt]
    LogFailAttempt2 --> ErrorCred2[Error: Password Salah]
    ErrorCred2 --> BackLogin2[Kembali ke Login]
    BackLogin2 --> InputCred
    
    VerifyPassword -->|Ya| Check2FA{2FA Enabled?}
    
    Check2FA -->|Ya| Redirect2FA[Redirect ke 2FA Input]
    Redirect2FA --> Input2FA[Input 6-Digit Code]
    Input2FA --> Verify2FA{Kode 2FA Valid?}
    Verify2FA -->|Tidak| Error2FA[Error: Kode 2FA Salah]
    Error2FA --> Input2FA
    Verify2FA -->|Valid| ResetAttempts[Reset Login Attempts]
    
    Check2FA -->|Tidak| ResetAttempts
    ResetAttempts --> CreateSession[Buat Session]
    CreateSession --> UpdateLastLogin[Update Last Login Time]
    UpdateLastLogin --> LogSuccessLogin[Log Successful Login]
    LogSuccessLogin --> CheckRole{Cek Role User}
    
    CheckRole -->|Admin| RedirectAdmin[Redirect ke Admin Dashboard]
    CheckRole -->|Pasien| RedirectPasien[Redirect ke Pasien Dashboard]
    
    RedirectAdmin --> End5([Selesai])
    RedirectPasien --> End6([Selesai])
```

---

## 3. Activity Diagram - Login Dokter

```mermaid
flowchart TD
    Start([Mulai]) --> AccessLogin[Akses Halaman Login Dokter]
    AccessLogin --> InputCred[Input Username dan Password]
    InputCred --> SubmitLogin[Submit Form Login]
    
    SubmitLogin --> ValidateCSRF{Validasi CSRF Token}
    ValidateCSRF -->|Invalid| ErrorCSRF[Tampilkan Error CSRF]
    ErrorCSRF --> End1([Selesai])
    
    ValidateCSRF -->|Valid| CheckRateLimit{Rate Limit OK?}
    CheckRateLimit -->|Exceeded| ErrorRateLimit[Error: Terlalu Banyak Percobaan]
    ErrorRateLimit --> End2([Selesai])
    
    CheckRateLimit -->|OK| CheckAccountLocked{Akun Terkunci?}
    CheckAccountLocked -->|Ya| ErrorLocked[Error: Akun Terkunci]
    ErrorLocked --> End3([Selesai])
    
    CheckAccountLocked -->|Tidak| QueryDokter[Query Dokter dari Database]
    QueryDokter --> DokterExist{Dokter Ditemukan?}
    
    DokterExist -->|Tidak| LogFailAttempt1[Log Failed Login Attempt]
    LogFailAttempt1 --> ErrorCred1[Error: Username Tidak Ditemukan]
    ErrorCred1 --> BackLogin1[Kembali ke Login]
    BackLogin1 --> InputCred
    
    DokterExist -->|Ya| VerifyPassword{Password Valid?}
    VerifyPassword -->|Tidak| LogFailAttempt2[Log Failed Login Attempt]
    LogFailAttempt2 --> ErrorCred2[Error: Password Salah]
    ErrorCred2 --> BackLogin2[Kembali ke Login]
    BackLogin2 --> InputCred
    
    VerifyPassword -->|Ya| Check2FA{2FA Enabled?}
    
    Check2FA -->|Ya| Redirect2FA[Redirect ke 2FA Input]
    Redirect2FA --> Input2FA[Input 6-Digit Code]
    Input2FA --> Verify2FA{Kode 2FA Valid?}
    Verify2FA -->|Tidak| Error2FA[Error: Kode 2FA Salah]
    Error2FA --> Input2FA
    Verify2FA -->|Valid| CreateSession[Buat Session]
    
    Check2FA -->|Tidak| CreateSession
    CreateSession --> UpdateLastLogin[Update Last Login Time]
    UpdateLastLogin --> LogSuccessLogin[Log Successful Login]
    LogSuccessLogin --> RedirectDashboard[Redirect ke Dokter Dashboard]
    RedirectDashboard --> End4([Selesai])
```

---

## 4. Activity Diagram - Booking Janji Dokter (Pasien)

```mermaid
flowchart TD
    Start([Mulai]) --> CheckLogin{Sudah Login?}
    CheckLogin -->|Tidak| RedirectLogin[Redirect ke Login]
    RedirectLogin --> End1([Selesai])
    
    CheckLogin -->|Ya| AccessBooking[Akses Halaman Booking]
    AccessBooking --> ViewDoctorList[Tampilkan Daftar Dokter]
    ViewDoctorList --> SelectDoctor[Pilih Dokter]
    SelectDoctor --> FetchSchedule[Fetch Jadwal Dokter]
    FetchSchedule --> DisplaySchedule[Tampilkan Jadwal Tersedia]
    DisplaySchedule --> SelectSchedule[Pilih Hari dan Jam]
    SelectSchedule --> InputKeluhan[Input Keluhan]
    InputKeluhan --> SubmitBooking[Submit Form Booking]
    
    SubmitBooking --> ValidateCSRF{Validasi CSRF Token}
    ValidateCSRF -->|Invalid| ErrorCSRF[Error CSRF]
    ErrorCSRF --> End2([Selesai])
    
    ValidateCSRF -->|Valid| ValidateInput{Data Valid?}
    ValidateInput -->|Tidak| ErrorInput[Error: Data Tidak Lengkap]
    ErrorInput --> BackBooking[Kembali ke Form]
    BackBooking --> InputKeluhan
    
    ValidateInput -->|Ya| CheckScheduleAvail{Jadwal Tersedia?}
    CheckScheduleAvail -->|Tidak| ErrorSchedule[Error: Jadwal Sudah Penuh]
    ErrorSchedule --> BackSchedule[Kembali Pilih Jadwal]
    BackSchedule --> SelectSchedule
    
    CheckScheduleAvail -->|Ya| InsertBooking[Insert Booking ke Database]
    InsertBooking --> LogAudit[Log Audit Trail]
    LogAudit --> SuccessBooking[Tampilkan Pesan Berhasil]
    SuccessBooking --> ShowBookingInfo[Tampilkan Info Booking]
    ShowBookingInfo --> End3([Selesai])
```

---

## 5. Activity Diagram - Lihat Booking (Dokter)

```mermaid
flowchart TD
    Start([Mulai]) --> CheckLogin{Sudah Login Dokter?}
    CheckLogin -->|Tidak| RedirectLogin[Redirect ke Login Dokter]
    RedirectLogin --> End1([Selesai])
    
    CheckLogin -->|Ya| AccessBookingList[Akses Halaman Booking]
    AccessBookingList --> QueryBookings[Query Booking untuk Dokter Ini]
    QueryBookings --> DisplayBookings[Tampilkan Daftar Booking]
    DisplayBookings --> CheckEmpty{Ada Booking?}
    
    CheckEmpty -->|Tidak| ShowEmpty[Tampilkan Pesan Kosong]
    ShowEmpty --> End2([Selesai])
    
    CheckEmpty -->|Ya| ViewBookingDetails[Lihat Detail Booking]
    ViewBookingDetails --> DokterAction{Pilih Aksi}
    
    DokterAction -->|Hapus| ConfirmDelete{Konfirmasi Hapus?}
    ConfirmDelete -->|Tidak| End3([Selesai])
    ConfirmDelete -->|Ya| DeleteBooking[Hapus Booking]
    DeleteBooking --> LogDeleteAudit[Log Audit Trail Delete]
    LogDeleteAudit --> RefreshList[Refresh Daftar Booking]
    RefreshList --> DisplayBookings
    
    DokterAction -->|Catat Pemeriksaan| RedirectPemeriksaan[Redirect ke Form Pemeriksaan]
    RedirectPemeriksaan --> End4([Selesai])
    
    DokterAction -->|Tidak Ada| End5([Selesai])
```

---

## 6. Activity Diagram - Catat Pemeriksaan (Dokter)

```mermaid
flowchart TD
    Start([Mulai]) --> CheckLogin{Sudah Login Dokter?}
    CheckLogin -->|Tidak| RedirectLogin[Redirect ke Login Dokter]
    RedirectLogin --> End1([Selesai])
    
    CheckLogin -->|Ya| AccessPemeriksaan[Akses Form Pemeriksaan]
    AccessPemeriksaan --> InputPasien[Input/Pilih Nama Pasien]
    InputPasien --> InputDiagnosa[Input Diagnosa]
    InputDiagnosa --> InputObat[Input Obat]
    InputObat --> InputTanggal[Input Tanggal Pemeriksaan]
    InputTanggal --> SubmitForm[Submit Form]
    
    SubmitForm --> ValidateCSRF{Validasi CSRF Token}
    ValidateCSRF -->|Invalid| ErrorCSRF[Error CSRF]
    ErrorCSRF --> End2([Selesai])
    
    ValidateCSRF -->|Valid| ValidateInput{Data Valid?}
    ValidateInput -->|Tidak| ErrorInput[Error: Data Tidak Lengkap]
    ErrorInput --> BackForm[Kembali ke Form]
    BackForm --> InputPasien
    
    ValidateInput -->|Ya| InsertPemeriksaan[Insert ke Database pemeriksaan]
    InsertPemeriksaan --> InsertRiwayat[Insert ke Database riwayat]
    InsertRiwayat --> LogAudit[Log Audit Trail]
    LogAudit --> CheckRawatInap{Perlu Rawat Inap?}
    
    CheckRawatInap -->|Ya| InsertRawatInap[Insert Permintaan Rawat Inap]
    InsertRawatInap --> LogAuditRawat[Log Audit Trail Rawat Inap]
    LogAuditRawat --> SuccessFull[Tampilkan Pesan Berhasil + Rawat Inap]
    SuccessFull --> End3([Selesai])
    
    CheckRawatInap -->|Tidak| SuccessOnly[Tampilkan Pesan Berhasil]
    SuccessOnly --> End4([Selesai])
```

---

## 7. Activity Diagram - Kelola Rawat Inap (Admin)

```mermaid
flowchart TD
    Start([Mulai]) --> CheckLogin{Sudah Login Admin?}
    CheckLogin -->|Tidak| RedirectLogin[Redirect ke Login]
    RedirectLogin --> End1([Selesai])
    
    CheckLogin -->|Ya| AccessRawatInap[Akses Halaman Rawat Inap]
    AccessRawatInap --> QueryRawatInap[Query Data Rawat Inap]
    QueryRawatInap --> DisplayList[Tampilkan Daftar Rawat Inap]
    DisplayList --> AdminAction{Pilih Aksi}
    
    AdminAction -->|Tambah Baru| RedirectForm[Redirect ke Form Tambah]
    RedirectForm --> InputPasien[Input Nama Pasien]
    InputPasien --> InputDokter[Input Nama Dokter]
    InputDokter --> InputAlasan[Input Alasan Rawat Inap]
    InputAlasan --> SubmitTambah[Submit Form]
    
    SubmitTambah --> ValidateInput1{Data Valid?}
    ValidateInput1 -->|Tidak| ErrorInput1[Error: Data Tidak Lengkap]
    ErrorInput1 --> InputPasien
    
    ValidateInput1 -->|Ya| InsertRawatInap[Insert ke Database]
    InsertRawatInap --> LogAudit1[Log Audit Trail]
    LogAudit1 --> SuccessTambah[Tampilkan Pesan Berhasil]
    SuccessTambah --> RefreshList1[Refresh Daftar]
    RefreshList1 --> DisplayList
    
    AdminAction -->|Update Status| SelectRow[Pilih Baris Data]
    SelectRow --> ShowUpdateForm[Tampilkan Form Update]
    ShowUpdateForm --> SelectStatus[Pilih Status Baru]
    SelectStatus --> SubmitUpdate[Submit Update]
    
    SubmitUpdate --> ValidateInput2{Data Valid?}
    ValidateInput2 -->|Tidak| ErrorInput2[Error: Status Invalid]
    ErrorInput2 --> SelectStatus
    
    ValidateInput2 -->|Ya| UpdateDB[Update Database]
    UpdateDB --> LogAudit2[Log Audit Trail]
    LogAudit2 --> SuccessUpdate[Tampilkan Pesan Berhasil]
    SuccessUpdate --> RefreshList2[Refresh Daftar]
    RefreshList2 --> DisplayList
    
    AdminAction -->|Hapus| ConfirmDelete{Konfirmasi Hapus?}
    ConfirmDelete -->|Tidak| End2([Selesai])
    ConfirmDelete -->|Ya| DeleteRawatInap[Hapus dari Database]
    DeleteRawatInap --> LogAudit3[Log Audit Trail Delete]
    LogAudit3 --> SuccessDelete[Tampilkan Pesan Berhasil]
    SuccessDelete --> RefreshList3[Refresh Daftar]
    RefreshList3 --> DisplayList
    
    AdminAction -->|Tidak Ada| End3([Selesai])
```

---

## 8. Activity Diagram - Kelola Pembayaran (Admin)

```mermaid
flowchart TD
    Start([Mulai]) --> CheckLogin{Sudah Login Admin?}
    CheckLogin -->|Tidak| RedirectLogin[Redirect ke Login]
    RedirectLogin --> End1([Selesai])
    
    CheckLogin -->|Ya| AccessPembayaran[Akses Halaman Pembayaran]
    AccessPembayaran --> QueryPembayaran[Query Data Pembayaran]
    QueryPembayaran --> DisplayList[Tampilkan Daftar Pembayaran]
    DisplayList --> AdminAction{Pilih Aksi}
    
    AdminAction -->|Tambah Baru| RedirectForm[Redirect ke Form Tambah]
    RedirectForm --> InputPasien[Input Nama Pasien]
    InputPasien --> InputJumlah[Input Jumlah Pembayaran]
    InputJumlah --> InputKeterangan[Input Keterangan]
    InputKeterangan --> InputTanggal[Input Tanggal]
    InputTanggal --> SubmitTambah[Submit Form]
    
    SubmitTambah --> ValidateInput1{Data Valid?}
    ValidateInput1 -->|Tidak| ErrorInput1[Error: Data Tidak Lengkap]
    ErrorInput1 --> InputPasien
    
    ValidateInput1 -->|Ya| ValidateAmount{Jumlah > 0?}
    ValidateAmount -->|Tidak| ErrorAmount[Error: Jumlah Harus > 0]
    ErrorAmount --> InputJumlah
    
    ValidateAmount -->|Ya| InsertPembayaran[Insert ke Database]
    InsertPembayaran --> LogAudit1[Log Audit Trail]
    LogAudit1 --> SuccessTambah[Tampilkan Pesan Berhasil]
    SuccessTambah --> RefreshList1[Refresh Daftar]
    RefreshList1 --> DisplayList
    
    AdminAction -->|Hapus| ConfirmDelete{Konfirmasi Hapus?}
    ConfirmDelete -->|Tidak| End2([Selesai])
    ConfirmDelete -->|Ya| DeletePembayaran[Hapus dari Database]
    DeletePembayaran --> LogAudit2[Log Audit Trail Delete]
    LogAudit2 --> SuccessDelete[Tampilkan Pesan Berhasil]
    SuccessDelete --> RefreshList2[Refresh Daftar]
    RefreshList2 --> DisplayList
    
    AdminAction -->|Tidak Ada| End3([Selesai])
```

---

## 9. Activity Diagram - Lihat Riwayat Medis (Pasien)

```mermaid
flowchart TD
    Start([Mulai]) --> CheckLogin{Sudah Login Pasien?}
    CheckLogin -->|Tidak| RedirectLogin[Redirect ke Login]
    RedirectLogin --> End1([Selesai])
    
    CheckLogin -->|Ya| AccessRiwayat[Akses Halaman Riwayat]
    AccessRiwayat --> QueryRiwayat[Query Riwayat untuk Pasien Ini]
    QueryRiwayat --> DisplayRiwayat[Tampilkan Daftar Riwayat]
    DisplayRiwayat --> CheckEmpty{Ada Riwayat?}
    
    CheckEmpty -->|Tidak| ShowEmpty[Tampilkan Pesan Kosong]
    ShowEmpty --> End2([Selesai])
    
    CheckEmpty -->|Ya| ViewDetails{Lihat Detail?}
    ViewDetails -->|Ya| ShowDetail[Tampilkan Detail Diagnosa]
    ShowDetail --> BackList[Kembali ke Daftar]
    BackList --> DisplayRiwayat
    
    ViewDetails -->|Tidak| End3([Selesai])
```

---

## 10. Activity Diagram - Setup 2FA (Two-Factor Authentication)

```mermaid
flowchart TD
    Start([Mulai]) --> CheckLogin{Sudah Login?}
    CheckLogin -->|Tidak| RedirectLogin[Redirect ke Login]
    RedirectLogin --> End1([Selesai])
    
    CheckLogin -->|Ya| AccessSetting[Akses Setting Keamanan]
    AccessSetting --> Check2FAStatus{2FA Sudah Aktif?}
    
    Check2FAStatus -->|Ya| Show2FAActive[Tampilkan Status Aktif]
    Show2FAActive --> Option2FA{Pilihan User}
    Option2FA -->|Disable 2FA| ConfirmDisable{Konfirmasi Disable?}
    ConfirmDisable -->|Tidak| End2([Selesai])
    ConfirmDisable -->|Ya| InputPassDisable[Input Password untuk Konfirmasi]
    InputPassDisable --> VerifyPassDisable{Password Benar?}
    VerifyPassDisable -->|Tidak| ErrorPass1[Error: Password Salah]
    ErrorPass1 --> InputPassDisable
    VerifyPassDisable -->|Ya| Disable2FA[Disable 2FA di Database]
    Disable2FA --> DeleteBackupCodes[Hapus Backup Codes]
    DeleteBackupCodes --> LogDisable[Log Security Event]
    LogDisable --> SuccessDisable[Tampilkan Pesan Berhasil]
    SuccessDisable --> End3([Selesai])
    
    Check2FAStatus -->|Tidak| Enable2FA[Pilih Enable 2FA]
    Enable2FA --> GenerateSecret[Generate 2FA Secret]
    GenerateSecret --> GenerateQR[Generate QR Code]
    GenerateQR --> DisplayQR[Tampilkan QR Code]
    DisplayQR --> InstructScan[Instruksi Scan dengan Authenticator App]
    InstructScan --> InputVerifyCode[Input Kode Verifikasi]
    InputVerifyCode --> VerifyCode{Kode Valid?}
    
    VerifyCode -->|Tidak| ErrorCode[Error: Kode Salah]
    ErrorCode --> InputVerifyCode
    
    VerifyCode -->|Ya| Save2FA[Simpan 2FA Secret ke Database]
    Save2FA --> GenerateBackupCodes[Generate 10 Backup Codes]
    GenerateBackupCodes --> SaveBackupCodes[Simpan Backup Codes ke Database]
    SaveBackupCodes --> DisplayBackupCodes[Tampilkan Backup Codes]
    DisplayBackupCodes --> WarningBackupCodes[Peringatan Simpan Backup Codes]
    WarningBackupCodes --> LogEnable[Log Security Event]
    LogEnable --> Success2FA[Tampilkan Pesan Berhasil]
    Success2FA --> End4([Selesai])
    
    Option2FA -->|Regenerate Backup Codes| ConfirmRegen{Konfirmasi Regenerate?}
    ConfirmRegen -->|Tidak| End5([Selesai])
    ConfirmRegen -->|Ya| DeleteOldCodes[Hapus Backup Codes Lama]
    DeleteOldCodes --> GenerateBackupCodes
```

---

## 11. Activity Diagram - Reset Password

```mermaid
flowchart TD
    Start([Mulai]) --> AccessReset[Akses Halaman Reset Password]
    AccessReset --> InputUsername[Input Username]
    InputUsername --> SubmitReset[Submit Request Reset]
    
    SubmitReset --> ValidateCSRF{Validasi CSRF Token}
    ValidateCSRF -->|Invalid| ErrorCSRF[Error CSRF]
    ErrorCSRF --> End1([Selesai])
    
    ValidateCSRF -->|Valid| CheckUser{User Ditemukan?}
    CheckUser -->|Tidak| ShowGenericMsg[Tampilkan Pesan Generic]
    ShowGenericMsg --> End2([Selesai])
    
    CheckUser -->|Ya| GenerateToken[Generate Reset Token]
    GenerateToken --> SaveToken[Simpan Token ke Database]
    SaveToken --> SetExpiry[Set Expiry 1 Jam]
    SetExpiry --> SendEmail[Kirim Email dengan Link Reset]
    SendEmail --> LogReset[Log Security Event]
    LogReset --> ShowSuccessMsg[Tampilkan Pesan Sukses]
    ShowSuccessMsg --> End3([Selesai])
    
    ShowSuccessMsg --> UserClickLink[User Klik Link di Email]
    UserClickLink --> ValidateToken{Token Valid & Belum Expired?}
    
    ValidateToken -->|Tidak| ErrorToken[Error: Token Invalid/Expired]
    ErrorToken --> End4([Selesai])
    
    ValidateToken -->|Ya| ShowResetForm[Tampilkan Form Password Baru]
    ShowResetForm --> InputNewPass[Input Password Baru]
    InputNewPass --> InputConfirmPass[Konfirmasi Password Baru]
    InputConfirmPass --> SubmitNewPass[Submit Password Baru]
    
    SubmitNewPass --> ValidateNewPass{Password Valid & Cocok?}
    ValidateNewPass -->|Tidak| ErrorNewPass[Error: Password Invalid]
    ErrorNewPass --> InputNewPass
    
    ValidateNewPass -->|Ya| HashNewPass[Hash Password Baru]
    HashNewPass --> UpdatePassword[Update Password di Database]
    UpdatePassword --> MarkTokenUsed[Tandai Token Sudah Digunakan]
    MarkTokenUsed --> LogPasswordChange[Log Security Event]
    LogPasswordChange --> ShowSuccessReset[Tampilkan Pesan Berhasil]
    ShowSuccessReset --> RedirectLogin[Redirect ke Login]
    RedirectLogin --> End5([Selesai])
```

---

## 12. Activity Diagram - Logout

```mermaid
flowchart TD
    Start([Mulai]) --> CheckLogin{Sudah Login?}
    CheckLogin -->|Tidak| RedirectHome[Redirect ke Home]
    RedirectHome --> End1([Selesai])
    
    CheckLogin -->|Ya| ClickLogout[User Klik Logout]
    ClickLogout --> GetSessionID[Ambil Session ID]
    GetSessionID --> UpdateSessionDB[Update Session Status: is_active = 0]
    UpdateSessionDB --> DestroySession[Destroy PHP Session]
    DestroySession --> ClearCookies[Clear Session Cookies]
    ClearCookies --> LogLogout[Log Security Event: LOGOUT]
    LogLogout --> ShowLogoutMsg[Tampilkan Pesan Logout Berhasil]
    ShowLogoutMsg --> RedirectLogin[Redirect ke Halaman Login]
    RedirectLogin --> End2([Selesai])
```

---

## 13. Activity Diagram - Admin Kelola Booking

```mermaid
flowchart TD
    Start([Mulai]) --> CheckLogin{Sudah Login Admin?}
    CheckLogin -->|Tidak| RedirectLogin[Redirect ke Login]
    RedirectLogin --> End1([Selesai])
    
    CheckLogin -->|Ya| AccessBooking[Akses Halaman Kelola Booking]
    AccessBooking --> QueryAllBooking[Query Semua Booking]
    QueryAllBooking --> DisplayBookings[Tampilkan Daftar Booking]
    DisplayBookings --> CheckEmpty{Ada Booking?}
    
    CheckEmpty -->|Tidak| ShowEmpty[Tampilkan Pesan Kosong]
    ShowEmpty --> End2([Selesai])
    
    CheckEmpty -->|Ya| AdminAction{Pilih Aksi}
    
    AdminAction -->|View Detail| ViewDetail[Tampilkan Detail Booking]
    ViewDetail --> BackList1[Kembali ke Daftar]
    BackList1 --> DisplayBookings
    
    AdminAction -->|Hapus| SelectBooking[Pilih Booking]
    SelectBooking --> ConfirmDelete{Konfirmasi Hapus?}
    ConfirmDelete -->|Tidak| End3([Selesai])
    
    ConfirmDelete -->|Ya| DeleteBooking[Hapus dari Database]
    DeleteBooking --> LogAuditDelete[Log Audit Trail Delete]
    LogAuditDelete --> SuccessDelete[Tampilkan Pesan Berhasil]
    SuccessDelete --> RefreshList[Refresh Daftar]
    RefreshList --> DisplayBookings
    
    AdminAction -->|Export Data| SelectFormat[Pilih Format Export]
    SelectFormat --> GenerateFile[Generate File]
    GenerateFile --> DownloadFile[Download File]
    DownloadFile --> End4([Selesai])
    
    AdminAction -->|Tidak Ada| End5([Selesai])
```

---

## 14. Activity Diagram - Dokter Kelola Jadwal

```mermaid
flowchart TD
    Start([Mulai]) --> CheckLogin{Sudah Login Dokter?}
    CheckLogin -->|Tidak| RedirectLogin[Redirect ke Login Dokter]
    RedirectLogin --> End1([Selesai])
    
    CheckLogin -->|Ya| AccessJadwal[Akses Halaman Jadwal]
    AccessJadwal --> QueryJadwal[Query Jadwal untuk Dokter Ini]
    QueryJadwal --> DisplayJadwal[Tampilkan Jadwal Praktek]
    DisplayJadwal --> DokterAction{Pilih Aksi}
    
    DokterAction -->|Tambah Jadwal| RedirectForm[Redirect ke Form Tambah]
    RedirectForm --> SelectHari[Pilih Hari]
    SelectHari --> InputJamMulai[Input Jam Mulai]
    InputJamMulai --> InputJamSelesai[Input Jam Selesai]
    InputJamSelesai --> SubmitJadwal[Submit Form]
    
    SubmitJadwal --> ValidateInput{Data Valid?}
    ValidateInput -->|Tidak| ErrorInput[Error: Data Tidak Valid]
    ErrorInput --> SelectHari
    
    ValidateInput -->|Ya| CheckDuplicate{Jadwal Bentrok?}
    CheckDuplicate -->|Ya| ErrorDuplicate[Error: Jadwal Sudah Ada]
    ErrorDuplicate --> SelectHari
    
    CheckDuplicate -->|Tidak| InsertJadwal[Insert ke Database]
    InsertJadwal --> LogAudit1[Log Audit Trail]
    LogAudit1 --> SuccessTambah[Tampilkan Pesan Berhasil]
    SuccessTambah --> RefreshJadwal1[Refresh Jadwal]
    RefreshJadwal1 --> DisplayJadwal
    
    DokterAction -->|Hapus Jadwal| SelectJadwal[Pilih Jadwal]
    SelectJadwal --> ConfirmDelete{Konfirmasi Hapus?}
    ConfirmDelete -->|Tidak| End2([Selesai])
    
    ConfirmDelete -->|Ya| CheckBookingExist{Ada Booking di Jadwal Ini?}
    CheckBookingExist -->|Ya| WarningBooking[Peringatan: Ada Booking]
    WarningBooking --> ConfirmForceDelete{Tetap Hapus?}
    ConfirmForceDelete -->|Tidak| End3([Selesai])
    ConfirmForceDelete -->|Ya| DeleteJadwal[Hapus Jadwal]
    
    CheckBookingExist -->|Tidak| DeleteJadwal
    DeleteJadwal --> LogAudit2[Log Audit Trail Delete]
    LogAudit2 --> SuccessDelete[Tampilkan Pesan Berhasil]
    SuccessDelete --> RefreshJadwal2[Refresh Jadwal]
    RefreshJadwal2 --> DisplayJadwal
    
    DokterAction -->|Tidak Ada| End4([Selesai])
```

---

## Penjelasan Activity Diagram

### Simbol yang Digunakan:
- **Start/End (Rounded Rectangle):** Titik mulai dan selesai
- **Process (Rectangle):** Proses/aksi yang dilakukan sistem
- **Decision (Diamond):** Keputusan/kondisi dengan cabang Ya/Tidak
- **Arrow:** Alur proses

### Fitur Keamanan yang Tercakup:
1. **CSRF Token Validation** - Semua form divalidasi
2. **Rate Limiting** - Mencegah brute force attack
3. **Account Locking** - Setelah 5x percobaan gagal
4. **Two-Factor Authentication (2FA)** - Lapisan keamanan tambahan
5. **Password Hashing** - Bcrypt untuk menyimpan password
6. **Security Logging** - Semua event keamanan dicatat
7. **Audit Trail** - Semua perubahan data dicatat
8. **Session Management** - Tracking sesi aktif
9. **Password Reset** - Dengan token expiry
10. **Input Validation** - Semua input divalidasi

### Proses Utama:
1. **Autentikasi** - Login, Logout, 2FA
2. **Manajemen Pasien** - Pendaftaran, Booking, Riwayat
3. **Manajemen Dokter** - Login, Lihat Booking, Catat Pemeriksaan, Kelola Jadwal
4. **Manajemen Admin** - Kelola Booking, Pembayaran, Rawat Inap
5. **Keamanan** - Setup 2FA, Reset Password, Logout

---

**Dibuat untuk:** RS Sehat Selalu  
**Format:** Mermaid Flowchart  
**Tanggal:** 2025
