<?php

/**
 * Katalog endpoint untuk halaman API Tester (panel admin, Super Admin).
 * Ditulis manual: tambahkan entri di sini bila ada rute baru di routes/api.php.
 *
 * Field: method, path (relatif terhadap /api; {id} = parameter path),
 *        desc, auth (butuh Bearer token), body (contoh JSON, opsional),
 *        query (contoh query string, opsional).
 */
return [
    'Katalog (publik)' => [
        ['method' => 'GET', 'path' => '/layanan',      'desc' => 'Daftar layanan', 'auth' => false],
        ['method' => 'GET', 'path' => '/layanan/{id}', 'desc' => 'Detail satu layanan', 'auth' => false],
        ['method' => 'GET', 'path' => '/produk',       'desc' => 'Daftar produk', 'auth' => false],
        ['method' => 'GET', 'path' => '/produk/{id}',  'desc' => 'Detail satu produk', 'auth' => false],
        ['method' => 'GET', 'path' => '/barber',       'desc' => 'Daftar barber', 'auth' => false],
        ['method' => 'GET', 'path' => '/galeri',       'desc' => 'Galeri foto', 'auth' => false],
    ],
    'Autentikasi' => [
        ['method' => 'POST', 'path' => '/register', 'desc' => 'Daftar customer baru', 'auth' => false,
            'body' => ['nama' => 'Customer Uji', 'email' => 'uji@example.com', 'password' => 'rahasia123']],
        ['method' => 'POST', 'path' => '/login', 'desc' => 'Login; token otomatis terisi di kolom Bearer', 'auth' => false,
            'body' => ['email' => 'uji@example.com', 'password' => 'rahasia123']],
        ['method' => 'POST', 'path' => '/login/google', 'desc' => 'Login Google (butuh ID token asli dari app)', 'auth' => false,
            'body' => ['id_token' => '<id token Google>']],
        ['method' => 'POST', 'path' => '/password/forgot', 'desc' => 'Lupa password: ajukan permintaan ke admin (berisi request_token; 422 bila email tidak terdaftar)', 'auth' => false,
            'body' => ['email' => 'uji@example.com']],
        ['method' => 'POST', 'path' => '/password/status', 'desc' => 'Cek status permintaan; setelah disetujui admin, password sementara diserahkan SEKALI', 'auth' => false,
            'body' => ['request_token' => '<dari /password/forgot>']],
        ['method' => 'POST', 'path' => '/logout', 'desc' => 'Keluar; mencabut token', 'auth' => true],
    ],
    'Profil' => [
        ['method' => 'GET', 'path' => '/me', 'desc' => 'Data customer yang login', 'auth' => true],
        ['method' => 'POST', 'path' => '/me', 'desc' => 'Ubah profil', 'auth' => true,
            'body' => ['nama' => 'Customer Uji', 'no_hp' => '081234567890', 'alamat' => 'Jl. Contoh No. 1']],
    ],
    'Ganti Password' => [
        ['method' => 'POST', 'path' => '/me/password', 'desc' => 'Ganti password (juga untuk mengganti password sementara)', 'auth' => true,
            'body' => ['current_password' => 'rahasia123', 'password' => 'passwordBaru123']],
    ],
    'Hapus Akun' => [
        ['method' => 'DELETE', 'path' => '/me', 'desc' => 'Hapus akun (anonimisasi; akun berpassword wajib kirim password)', 'auth' => true,
            'body' => ['password' => 'rahasia123']],
    ],
    'Booking' => [
        ['method' => 'GET', 'path' => '/booking', 'desc' => 'Riwayat booking customer', 'auth' => true],
        ['method' => 'GET', 'path' => '/booking/{id}', 'desc' => 'Detail satu booking', 'auth' => true],
        ['method' => 'POST', 'path' => '/booking', 'desc' => 'Buat booking layanan', 'auth' => true,
            'body' => ['layanan_ids' => [1], 'barber_id' => 1, 'tanggal_booking' => '2026-12-01', 'jam_booking' => '10:00', 'catatan' => 'Dibuat lewat API Tester']],
        ['method' => 'POST', 'path' => '/booking/produk', 'desc' => 'Pesan produk (dengan ongkir)', 'auth' => true,
            'body' => ['items' => [['produk_id' => 1, 'qty' => 1]], 'alamat_kirim' => 'Jl. Contoh No. 1', 'kota_tujuan_id' => '1', 'kota_tujuan_label' => 'Contoh Kota', 'kurir' => 'jne', 'layanan_ongkir' => 'REG', 'biaya_ongkir' => 10000, 'estimasi_ongkir' => '2-3 hari', 'total_berat' => 500, 'catatan' => '']],
        ['method' => 'PUT', 'path' => '/booking/{id}', 'desc' => 'Ubah booking (hanya confirmed & belum lunas)', 'auth' => true,
            'body' => ['layanan_ids' => [1], 'barber_id' => 1, 'tanggal_booking' => '2026-12-02', 'jam_booking' => '11:00', 'catatan' => 'Diubah lewat API Tester']],
        ['method' => 'DELETE', 'path' => '/booking/{id}', 'desc' => 'Batalkan booking (status menjadi batal)', 'auth' => true],
        ['method' => 'POST', 'path' => '/booking/{id}/pay', 'desc' => 'Bayar booking', 'auth' => true,
            'body' => ['metode_bayar' => 'transfer', 'kanal_bayar' => 'BCA']],
    ],
    'Ulasan' => [
        ['method' => 'GET', 'path' => '/ulasan', 'desc' => 'Daftar ulasan milik customer', 'auth' => true],
        ['method' => 'GET', 'path' => '/ulasan/{id}', 'desc' => 'Detail satu ulasan', 'auth' => true],
        ['method' => 'POST', 'path' => '/ulasan', 'desc' => 'Tulis ulasan (booking harus berstatus selesai)', 'auth' => true,
            'body' => ['order_id' => 1, 'rating' => 5, 'komentar' => 'Potongan rapi, pelayanan ramah.']],
        ['method' => 'PUT', 'path' => '/ulasan/{id}', 'desc' => 'Ubah rating/komentar', 'auth' => true,
            'body' => ['rating' => 4, 'komentar' => 'Diperbarui lewat API Tester']],
        ['method' => 'DELETE', 'path' => '/ulasan/{id}', 'desc' => 'Hapus ulasan', 'auth' => true],
    ],
    'Pengiriman' => [
        ['method' => 'GET', 'path' => '/shipping/search', 'desc' => 'Cari tujuan pengiriman (min. 3 huruf)', 'auth' => true,
            'query' => 'search=bandung'],
        ['method' => 'POST', 'path' => '/shipping/cost', 'desc' => 'Hitung ongkos kirim', 'auth' => true,
            'body' => ['destination' => '1', 'weight' => 500, 'courier' => 'jne']],
    ],
];
