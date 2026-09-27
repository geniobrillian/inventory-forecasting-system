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
| **Phase 3** | Inventory Core & Ledger | `COMPLETED` | Dual-layer Inventory (Stock & Ledger), Stock In/Out, Transfer, Adjustment |
| **Phase 4** | Purchasing & Sales Integration | `COMPLETED` | Purchase Orders, Receiving -> Stock In, Sales -> Stock Out |
| **Phase 5** | Stock Card & Analytics Dashboard | `COMPLETED` | Kartu Stok real-time, KPI Widgets, Interactive Charts (ApexCharts) |
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

- [x] **Skema Database Inventory**:
  - `inventory_stocks` (product_id, warehouse_id, quantity, reserved_quantity, available_quantity) -> Unique composite `(product_id, warehouse_id)`.
  - `inventory_transactions` (product_id, warehouse_id, transaction_type, quantity, stock_before, stock_after, reference_type, reference_id, notes, performed_by, transaction_date).
- [x] **Service & Action Layer (Domain Engine)**:
  - `InventoryService` dengan arsitektur transaksi atomik berbasis `DB::transaction()` dan row-locking `lockForUpdate()` untuk mencegah *race conditions* dan inkonsistensi saldo stok.
  - Method `stockIn()`, `stockOut()`, `transfer()`, `adjust()`, `getStock()`, `getTotalStock()`.
  - Custom Exception `InsufficientStockException` untuk memvalidasi defisit kuantitas sebelum pengeluaran atau transfer.
- [x] **Form Requests & Validasi Backend**:
  - `StockInRequest`: Validasi kuantitas masuk $> 0$, tipe transaksi masuk terdaftar, dan tanggal.
  - `StockOutRequest`: Validasi kuantitas keluar $> 0$, tipe transaksi keluar terdaftar, dan pencegahan saldo minus.
  - `StockTransferRequest`: Validasi pemindahan antar-gudang berbeda (`from_warehouse_id != to_warehouse_id`) dan kecukupan stok sumber.
  - `StockAdjustmentRequest`: Validasi kuantitas fisik aktual $\ge 0$, alasan penyesuaian (opname), dan kalkulasi delta mutasi otomatis.
- [x] **Controllers & AJAX Endpoint**:
  - `InventoryController`: Halaman ringkasan stok multi-gudang (`overview`), formulir dan aksi mutasi masuk (`stockInForm`/`processStockIn`), mutasi keluar (`stockOutForm`/`processStockOut`), transfer antar-gudang (`transferForm`/`processTransfer`), serta opname fisik (`adjustmentForm`/`processAdjustment`).
  - Endpoint `getStockAjax` (`GET /inventory/api/stock?product_id=X&warehouse_id=Y`) untuk pengecekan stok live real-time di UI dropdown form.
  - `StockCardController`: Kalkulasi buku besar kartu stok, perhitungan saldo awal sebelum rentang tanggal filter, dan saldo berjalan (*running cumulative balance*) baris per baris.
- [x] **UI Views & Interaktivitas**:
  - `inventory/overview.blade.php`: KPI Metrik Persediaan (Total Unit, Valuasi IDR, Low Stock, Out of Stock), filter pencarian/kategori/gudang, tabel inventori multi-gudang, dan status kesehatan buffer.
  - `inventory/stock-in.blade.php`: Formulir penerimaan barang dengan preview estimasi saldo akhir real-time via Alpine.js.
  - `inventory/stock-out.blade.php`: Formulir pengeluaran barang dengan live checker sisa stok dan proteksi tombol submit saat defisit.
  - `inventory/transfer.blade.php`: Formulir relokasi antar-gudang dengan live checker stok gudang asal dan pencegahan gudang asal-tujuan yang sama.
  - `inventory/adjustment.blade.php`: Formulir rekonsiliasi stok fisik / stock opname dengan kalkulasi delta mutasi live (+/-).
  - `inventory/stock-card.blade.php`: Tampilan buku besar kartu stok real-time, filter tanggal & gudang, ringkasan saldo awal/masuk/keluar/akhir, dan tabel rincian transaksi mutasi.
- [x] **Seeder & Automated Tests**:
  - `InventorySeeder`: Seeder riwayat mutasi stok awal, transfer antar-gudang, dan transaksi penjualan demo.
  - `InventoryServiceTest.php`: 7 test cases (stock in, stock out, insufficient stock exception, atomic transfer, transfer validation, opname increase & decrease).
  - `InventoryStockTest.php`: 8 test cases (auth protection, overview display, stock in form & submit, stock out form & submit, deficit rejection, transfer antar gudang, stock adjustment, AJAX stock lookup).
  - `StockCardTest.php`: 2 test cases (empty state & running balance calculation with historic pre-filter balance).

**Acceptance Criteria**:
- Nilai stok pada `inventory_stocks` selalu identik dengan kalkulasi kumulatif `inventory_transactions`.
- Tidak ada mutasi stok yang terjadi tanpa menghasilkan record ledger transaksi.
- Seluruh 65 feature & unit automated tests berstatus `100% PASS`.

---

### Phase 4 — Purchasing & Sales Integration
> **Tujuan**: Menghubungkan proses bisnis pengadaan (*Purchasing*) dan penjualan (*Sales*) langsung ke mutasi stok inventaris dengan konsistensi dua lapis (*Current Stock* & *Immutable Ledger*).

- [x] **Purchasing & Procurement Module**:
  - Skema database `purchases` & `purchase_items` (nomor PO unik berurutan `PO-YYYYMM-XXXX`, supplier_id, warehouse_id, purchase_date, expected_date, subtotal, discount, tax, shipping_cost, total, notes, status `[DRAFT, ORDERED, PARTIALLY_RECEIVED, RECEIVED, CANCELLED]`).
  - Accessor model & status helpers: `isDraft()`, `canReceive()`, `canCancel()`, `canEdit()`, `receiving_progress_percent`.
  - Service Layer `PurchaseService`:
    - `generatePurchaseNumber()`: Pembuatan kode faktur pengadaan otomatis berurutan.
    - `createPurchase()` & `updatePurchase()`: Validasi item pesanan, penghitungan subtotal, diskon, pajak, dan ongkos kirim.
    - `orderPurchase()`: Transisi status dari `DRAFT` menjadi `ORDERED`.
    - `receiveItems()`: Pemrosesan penerimaan fisik barang (GRN), pencatatan kuantitas diterima per item, pembaruan status PO secara cerdas (`PARTIALLY_RECEIVED` atau `RECEIVED`), dan pemicu atomik penambahan stok gudang (`InventoryService::stockIn` dengan `transaction_type = PURCHASE`).
    - `cancelPurchase()`: Pembatalan pesanan jika belum ada fisik barang yang diterima.
  - Form Requests: `PurchaseRequest` dan `ReceivePurchaseRequest` dengan validasi ketat dan alias field dinamis.
  - UI Purchasing:
    - `purchasing/index.blade.php`: Ringkasan PO, filter supplier/gudang/status/tanggal, search bar, status badges, progress bar penerimaan, dan pagination.
    - `purchasing/create.blade.php` & `purchasing/edit.blade.php`: Kalkulator pesanan dinamis Alpine.js dengan input diskon, pajak, subtotal baris otomatis, dan validasi item sebelum submit.
    - `purchasing/show.blade.php`: Detail PO, kartu progres penerimaan, rincian barang dan biaya finansial, aksi ajukan pesan, terima barang, batalkan PO, dan print-ready faktur.
    - `purchasing/receive.blade.php`: Form penerimaan fisik barang (GRN) dengan input sisa kuantitas belum diterima dan validasi batas maksimal per item.

- [x] **Sales & Commercial Orders Module**:
  - Skema database `sales` & `sale_items` (nomor faktur unik berurutan `INV-YYYYMM-XXXX`, warehouse_id, customer_name, customer_phone, sale_date, status `[DRAFT, COMPLETED, CANCELLED]`, payment_method `[CASH, TRANSFER, QRIS, DEBT, OTHER]`, payment_status `[PAID, PARTIAL, UNPAID]`, subtotal, discount, tax, shipping_cost, paid_amount, change_amount, total, notes).
  - Accessor model & status helpers: `isDraft()`, `isCompleted()`, `isCancelled()`, `canComplete()`, `canCancel()`.
  - Service Layer `SalesService`:
    - `generateInvoiceNumber()`: Pembuatan kode faktur penjualan otomatis berurutan.
    - `createSale()`: Pembuatan faktur penjualan. Jika status `COMPLETED`, sistem langsung memotong stok gudang secara atomik (`InventoryService::stockOut` dengan `transaction_type = SALE`). Jika stok tidak mencukupi, dilempar `InsufficientStockException`.
    - `updateSale()`: Pembaruan draf penjualan dengan kalkulasi ulang nominal dan sinkronisasi item.
    - `completeSale()`: Penyelesaian draf penjualan dan pemotongan stok gudang secara atomik.
    - `cancelSale()`: Pembatalan penjualan. Jika sebelumnya berstatus `COMPLETED`, stok barang dikembalikan otomatis ke gudang via transaksi ledger bertipe `RETURN_IN`.
  - Form Request: `SaleRequest` dengan validasi kuantitas item, harga jual, dan opsi pembayaran.
  - UI Sales:
    - `sales/index.blade.php`: Ringkasan transaksi penjualan, filter gudang/status/tanggal/search pelanggan & faktur, status pembayaran badges, dan pagination.
    - `sales/create.blade.php` & `sales/edit.blade.php`: POS / Kasir Penjualan interaktif Alpine.js dengan cek sisa stok gudang secara live, kalkulasi subtotal, diskon %, pajak %, ongkir, uang dibayar, dan nominal kembalian real-time.
    - `sales/show.blade.php`: Tampilan faktur penjualan lengkap, status pembayaran, kasir/petugas, rincian produk, cetak faktur, serta aksi selesaikan draf atau batalkan penjualan (dengan pengembalian stok).

- [x] **Seeder & Automated Feature Test Suite**:
  - `PurchasingSalesSeeder`: Seeder realistis untuk skenario PO diterima lengkap (`RECEIVED`), PO diterima sebagian (`PARTIALLY_RECEIVED`), draf PO, penjualan transfer lunas (`COMPLETED`), penjualan tunai retail (`COMPLETED`), dan draf piutang (`DRAFT`).
  - `PurchaseTest.php` (4 test cases): Create draft PO without inflating stock, transition to ORDERED, partial and full receiving triggering atomic stock in and ledger recording, cancel PO.
  - `SaleTest.php` (5 test cases): View index, create draft sale without stock deduction, create completed sale with atomic stock deduction and ledger recording, deficit rejection (`InsufficientStockException`), cancel completed sale restoring stock via `RETURN_IN`.
  - `PurchasingSalesIntegrationTest.php` (1 comprehensive lifecycle test case): Pengujian end-to-end terintegrasi pengadaan PO -> penerimaan barang -> stok naik -> penjualan draf -> penjualan selesai -> stok turun -> pembatalan penjualan -> stok kembali -> verifikasi 4 record transaksi ledger kartu stok.

**Acceptance Criteria**:
- Invarian Pengadaan Terpenuhi: Pembuatan PO (DRAFT/ORDERED) tidak pernah menambah saldo stok fisik sebelum proses penerimaan barang fisik (`receiveItems`).
- Invarian Penjualan Terpenuhi: Penjualan berstatus `COMPLETED` selalu memotong saldo stok fisik dan mencatat record transaksi `SALE`. Jika dibatalkan, stok pulih melalui record `RETURN_IN`.
- Seluruh automated test suite (75 tests, 261 assertions) berstatus `100% PASS`.
- Fresh migration dan seeding (`php artisan migrate:fresh --seed`) berjalan tanpa kendala.

---

### Phase 5 — Stock Card & Analytics Dashboard
> **Tujuan**: Menyediakan antarmuka analitik dan kartu stok yang interaktif untuk memonitor kesehatan inventaris bisnis secara komprehensif.

- [x] **Kartu Stok Interaktif (Stock Card Ledger)**:
  - Filter interaktif berbasis produk, gudang, rentang tanggal (dari s/d), dan tipe transaksi (`PURCHASE`, `SALE`, `TRANSFER_IN`, `TRANSFER_OUT`, `ADJUSTMENT_IN`, `ADJUSTMENT_OUT`, `RETURN_IN`, `RETURN_OUT`, `INITIAL`).
  - Komputasi Saldo Awal (*Initial Balance*) secara matematis sebelum rentang tanggal filter yang dipilih.
  - Kalkulasi Saldo Berjalan (*Running Cumulative Balance*) baris per baris secara kronologis.
  - Kartu ringkasan metrik: Saldo Awal, Total Masuk (+), Total Keluar (-), dan Saldo Akhir.
  - Fitur cetak ramah printer (*Print View*) dengan layout kartu stok resmi.
- [x] **Dashboard KPI Widgets & Business Intelligence Engine**:
  - `AnalyticsService` untuk agregasi data performa tinggi tanpa N+1 queries.
  - **4 Metrik Utama**:
    - Total SKU Produk Aktif.
    - Total Kuantitas Fisik Stok (Semua Gudang).
    - Total Valuasi Persediaan (IDR) = $\sum(\text{stock.quantity} \times \text{product.purchase\_price})$.
    - Penjualan 30 Hari Terakhir (Revenue) & Estimasi HPP / COGS 30 Hari.
    - Rasio *Inventory Turnover* Terdisi Tahunan (*Annualized Turnover Ratio*).
  - **Kesehatan Stok (Stock Health Breakdown)**:
    - *Healthy Stock* ($> \text{minimum\_stock}$).
    - *Low Stock* ($\le \text{minimum\_stock}$).
    - *Critical Stock* ($\le 40\%$ dari minimum stock).
    - *Out of Stock* ($= 0$).
  - **Analisis Perputaran & Kecepatan Barang (Velocity Metrics)**:
    - *Fast-Moving Products*: Produk dengan penjualan $\ge 20$ unit dalam 30 hari terakhir.
    - *Slow-Moving Products*: Produk dengan penjualan rendah.
    - *Dead Stock*: Barang tidak terjual / tidak bergerak selama $> 90$ hari lengkap dengan durasi hari tidak aktif.
  - **Top Selling Products**: 5 produk paling laris dalam 30 hari berdasarkan volume penjualan dan kontribusi omzet.
- [x] **Visualisasi Interaktif (ApexCharts)**:
  - **Demand Trend Area Chart**: Grafik area tren permintaan terjual harian (14 hari terakhir) dengan tooltip interaktif dan smooth spline curve.
  - **Stock Flow Comparison Bar Chart**: Grafik komparasi barang masuk (*Stock In*) vs barang keluar (*Stock Out*) selama 6 bulan terakhir.
  - **Warehouse Distribution Donut Chart**: Grafik proporsi volume unit dan nilai rupiah stok antar-gudang.
- [x] **Live Ledger Stream (Recent Activity Feed)**:
  - Widget umpan langsung 8 transaksi mutasi stok terakhir dengan badge status, operator, kuantitas, dan saldo sebelum-sesudah.
- [x] **Automated Feature Test Suite**:
  - `AnalyticsDashboardTest.php` (6 test cases):
    - Render halaman analitik dashboard dengan otentikasi.
    - Validasi kalkulasi KPI, total stok, dan valuasi rupiah persediaan.
    - Validasi klasifikasi kesehatan stok (Healthy, Low, Critical, Out of Stock).
    - Validasi deteksi barang Fast-Moving dan Dead Stock.
    - Validasi struktur datasets chart tren permintaan dan pergerakan stok.
    - Validasi filter kartu stok per tipe transaksi.
  - `StockCardTest.php` (2 test cases):
    - Render kartu stok dengan keadaan awal (empty state).
    - Perhitungan saldo awal historis dan saldo berjalan kronologis.

**Acceptance Criteria**:
- Seluruh metrik KPI dashboard teragregasi secara akurat tanpa inkonsistensi data.
- Grafik ApexCharts termuat responsif dan menyajikan visualisasi data dinamis.
- Seluruh test suite (81 tests, 282 assertions) berstatus `100% PASS`.
- Fresh migration dan seeding (`php artisan migrate:fresh --seed`) berjalan tanpa kendala.

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
