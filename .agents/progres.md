# Progress & Roadmap Pengerjaan
## Smart Inventory & Demand Forecasting System

Dokumen ini melacak seluruh tahapan pengerjaan proyek dari awal hingga siap produksi sesuai dengan [PRD.md](file:///c:/Coding/skripsi/satu/skripsi-satu/.agents/PRD.md), [Agent.md](file:///c:/Coding/skripsi/satu/skripsi-satu/.agents/Agent.md), [architecture.md](file:///c:/Coding/skripsi/satu/skripsi-satu/.agents/architecture.md), dan [db.md](file:///c:/Coding/skripsi/satu/skripsi-satu/.agents/db.md).

---

## Ringkasan Fase

| Fase | Nama Fase | Status | Fokus Utama |
|---|---|---|---|
| **Phase 0** | Environment & Project Setup | `COMPLETED` | Inisialisasi Laravel, Git, CI Workflow, Environment |
| **Phase 1** | Project Foundation & Auth | `COMPLETED` | Authentication, RBAC (Roles & Permissions), Base Layout & Navigation |
| **Phase 2** | Master Data Management | `COMPLETED` | Schema, Model, CRUD Category, Unit, Supplier, Warehouse & Locations, Product |
| **Phase 3** | Inventory Core & Ledger | `NOT STARTED` | Dual-layer Inventory (Stock & Ledger), Stock In/Out, Transfer, Adjustment |
| **Phase 4** | Purchasing & Sales Integration | `NOT STARTED` | Purchase Orders, Receiving -> Stock In, Sales -> Stock Out |
| **Phase 5** | Stock Card & Analytics Dashboard | `NOT STARTED` | Kartu Stok real-time, KPI Widgets, Interactive Charts (ApexCharts) |
| **Phase 6** | Demand Forecasting Engine | `NOT STARTED` | Moving Average, Exponential Smoothing, Forecast History & Accuracy |
| **Phase 7** | Replenishment & Decision Engine | `NOT STARTED` | Safety Stock, ROP, Days Until Stockout, Restock Recommendations |
| **Phase 8** | Automation & Notifications | `NOT STARTED` | Queue Workers, Scheduled Jobs, Email & WhatsApp Alerts |
| **Phase 9** | Reports & Export Engine | `NOT STARTED` | Laporan Mutasi & Stok, Export Excel (Maatwebsite) & PDF |
| **Phase 10** | Testing, Hardening & Security | `NOT STARTED` | Integrity Tests, Concurrency Tests, Policy Audit, Performance |
| **Phase 11** | Production Readiness & Deployment | `NOT STARTED` | Production Config, Optimizations, Backup & Maintenance |

---

## Breakdown Detail Setiap Fase

---

### Phase 0 — Environment & Project Setup
> **Tujuan**: Memastikan lingkungan pengembangan lokal dan CI/CD siap pakai serta terkonfigurasi dengan baik.

- [x] Inisialisasi project Laravel 12 dengan PHP 8.2+.
- [x] Konfigurasi environment database lokal (SQLite/MySQL).
- [x] Setup GitHub Actions CI Workflow (`.github/workflows/ci.yml`) untuk automated testing pada PHP 8.2 & 8.3.
- [x] Standardisasi dokumentasi teknis dalam folder `.agents/`.

**Acceptance Criteria**:
- `php artisan test` dan `php artisan migrate` berjalan sukses tanpa error.

---

### Phase 1 — Project Foundation & Authentication (RBAC)
> **Tujuan**: Membangun fondasi sistem autentikasi pengguna, manajemen hak akses berbasis role, serta tata letak antarmuka utama.

- [x] **Autentikasi Pengguna**:
  - Login, Logout, Update Profile, Ubah Password.
  - Proteksi akun nonaktif (`is_active = false`).
- [x] **Role & Permission Management (RBAC)**:
  - Definisi Role: `Super Admin`, `Warehouse Staff`, `Purchasing Staff`, `Manager`.
  - Definisi Permission granular per modul domain.
  - Relasi Many-to-Many (`roles`, `permissions`, `role_user`, `permission_role`).
  - Middleware `EnsureUserHasRole` (`role:slug`) dan server-side authorization Gates (`Gate::before` untuk Super Admin).
  - Directive Blade `@role` dan `@permission`.
  - Seeder default roles, permissions, dan akun demo (`RbacSeeder`).
- [x] **Base UI & Navigation Layout**:
  - Layout Dashboard responsif (`layouts/app.blade.php`, `layouts/guest.blade.php`) menggunakan Tailwind CSS & Alpine.js.
  - Sidebar dinamis dengan menu kondisional berbasis peran dan izin user.
  - Topbar dengan role badge, profile drawer, dan flash alerts (success, error, info).
  - Tampilan Dashboard (`dashboard.blade.php`) dengan kartu metrik dan pintasan cepat.
  - Tampilan Profil (`profile/edit.blade.php`) untuk update nama, email, nomor telepon, dan password.
- [x] **Automated Tests**:
  - `AuthTest.php` (8 test assertions).
  - `RbacTest.php` (4 test assertions).

**Acceptance Criteria**:
- User dapat login dan mengakses modul sesuai hak akses perannya.
- Proteksi server-side aktif mencegah akses ilegal via URL langsung.

---

### Phase 2 — Master Data Management
> **Tujuan**: Menyediakan manajemen data master yang lengkap dan tervalidasi sebagai fondasi operasional inventory.

- [x] **Database Schema & Eloquent Models**:
  - `categories` (id, name, slug, description, is_active, timestamps, soft deletes).
  - `units` (id, name, code, symbol, description, is_active, timestamps, soft deletes).
  - `suppliers` (id, code, name, contact_person, phone, email, address, default_lead_time_days, is_active, timestamps, soft deletes).
  - `warehouses` (id, code, name, address, description, is_active, timestamps, soft deletes).
  - `warehouse_locations` (id, warehouse_id, code, name, type [RACK, ZONE, BIN, AISLE, DEFAULT], description, is_active, timestamps, soft deletes).
  - `products` (id, sku [indexed], name, category_id, unit_id, supplier_id, purchase_price, selling_price, minimum_stock, lead_time_days, forecast_method, is_active, timestamps, soft deletes).
  - Relasi Eloquent lengkap (`belongsTo`, `hasMany`).
- [x] **Warehouse Locations Management**:
  - Sub-lokasi internal gudang (`warehouse_locations`: rak, zona, bin, dsb.) dengan manajemen terintegrasi di dalam halaman detail gudang (`warehouses.show`).
  - Modal interaktif Alpine.js untuk tambah dan edit sub-lokasi beserta toggle status aktif/nonaktif.
- [x] **Form Requests & Backend Validation**:
  - `CategoryRequest`: Validasi nama & slug unik (mengabaikan soft deletes), deskripsi, dan status aktif.
  - `UnitRequest`: Validasi kode unik dengan auto-uppercase (e.g. `PCS`, `BOX`, `KG`), nama satuan, simbol, dan status.
  - `SupplierRequest`: Validasi kode supplier unik dengan auto-uppercase, lead time $\ge 0$, kontak PIC, email, dan telepon.
  - `WarehouseRequest`: Validasi kode gudang unik dengan auto-uppercase, nama, alamat, dan deskripsi.
  - `WarehouseLocationRequest`: Validasi kode unik per gudang (composite unique constraint), tipe lokasi terdaftar.
  - `ProductRequest`: Validasi SKU unik dengan auto-uppercase, validasi foreign keys (`category_id`, `unit_id`, `supplier_id`), harga beli & jual non-negatif, minimum stock $\ge 0$, lead time $\ge 0$, dan metode peramalan (`MOVING_AVERAGE`, `EXPONENTIAL_SMOOTHING`).
- [x] **Controllers & Routing**:
  - `CategoryController`, `UnitController`, `SupplierController`, `WarehouseController`, `WarehouseLocationController`, `ProductController`.
  - Resource routes lengkap di bawah prefix `/master-data`.
  - Quick status toggle endpoint (`PATCH .../toggle-status`) untuk seluruh entitas master data.
  - Perlindungan penghapusan (*safe delete*): Mencegah penghapusan master data jika masih dirujuk oleh produk aktif.
- [x] **UI CRUD & Interaksi (Dark-Slate Modern Aesthetic)**:
  - Tampilan CRUD lengkap: Categories (`index`, `create`, `edit`), Units (`index`, `create`, `edit`), Suppliers (`index`, `create`, `edit`, `show`), Warehouses (`index`, `create`, `edit`, `show`), Products (`index`, `create`, `edit`, `show`).
  - Overview KPI Stats Cards pada setiap modul (Total, Aktif, Nonaktif, Rata-rata margin, Distribusi lokasi).
  - Fitur Search instan dan Filter dinamis (kategori, supplier, status aktif, metode forecast) dengan persistensi query pagination.
  - Integrasi navigasi menu sidebar dinamis sesuai role & permission.
- [x] **Seeder Master Data**:
  - `MasterDataSeeder` berisi katalog data realistis: Kategori hardware & networking, satuan UoM, vendor supplier terpercaya, gudang Jakarta & Surabaya dengan sub-lokasi rak/zona, serta produk SKU lengkap.
- [x] **Automated Tests**:
  - `MasterDataSchemaTest.php` (2 test cases: integritas tabel, kolom, dan relasi).
  - `CategoryTest.php` (6 test cases: listing, create, unique constraint, update, soft delete, toggle status).
  - `UnitTest.php` (6 test cases: listing, create, unique uppercase code, update, soft delete, toggle status).
  - `SupplierTest.php` (7 test cases: listing, create, lead time validation, show page, update, soft delete, toggle status).
  - `WarehouseTest.php` (5 test cases: listing, create, internal location management, soft delete, toggle status).
  - `ProductTest.php` (8 test cases: listing, create, validation rules, show page & profit margin, update, soft delete, toggle status, search & filters).

**Acceptance Criteria**:
- Seluruh data master (Category, Unit, Supplier, Warehouse, WarehouseLocation, Product) dapat dibuat, diubah, dinonaktifkan, dihapus secara aman, dicari, dan difilter dengan validasi ketat.
- Seluruh unit & feature tests (48 tests, 161 assertions) berstatus `100% PASS`.

---

### Phase 3 — Inventory Core & Transaction Ledger
> **Tujuan**: Membangun mesin inti pencatatan persediaan dengan arsitektur dua lapis (*Current Stock* dan *Immutable Ledger*).

- [ ] **Skema Database Inventory**:
  - `inventory_stocks` (product_id, warehouse_id, quantity, reserved_quantity, available_quantity) -> Unique composite `(product_id, warehouse_id)`.
  - `inventory_transactions` (product_id, warehouse_id, transaction_type, quantity, stock_before, stock_after, reference_type, reference_id, transaction_date, performed_by).
- [ ] **Service & Action Layer (Transaksional)**:
  - `InventoryService` / Action Classes: `CreateStockIn`, `CreateStockOut`, `TransferStock`, `AdjustStock`.
  - Pemanfaatan `DB::transaction()` dan row-locking `lockForUpdate()` untuk mencegah *race conditions*.
- [ ] **Aturan Mutasi Stok**:
  - **Stock In**: Pembelian, Retur Masuk, Penyesuaian Masuk.
  - **Stock Out**: Penjualan, Retur Keluar, Penyesuaian Keluar (validasi stok mencukupi).
  - **Stock Transfer**: Atomik (TRANSFER_OUT di gudang asal & TRANSFER_IN di gudang tujuan dalam 1 transaksi DB).
  - **Stock Adjustment**: Koreksi stok fisik dengan pencatatan selisih dan catatan alasan.
- [ ] **Stock Card (Kartu Stok)**:
  - Riwayat lengkap pergerakan stok per produk & gudang secara kronologis real-time.

**Acceptance Criteria**:
- Nilai stok pada `inventory_stocks` selalu identik dengan kalkulasi kumulatif `inventory_transactions`.
- Tidak ada mutasi stok yang terjadi tanpa menghasilkan record ledger transaksi.

---

### Phase 4 — Purchasing & Sales Integration
> **Tujuan**: Menghubungkan proses bisnis pengadaan (*Purchasing*) dan penjualan (*Sales*) langsung ke mutasi stok inventaris.

- [ ] **Purchasing Module**:
  - Skema `purchases` & `purchase_items`.
  - Status PO: `DRAFT`, `ORDERED`, `PARTIALLY_RECEIVED`, `RECEIVED`, `CANCELLED`.
  - Alur Penerimaan (*Receiving*): Pembuatan Purchase Order tidak langsung menambah stok; stok masuk dipicu saat proses penerimaan barang fisik terjadi.
- [ ] **Sales Module**:
  - Skema `sales` & `sale_items`.
  - Status Penjualan: `DRAFT`, `COMPLETED`, `CANCELLED`.
  - Pengurangan stok otomatis (*Stock Out*) saat status penjualan `COMPLETED`.

**Acceptance Criteria**:
- Transaksi Purchase yang berstatus diterima menghasilkan transaksi Stock In secara otomatis.
- Transaksi Sales yang selesai memotong stok via Stock Out secara atomik.

---

### Phase 5 — Stock Card & Analytics Dashboard
> **Tujuan**: Menyediakan antarmuka analitik dan kartu stok yang interaktif untuk memonitor kesehatan inventaris bisnis.

- [ ] **Kartu Stok Interaktif**:
  - Filter berdasarkan produk, gudang, rentang tanggal, dan tipe transaksi.
- [ ] **Dashboard KPI Widgets**:
  - Total Produk, Total Nilai Persediaan (Inventory Value), Total Kuantitas Stok.
  - Jumlah Produk Kritis (*Critical Stock*), Stok Menipis (*Low Stock*), dan Stok Kosong (*Out of Stock*).
  - Indikator *Fast-Moving*, *Slow-Moving*, dan *Dead Stock*.
  - Rasio *Inventory Turnover*.
- [ ] **Visualisasi Interaktif (ApexCharts / Chart.js)**:
  - Grafik tren permintaan (*Demand Trend*).
  - Grafik tren stok historis (*Stock Trend*).
  - Distribusi stok antar-gudang.
  - Grafik perbandingan *Forecast vs Actual*.

**Acceptance Criteria**:
- Dashboard menampilkan data real-time yang akurat tanpa membebani performa query database (eager loading & optimasi agregasi).

---

### Phase 6 — Demand Forecasting Engine
> **Tujuan**: Mengimplementasikan mesin komputasi peramalan kebutuhan stok di masa depan berbasis histori penjualan.

- [ ] **Ekstraksi Data Demand Historis**:
  - Mengambil data murni dari transaksi riil pelanggan (transaksi `SALE`).
- [ ] **Metode Peramalan**:
  - **Moving Average (MA)**: Window parameter terkonfigurasi (7 hari, 14 hari, 30 hari).
  - **Exponential Smoothing (ES)**: Parameter bobot alpha $\alpha$ terkonfigurasi ($0 < \alpha \le 1$).
- [ ] **Persistence & History Peramalan**:
  - Tabel `demand_forecasts` untuk menyimpan hasil prediksi tiap periode, metode yang digunakan, dan parameter.
- [ ] **Evaluasi Akurasi**:
  - Perhitungan metrik error: *Absolute Error*, *Percentage Error*, *MAE (Mean Absolute Error)*, dan *MAPE (Mean Absolute Percentage Error)*.
  - Komparasi performa metode untuk rekomendasi metode terbaik.

**Acceptance Criteria**:
- `ForecastService` menghasilkan nilai peramalan yang konsisten, deterministik, dan dapat diaudit.
- Penanganan *edge cases*: data historis kosong/sedikit, nilai nol, dan proteksi *division by zero*.

---

### Phase 7 — Replenishment & Decision Support Engine
> **Tujuan**: Menyediakan kalkulasi rekomendasi pemesanan ulang (*Smart Replenishment*) berdasarkan parameter stok dinamis.

- [ ] **Safety Stock Calculation**:
  - Perhitungan stok pengaman berbasis variabilitas permintaan dan *safety factor*.
- [ ] **Reorder Point (ROP)**:
  - Formula: $\text{ROP} = (\text{Daily Forecast Demand} \times \text{Lead Time}) + \text{Safety Stock}$.
- [ ] **Days Until Stockout**:
  - Formula: $\text{Days Until Stockout} = \frac{\text{Available Stock}}{\text{Daily Forecast Demand}}$.
- [ ] **Smart Restock Recommendation**:
  - Formula kuantitas pemesanan: $\text{Target Stock} - \text{Available Stock}$.
  - Rekomendasi transparan (menampilkan breakdown kalkulasi kepada user).
  - Tidak membuat PO otomatis tanpa persetujuan manusia (*Human Approval*).
- [ ] **Klasifikasi Kesehatan & Kecepatan Stok**:
  - Status Stok: `HEALTHY`, `WARNING`, `CRITICAL`, `OUT_OF_STOCK`.
  - Klasifikasi Perputaran: `FAST_MOVING`, `NORMAL`, `SLOW_MOVING`, `DEAD_STOCK` (berdasarkan ambang batas hari tanpa transaksi).

**Acceptance Criteria**:
- Sistem menghasilkan rekomendasi pemesanan yang logis dengan rincian data parameter yang jelas.

---

### Phase 8 — Automation & Notifications
> **Tujuan**: Menjalankan kalkulasi terjadwal di latar belakang dan mengirimkan notifikasi otomatis saat stok mencapai batas kritis.

- [ ] **Background Queue Jobs**:
  - Job: `CalculateDailyForecast`, `CalculateSafetyStock`, `CalculateReorderPoint`, `CheckInventoryAlerts`.
- [ ] **Scheduler**:
  - Eksekusi harian (*daily*) untuk kalkulasi forecast, metrik stok, dan dead stock.
  - Eksekusi per jam (*hourly*) untuk pengecekan stok kritis dan pemrosesan antrean notifikasi.
- [ ] **Sistem Notifikasi Idempoten**:
  - Notifikasi saat stok $\le$ ROP, stok $\le$ Safety Stock, atau stok habis.
  - Channel: Email dan WhatsApp Webhook.
  - Tabel `notification_logs` untuk mencegah pengiriman alert berulang (*spamming*).

**Acceptance Criteria**:
- Job antrean dan scheduler berjalan lancar via CLI/worker tanpa memblokir request pengguna.
- Notifikasi terkirim tepat waktu dan tercatat di audit log.

---

### Phase 9 — Reports & Export Engine
> **Tujuan**: Menyediakan fitur rekapitulasi data dan pelaporan operasional dalam format file siap cetak dan analisis.

- [ ] **Halaman Laporan**:
  - Laporan Inventaris (stok dan valuasi per gudang).
  - Laporan Mutasi Stok (pergerakan barang masuk/keluar/transfer).
  - Laporan Stok Kritis / Low Stock.
  - Laporan Akurasi Peramalan (*Forecast Accuracy*).
  - Laporan Dead Stock & Barang Tidak Bergerak.
- [ ] **Filter & Export Data**:
  - Filter dinamis berdasarkan rentang tanggal, kategori, gudang, supplier, dan status.
  - Export ke format **Excel (.xlsx)** via Laravel Excel (Maatwebsite).
  - Export ke format **PDF (.pdf)** dengan template rapi siap cetak.

**Acceptance Criteria**:
- Laporan dapat diunduh dalam hitungan detik dengan data yang 100% konsisten terhadap database.

---

### Phase 10 — Testing, Hardening & Security
> **Tujuan**: Menjamin keandalan fungsional, keamanan data, dan performa tinggi sebelum rilis.

- [ ] **Automated Testing Suite**:
  - **Unit Tests**: Formula matematis MA, ES, Safety Stock, ROP, Days Until Stockout, Error calculation.
  - **Feature Tests**: CRUD Master Data, Stock In/Out, Transfer, Purchase, Sales, Auth & Policies.
  - **Inventory Integrity Tests**: Uji konsistensi stok sebelum dan sesudah mutasi berantai.
  - **Concurrency Tests**: Uji simulasi transaksi paralel pada stok yang sama.
- [ ] **Audit Keamanan & Otorisasi**:
  - Proteksi Mass Assignment, CSRF, validasi backend menyeluruh, pemisahan isolasi hak akses gudang.
- [ ] **Optimasi Query & Indexing**:
  - Audit query (pencegahan N+1) dan verifikasi index pada foreign keys, SKU, dan kolom tanggal.

**Acceptance Criteria**:
- Seluruh automated test suite lulus 100% (`php artisan test`).
- Integritas data tidak pernah mengalami kondisi negatif tanpa izin bisnis khusus.

---

### Phase 11 — Production Readiness & Deployment
> **Tujuan**: Menyiapkan konfigurasi deployment untuk lingkungan staging dan produksi.

- [ ] Konfigurasi environment production (`.env.production`).
- [ ] Konfigurasi Web Server (Nginx / Apache), PHP-FPM, dan MySQL.
- [ ] Setup Queue Worker supervisor & Cron task scheduler.
- [ ] Strategi backup database otomatis dan disaster recovery plan.
- [ ] Caching konfigurasi, routing, dan views (`config:cache`, `route:cache`, `view:cache`).

**Acceptance Criteria**:
- Sistem dapat dijalankan dari *clean environment* dan siap melayani pengguna akhir.
