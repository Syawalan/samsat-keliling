<?php

namespace Database\Seeders;

use App\Models\QueueTicket;
use App\Models\ServiceSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $services = ['Pajak tahunan', 'Perpanjangan STNK lima tahunan', 'Balik nama kendaraan', 'Konsultasi lainnya'];

        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $date = today()->subDays($daysAgo);
            $isToday = $date->isToday();
            $schedule = ServiceSchedule::query()->updateOrCreate(
                ['service_date' => $date->toDateString()],
                [
                    'location' => 'Halaman Kantor Samsat Kota',
                    'starts_at' => '08:00',
                    'ends_at' => '12:00',
                    'quota' => 50,
                    'service_minutes' => 4,
                    'info_title' => 'Layanan Samsat Keliling',
                    'info_content' => 'Bawa e-KTP dan STNK asli. Kuota antrean terbatas.',
                    'is_active' => $isToday,
                ],
            );

            $numberOfTickets = $isToday ? 12 : (($daysAgo * 7) % 9) + 3;
            for ($number = 1; $number <= $numberOfTickets; $number++) {
                $status = $isToday
                    ? ($number <= 4 ? 'completed' : ($number === 5 ? 'serving' : 'waiting'))
                    : 'completed';
                $issuedAt = $date->copy()->setTime(8, 0)->addMinutes(($number - 1) * 4);

                QueueTicket::query()->firstOrCreate(
                    ['service_schedule_id' => $schedule->id, 'queue_number' => $number],
                    [
                        'tracking_token' => Str::random(48),
                        'name' => 'Warga Demo ' . str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                        'phone' => '000000' . str_pad((string) $number, 6, '0', STR_PAD_LEFT),
                        'service_type' => $services[($number - 1) % count($services)],
                        'status' => $status,
                        'issued_at' => $issuedAt,
                        'called_at' => $status === 'waiting' ? null : $issuedAt->copy()->addMinutes(3),
                        'completed_at' => $status === 'completed' ? $issuedAt->copy()->addMinutes(7) : null,
                    ],
                );
            }
        }

        for ($daysAhead = 1; $daysAhead <= 4; $daysAhead++) {
            ServiceSchedule::query()->updateOrCreate(
                ['service_date' => today()->addDays($daysAhead)->toDateString()],
                [
                    'location' => 'Halaman Kantor Samsat Kota',
                    'starts_at' => '08:00',
                    'ends_at' => '12:00',
                    'quota' => 50,
                    'service_minutes' => 4,
                    'info_title' => 'Layanan Samsat Keliling',
                    'info_content' => 'Bawa e-KTP dan STNK asli. Kuota antrean terbatas.',
                    'is_active' => true,
                ],
            );
        }
    }
}