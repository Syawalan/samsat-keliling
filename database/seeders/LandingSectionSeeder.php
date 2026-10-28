<?php

namespace Database\Seeders;

use App\Models\LandingSection;
use Illuminate\Database\Seeder;

class LandingSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'slug' => 'samsat-keliling',
                'icon' => '🚐',
                'title' => 'Samsat Keliling',
                'description' => "Pelayanan Samsat Keliling ini melibatkan mobil bus yang dilengkapi dengan peralatan dan petugas yang siap memberikan layanan terkait administrasi kesamsatan. Mobil Samsat Keliling biasanya dilengkapi komputer dan printer untuk melakukan transaksi pembayaran Pajak Kendaraan langsung di tempat.\n\nJadwal dan lokasi ditentukan oleh Petugas Samsat Keliling. Informasi jadwal dan lokasi dapat diperoleh melalui halaman ini.",
                'subheading' => 'Tujuan',
                'subheading_text' => 'Meningkatkan mutu pelayanan publik, khususnya pelayanan pembayaran Pajak Kendaraan Bermotor (PKB).',
                'items_title' => 'Manfaat Pelayanan SAMSAT Keliling',
                'items' => [
                    'Memberikan kemudahan kepada masyarakat (Wajib Pajak) dalam pengurusan pengesahan STNK setiap tahun, pembayaran Pajak Kendaraan Bermotor (PKB), dan Santunan Wajib Dana Kecelakaan Lalu Lintas (SWDKLLJ).',
                    'Mendekatkan pelayanan kepada masyarakat atau Wajib Pajak sehingga dapat mengurangi biaya dan waktu perjalanan.',
                ],
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'slug' => 'mekanisme-pendaftaran',
                'icon' => '📝',
                'title' => 'Mekanisme Pendaftaran',
                'description' => null,
                'subheading' => null,
                'subheading_text' => null,
                'items_title' => null,
                'items' => [
                    'Wajib Pajak melakukan pendaftaran.',
                    'Pendaftaran diterima oleh petugas untuk dicek persyaratannya sesuai aturan yang berlaku.',
                    'Petugas pendaftaran melakukan input data dan validasi STNK.',
                    'Setelah berkas diterima, dilakukan penetapan Pajak dan SWDKLLJ.',
                    'Petugas Samsat memeriksa kebenaran Pajak dan SWDKLLJ sesuai ketentuan yang berlaku.',
                    'Wajib Pajak dipanggil untuk melakukan pembayaran sesuai antrean yang ditampilkan pada monitor pelayanan.',
                    'Setelah pembayaran diterima, SKPD dicetak sebagai bukti bahwa Pajak telah diterima.',
                    'Apabila Wajib Pajak mengambil nomor antrean namun tidak hadir sesuai jadwal tanpa membatalkan atau mengubah jadwal, nomor antrean dinyatakan hangus. Wajib Pajak harus mendaftar dan mengambil nomor kembali untuk memperoleh jadwal layanan berikutnya.',
                ],
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'slug' => 'syarat-ketentuan',
                'icon' => '📋',
                'title' => 'Syarat dan Ketentuan',
                'description' => null,
                'subheading' => null,
                'subheading_text' => null,
                'items_title' => null,
                'items' => [
                    'Identitas atau pemilik sah kendaraan (e-KTP).',
                    'STNK asli dan bukti pelunasan PKB serta SWDKLLJ (SKPD) yang telah divalidasi pada tahun terakhir.',
                    'Identitas pada KTP dan STNK harus sama.',
                ],
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'slug' => 'pemberitahuan',
                'icon' => 'ℹ️',
                'title' => 'Pemberitahuan Penting',
                'description' => null,
                'subheading' => null,
                'subheading_text' => null,
                'items_title' => null,
                'items' => [
                    'Bagi masyarakat yang ingin melakukan pembayaran pajak kendaraan melalui layanan Samsat Keliling wajib membawa Form Pengambilan Antrean yang tersedia pada sistem.',
                ],
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($sections as $section) {
            LandingSection::query()->updateOrCreate(['slug' => $section['slug']], $section);
        }
    }
}