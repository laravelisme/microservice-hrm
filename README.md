# HRM Dev App (Monorepo)

Ringkasan singkat

Proyek ini adalah monorepo berisi beberapa layanan microservice (PHP/Laravel dan Go) yang diatur via Docker Compose dan Traefik sebagai reverse-proxy.

Yang penting di repo ini:
- services/transaction-service — layanan Go (Fiber + GORM) untuk transaksi
- services/user-service, services/master-service — layanan PHP/Laravel
- docker-compose.yml — orkestrasi layanan dan Traefik
- .gitignore — aturan file yang diabaikan oleh git

Tujuan README
- Mempermudah developer baru menjalankan environment lokal.
- Menjelaskan cara development (hot-reload), debugging (Traefik 502), dan optimasi CPU untuk layanan Go.

-----
Persyaratan

- Docker Engine (termasuk docker compose v2)
- WSL2 (direkomendasikan untuk pengembangan di Windows)
- Editor: VSCode (Remote - WSL direkomendasikan)

-----
Quick start (semua service)

1. Buka terminal di folder project root:

```bash
cd /var/www/hrm-dev-app
```

2. Jalankan seluruh stack (traefik, postgres, rabbitmq, layanan):

```bash
docker compose up -d
```

3. Cek service yang berjalan:

```bash
docker compose ps
```

4. Traefik dashboard tersedia di http://localhost:8080

-----
Transaction service (Go) — development (hot-reload)

File utama: `services/transaction-service`

Pada konfigurasi dev kita pakai sebuah dev image (`Dockerfile.dev`) yang sudah menginstall watcher (CompileDaemon). Compose dev service mem-mount source sehingga Anda bisa mengedit file di host dan perubahan akan terdeteksi otomatis.

Start hanya dev service (untuk development cepat):

```bash
cd /var/www/hrm-dev-app
# Jika Anda ingin hanya menjalankan transaction service + dependensi
docker compose up -d postgres-transaction transaction-service-dev
```

Log dan hot-reload:

```bash
# Tampilkan logs realtime untuk melihat CompileDaemon / rebuild
docker compose logs -f --tail 200 transaction-service-dev
```

Trigger manual (jika watcher tidak mendeteksi karena editor/OS):

```bash
# memaksa update timestamp
touch services/transaction-service/internal/router/v1.go
```

Catatan penting tentang hot-reload & mount
- Edit file sebaiknya dilakukan melalui WSL (mis. VSCode Remote - WSL) agar perubahan file dipantau melalui inotify; jika Anda mengedit lewat Windows native editor dan mount tidak menyinkronkan mtime dengan benar, watcher bisa gagal mendeteksi perubahan.
- Jika watcher tak kunjung mendeteksi, coba `touch` file seperti di atas atau gunakan editor yang berjalan di WSL.

-----
Endpoint uji cepat

- Direct (bypass Traefik):

```bash
curl -i http://127.0.0.1:3001/health
```

- Melalui Traefik (pakai Host header karena router rule di compose menggunakan Host(`localhost`)):

```bash
curl -i -H 'Host: localhost' http://127.0.0.1/api/v2/transaction-data/health
```

-----
Mengatasi Bad Gateway (502) pada Traefik

Penyebab umum:
- Backend sedang restart (hot-reload) sehingga saat Traefik meneruskan request, belum ada listener — menyebabkan 502.
- Traefik belum memuat ulang provider/labels sehingga menunjuk ke IP/port yang salah.
- Backend crash saat runtime.

Langkah diagnosa cepat:

```bash
# 1. Lihat logs backend
docker compose logs -f transaction-service-dev

# 2. Lihat logs Traefik
docker compose logs -f traefik

# 3. Periksa routers/services Traefik
curl http://127.0.0.1:8080/api/http/routers | jq .
curl http://127.0.0.1:8080/api/http/services | jq .

# 4. Test koneksi dari dalam network gateway (simulasi Traefik -> container)
docker run --rm --network gateway curlimages/curl:8.2.1 curl -i http://transaction-service-dev:3000/health
```

Perbaikan cepat:
- Restart Traefik agar reload provider: `docker compose restart traefik`.
- Pastikan label service di `docker-compose.yml` menunjuk ke internal port container (3000) — file ini sudah dikonfigurasi.
- Tambahkan healthcheck dan retry middleware (sudah ditambahkan) agar Traefik menunggu backend sehat sebelum men-forward request.

-----
Mengurangi CPU usage saat develop (Go)

Sudah diterapkan di dev image / compose:
- Dev image (`Dockerfile.dev`) sudah menginstall CompileDaemon di build-time sehingga runtime tidak perlu meng-install package setiap start.
- Build step di dev image membatasi parallellism: `go build -p 1` dan `GOMAXPROCS=1` sehingga Go tidak memakai semua core.
- CompileDaemon build command dijalankan dengan `nice -n 10` untuk menurunkan prioritas CPU saat compile.

Jika Anda ingin cap CPU container lebih ketat, tambahkan (opsional) pada `docker-compose.yml`:

```yaml
transaction-service-dev:
  cpus: 0.5
```

atau jalankan container dev dengan opsi `--cpus` jika menggunakan `docker run`.

-----
Pengaturan keamanan / rahasia

- Jangan commit file `.env` berisi credential — `.gitignore` sudah menambahkan pola untuk `.env` dan file lokal lain.
- Untuk mengelola secret di Compose, gunakan file `.env` lokal yang tidak di-commit, atau gunakan secret manager jika di-deploy.

-----
Tips editor / workflow

- Gunakan VSCode Remote - WSL (jika di Windows) untuk mengedit file di direktori WSL sehingga inotify berfungsi.
- Setelah edit: cek logs `docker compose logs -f transaction-service-dev` untuk melihat apakah watcher memicu rebuild.

-----
Contributing

- Ikuti standar code style di repo.
- Jangan commit file sensitif (periksa `.gitignore`).

-----
Jika butuh bantuan lanjutan

- Mau saya:
  - commit dan push `.gitignore` dan `README.md` ke branch Anda? (butuh akses repo remote)
  - tambah contoh `Makefile` atau skrip helper di root untuk common tasks (start/stop/logs)?
  - ubah `docker-compose.yml` agar dev service punya limit CPU (cpus: 0.5)?

Kalau mau saya jalankan build/test live di environment ini dan laporkan output (logs / docker stats), jawab saja: jalankan test sekarang.

---
README dibuat otomatis oleh asisten; jika ada bagian yang ingin Anda perpendek, dipertegas, atau ditambahkan (contoh env vars, contoh response API), beri tahu saya dan saya perbaiki.
