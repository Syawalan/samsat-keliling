<?php

namespace App\Http\Controllers;

use App\Models\LandingSection;
use App\Models\QueueTicket;
use App\Models\ServiceSchedule;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function home(): View
    {
        $schedule = ServiceSchedule::query()->where('is_active', true)->whereDate('service_date', '>=', today())->orderBy('service_date')->first();
        $sections = LandingSection::query()->where('is_active', true)->orderBy('sort_order')->get();

        return view('home', compact('schedule', 'sections'));
    }

    public function schedule(): View
    {
        $schedules = ServiceSchedule::query()->where('is_active', true)->whereDate('service_date', '>=', today())->orderBy('service_date')->get();

        return view('schedule', compact('schedules'));
    }

    public function create(): View
    {
        $schedule = ServiceSchedule::query()->whereDate('service_date', today())->where('is_active', true)->first();

        return view('queue.create', compact('schedule'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'service_type' => ['required', 'in:Pajak tahunan,Perpanjangan STNK lima tahunan,Balik nama kendaraan,Konsultasi lainnya'],
        ]);

        try {
            $ticket = DB::transaction(function () use ($data): QueueTicket {
                $schedule = ServiceSchedule::query()->whereDate('service_date', today())->where('is_active', true)->lockForUpdate()->first();
                if (! $schedule) { throw ValidationException::withMessages(['queue' => 'Jadwal layanan hari ini belum tersedia.']); }
                if (now()->format('H:i') < substr((string) $schedule->starts_at, 0, 5)) { throw ValidationException::withMessages(['queue' => 'Pendaftaran antrean belum dibuka.']); }
                if (now()->format('H:i') >= substr((string) $schedule->ends_at, 0, 5)) { throw ValidationException::withMessages(['queue' => 'Jam layanan hari ini sudah selesai.']); }

                $count = $schedule->tickets()->count();
                if ($count >= $schedule->quota) { throw ValidationException::withMessages(['queue' => 'Kuota antrean hari ini sudah penuh.']); }

                $ahead = $schedule->tickets()->whereIn('status', ['waiting', 'called', 'serving'])->count();
                if ($ahead * $schedule->service_minutes > 35) { throw ValidationException::withMessages(['queue' => 'Estimasi tunggu sudah melebihi 35 menit. Silakan datang pada jadwal berikutnya.']); }

                return $schedule->tickets()->create([
                    'queue_number' => $count + 1,
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'service_type' => $data['service_type'],
                    'status' => 'waiting',
                    'issued_at' => now(),
                ]);
            }, 3);
        } catch (QueryException) {
            return back()->withInput()->withErrors(['queue' => 'Antrean sedang diperbarui. Silakan coba kembali.']);
        }

        return redirect()->route('queue.show', $ticket->tracking_token);
    }

    public function show(QueueTicket $ticket): View
    {
        $ticket->load('schedule');
        $remaining = $ticket->schedule->tickets()->where('queue_number', '<', $ticket->queue_number)->whereIn('status', ['waiting', 'called', 'serving'])->count();
        $waiting = $ticket->schedule->tickets()->where('status', 'waiting')->count();

        return view('queue.show', [
            'ticket' => $ticket,
            'position' => $ticket->status === 'waiting' ? $remaining + 1 : 0,
            'remaining' => $remaining,
            'estimate' => $ticket->status === 'waiting' ? now()->addMinutes($remaining * $ticket->schedule->service_minutes) : null,
            'waiting' => $waiting,
        ]);
    }

    public function downloadReport(Request $request, QueueTicket $ticket): Response
    {
        abort_unless($ticket->status === 'completed', 404);
        $ticket->loadMissing('schedule');

        $drawText = static function (string $text, int $x, int $y, int $size, string $font = 'F1', string $color = '0.15 0.23 0.34'): string {
            $encodedText = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
            $escapedText = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encodedText === false ? '' : $encodedText);

            return "BT /{$font} {$size} Tf {$color} rg {$x} {$y} Td ({$escapedText}) Tj ET\n";
        };

        $commands = "q\n0.96 0.98 1 rg 0 0 612 792 re f\n0.10 0.39 0.84 rg 0 660 612 132 re f\nQ\n";
        $commands .= $drawText('SAMSAT KELILING', 48, 746, 13, 'F2', '1 1 1');
        $commands .= $drawText('LAPORAN PENYELESAIAN LAYANAN', 48, 710, 20, 'F2', '1 1 1');
        $commands .= $drawText('Nomor antrean: ' . $ticket->display_number, 48, 680, 12, 'F1', '0.88 0.94 1');
        $commands .= "0.84 0.89 0.95 RG 48 632 m 564 632 l S\n";

        $details = [
            ['Nama', $ticket->name],
            ['Jenis layanan', $ticket->service_type],
            ['Waktu datang (nomor antrean diambil)', ($ticket->issued_at?->translatedFormat('d F Y, H:i') ?? '—') . ' WIB'],
            ['Waktu selesai dilayani', ($ticket->completed_at?->translatedFormat('d F Y, H:i') ?? '—') . ' WIB'],
            ['Lokasi layanan', $ticket->schedule->location],
        ];

        $y = 590;
        foreach ($details as [$label, $value]) {
            $valueLines = explode("\n", wordwrap($value, 62, "\n", true));
            $commands .= $drawText($label, 48, $y, 10, 'F1', '0.38 0.45 0.54');
            foreach ($valueLines as $lineIndex => $valueLine) {
                $commands .= $drawText($valueLine, 48, $y - 23 - ($lineIndex * 16), 13, 'F2', '0.12 0.22 0.34');
            }
            $rowSpacing = max(78, 55 + (count($valueLines) * 16));
            $commands .= "0.90 0.93 0.96 RG 48 " . ($y - $rowSpacing + 15) . " m 564 " . ($y - $rowSpacing + 15) . " l S\n";
            $y -= $rowSpacing;
        }

        $commands .= $drawText('Dokumen ini merupakan bukti bahwa layanan telah selesai diberikan.', 48, 155, 10, 'F1', '0.38 0.45 0.54');
        $commands .= $drawText('Dicetak pada ' . now()->translatedFormat('d F Y, H:i') . ' WIB', 48, 62, 9, 'F1', '0.50 0.57 0.65');

        $pdf = $this->makePdfDocument($commands);
        $filename = 'laporan-' . strtolower($ticket->display_number) . '-' . $ticket->schedule->service_date->format('Y-m-d') . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($request->boolean('download') ? 'attachment' : 'inline') . '; filename="' . $filename . '"',
        ]);
    }

    public function status(QueueTicket $ticket): JsonResponse
    {
        $ticket->load('schedule');
        $remaining = $ticket->schedule->tickets()->where('queue_number', '<', $ticket->queue_number)->whereIn('status', ['waiting', 'called', 'serving'])->count();

        return response()->json([
            'status' => $ticket->status,
            'position' => $ticket->status === 'waiting' ? $remaining + 1 : 0,
            'remaining' => $ticket->status === 'waiting' ? $remaining : 0,
            'estimate' => $ticket->status === 'waiting' ? now()->addMinutes($remaining * $ticket->schedule->service_minutes)->format('H:i') : null,
        ]);
    }

    private function makePdfDocument(string $content): string
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            5 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
            6 => '<< /Length ' . strlen($content) . ">>\nstream\n" . $content . "endstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= "{$number} 0 obj\n{$object}\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 7\n0000000000 65535 f \n";
        for ($number = 1; $number <= 6; $number++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$number]);
        }

        return $pdf . "trailer\n<< /Size 7 /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF\n";
    }
}
