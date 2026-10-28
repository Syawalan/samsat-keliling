<?php

namespace App\Http\Controllers;

use App\Models\QueueTicket;
use App\Models\ServiceSchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function dashboard(Request $request): View
    {
        $validated = $request->validate(['period' => ['nullable', 'in:today,this_week,this_month,this_year,last_year']]);
        $selectedPeriod = $validated['period'] ?? 'today';
        $today = today();
        $periods = [
            'today' => 'Hari ini',
            'this_week' => 'Minggu ini',
            'this_month' => 'Bulan ini',
            'this_year' => 'Tahun ini',
            'last_year' => 'Tahun lalu',
        ];
        $granularity = 'day';
        $from = $today->copy();
        $to = $today->copy();

        switch ($selectedPeriod) {
            case 'today':
                $granularity = 'hour';
                break;
            case 'this_week':
                $from = $today->copy()->startOfWeek();
                break;
            case 'this_month':
                $from = $today->copy()->startOfMonth();
                break;
            case 'this_year':
                $granularity = 'month';
                $from = $today->copy()->startOfYear();
                break;
            case 'last_year':
                $granularity = 'month';
                $from = $today->copy()->subYear()->startOfYear();
                $to = $today->copy()->subYear()->endOfYear();
                break;
        }

        $schedule = ServiceSchedule::query()->whereDate('service_date', today())->first();
        $baseTickets = QueueTicket::query()->where('service_schedule_id', $schedule?->id ?? 0);
        $stats = [
            'total' => (clone $baseTickets)->count(),
            'waiting' => (clone $baseTickets)->where('status', 'waiting')->count(),
            'served' => (clone $baseTickets)->where('status', 'completed')->count(),
            'skipped' => (clone $baseTickets)->where('status', 'skipped')->count(),
        ];
        $current = $schedule?->tickets()->whereIn('status', ['called', 'serving'])->orderByDesc('queue_number')->first();
        $issuedTickets = QueueTicket::query()->whereBetween('issued_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        if ($granularity === 'hour') {
            $counts = (clone $issuedTickets)->selectRaw('hour(issued_at) as bucket, count(*) as total')->groupBy('bucket')->get()->keyBy('bucket');
            $startHour = $schedule ? (int) substr((string) $schedule->starts_at, 0, 2) : 8;
            $endHour = $schedule ? (int) substr((string) $schedule->ends_at, 0, 2) : 17;
            if ($schedule && substr((string) $schedule->ends_at, 3, 2) === '00') {
                $endHour--;
            }
            $chartPoints = collect(range($startHour, max($startHour, $endHour)))
                ->map(fn (int $hour): array => ['label' => sprintf('%02d', $hour), 'count' => (int) ($counts->get($hour)->total ?? 0)])
                ->all();
        } elseif ($granularity === 'month') {
            $counts = (clone $issuedTickets)->selectRaw('month(issued_at) as bucket, count(*) as total')->groupBy('bucket')->get()->keyBy('bucket');
            $chartPoints = collect(range(1, 12))
                ->map(fn (int $month): array => ['label' => $from->copy()->month($month)->translatedFormat('M'), 'count' => (int) ($counts->get($month)->total ?? 0)])
                ->all();
        } else {
            $counts = (clone $issuedTickets)->selectRaw('date(issued_at) as bucket, count(*) as total')->groupBy('bucket')->get()->keyBy('bucket');
            $chartPoints = [];
            for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                $key = $date->toDateString();
                $chartPoints[] = [
                    'label' => $selectedPeriod === 'this_week' ? $date->translatedFormat('D') : $date->format('d'),
                    'count' => (int) ($counts->get($key)->total ?? 0),
                ];
            }
        }

        $chartTotal = collect($chartPoints)->sum('count');

        return view('admin.dashboard', compact('schedule', 'stats', 'current', 'periods', 'selectedPeriod', 'from', 'to', 'chartPoints', 'chartTotal'));
    }

    public function queues(): View
    {
        $schedule = ServiceSchedule::query()->whereDate('service_date', today())->first();
        $tickets = $schedule?->tickets()->orderBy('queue_number')->get() ?? collect();
        $current = $tickets->first(fn (QueueTicket $ticket): bool => in_array($ticket->status, ['called', 'serving'], true));

        return view('admin.queues', compact('schedule', 'tickets', 'current'));
    }

    public function callNext(): RedirectResponse
    {
        $schedule = ServiceSchedule::query()->whereDate('service_date', today())->first();
        if (! $schedule) {
            return back()->with('error', 'Jadwal hari ini belum dibuat.');
        }
        if ($schedule->tickets()->whereIn('status', ['called', 'serving'])->exists()) {
            return back()->with('error', 'Selesaikan antrean yang sedang dipanggil sebelum memanggil nomor berikutnya.');
        }
        $called = DB::transaction(function () use ($schedule): ?QueueTicket {
            $ticket = $schedule->tickets()->where('status', 'waiting')->orderBy('queue_number')->lockForUpdate()->first();
            if ($ticket) {
                $ticket->update(['status' => 'called', 'called_at' => now()]);
            }

            return $ticket;
        });

        return back()->with($called ? 'success' : 'error', $called ? 'Nomor ' . $called->display_number . ' dipanggil.' : 'Tidak ada antrean yang menunggu.');
    }

    public function updateStatus(Request $request, QueueTicket $ticket): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:serving,completed,skipped']]);
        abort_unless($ticket->service_schedule_id === ServiceSchedule::query()->whereDate('service_date', today())->value('id'), 404);
        abort_unless($ticket->status === 'called' || ($ticket->status === 'serving' && in_array($data['status'], ['completed', 'skipped'], true)) || ($ticket->status === 'waiting' && $data['status'] === 'skipped'), 422);
        $ticket->update(['status' => $data['status'], 'completed_at' => in_array($data['status'], ['completed', 'skipped'], true) ? now() : null]);

        return back()->with('success', 'Status antrean diperbarui.');
    }

    public function editSchedule(): View
    {
        $schedule = ServiceSchedule::query()->firstOrNew(['service_date' => today()]);

        return view('admin.schedule', compact('schedule'));
    }

    public function saveSchedule(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service_date' => ['required', 'date', 'after_or_equal:today'],
            'location' => ['required', 'string', 'max:180'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'quota' => ['required', 'integer', 'min:1', 'max:500'],
            'service_minutes' => ['required', 'integer', 'min:1', 'max:35'],
            'info_title' => ['required', 'string', 'max:180'],
            'info_content' => ['nullable', 'string', 'max:5000'],
        ]);
        if (ServiceSchedule::query()->whereDate('service_date', $data['service_date'])->exists()) {
            ServiceSchedule::query()->whereDate('service_date', $data['service_date'])->update([...$data, 'is_active' => $request->boolean('is_active')]);
        } else {
            ServiceSchedule::query()->create([...$data, 'is_active' => $request->boolean('is_active')]);
        }

        return back()->with('success', 'Informasi dan jadwal berhasil disimpan.');
    }

    public function report(Request $request): View
    {
        [$from, $to] = $this->period($request);
        $rows = ServiceSchedule::query()->whereBetween('service_date', [$from, $to])->withCount(['tickets as total', 'tickets as served' => fn (Builder $query) => $query->where('status', 'completed'), 'tickets as skipped' => fn (Builder $query) => $query->where('status', 'skipped')])->orderByDesc('service_date')->get();

        return view('admin.report', compact('from', 'to', 'rows'));
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->period($request);

        return response()->streamDownload(function () use ($from, $to): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Tanggal', 'Nomor', 'Nama', 'Telepon', 'Keperluan', 'Status', 'Diterbitkan', 'Dilayani']);
            QueueTicket::query()->with('schedule')->whereHas('schedule', fn (Builder $query) => $query->whereBetween('service_date', [$from, $to]))->orderBy('id')->chunk(500, function ($tickets) use ($output): void {
                foreach ($tickets as $ticket) {
                    fputcsv($output, [$ticket->schedule->service_date->format('Y-m-d'), $ticket->display_number, $ticket->name, $ticket->phone, $ticket->service_type, $ticket->status, $ticket->issued_at?->format('H:i:s'), $ticket->completed_at?->format('H:i:s')]);
                }
            });
            fclose($output);
        }, 'laporan-antrean-' . $from . '-' . $to . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function period(Request $request): array
    {
        $validated = $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from']]);

        return [$validated['from'] ?? today()->subDays(6)->toDateString(), $validated['to'] ?? today()->toDateString()];
    }
}
