<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Absensi</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e2336; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 18px; text-transform: uppercase; color: #1e2336; }
        .header p { margin: 4px 0 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background-color: #1e2336; color: #ffffff; font-weight: bold; text-align: center; }
        tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
        .text-center { text-align: center; }
        .badge-hadir { color: #15803d; font-weight: bold; }
        .badge-telat { color: #b45309; font-weight: bold; }
        .badge-izin { color: #1d4ed8; font-weight: bold; }
        .badge-alpha { color: #b91c1c; font-weight: bold; }
        .footer { margin-top: 25px; text-align: right; font-size: 11px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Rekapitulasi Kehadiran & Lembur Karyawan</h2>
        <p>
            Periode: <strong>{{ ($startDate !== 'Awal' && strtotime($startDate)) ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : $startDate }}</strong> 
            s/d 
            <strong>{{ ($endDate !== 'Sekarang' && strtotime($endDate)) ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : $endDate }}</strong>
            &nbsp;|&nbsp; 
            Divisi: <strong>{{ $divisi === 'all' ? 'Semua Divisi' : strtoupper(str_replace('_', ' ', $divisi)) }}</strong>
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="10%">Tanggal</th>
                <th width="18%">Nama Karyawan</th>
                <th width="12%">Divisi</th>
                <th width="9%">Masuk</th>
                <th width="9%">Keluar</th>
                <th width="9%">Status</th>
                <th width="12%">Lembur (Sah)</th>
                <th>Catatan Tugas / Lembur</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $absen)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($absen->date)->format('d/m/Y') }}</td>
                <td><strong>{{ $absen->user->name ?? 'User Terhapus' }}</strong></td>
                <td class="text-center">{{ ucwords(str_replace('_', ' ', $absen->user->role ?? '-')) }}</td>
                <td class="text-center">{{ $absen->clock_in ? \Carbon\Carbon::parse($absen->clock_in)->format('H:i') : '-' }}</td>
                <td class="text-center">{{ $absen->clock_out ? \Carbon\Carbon::parse($absen->clock_out)->format('H:i') : '-' }}</td>
                <td class="text-center">
                    <span class="badge-{{ $absen->status }}">{{ strtoupper($absen->status) }}</span>
                </td>
                <td class="text-center">
                    @if($absen->is_overtime && $absen->overtime_status === 'approved')
                        {{ floor($absen->overtime_minutes / 60) }} Jam {{ $absen->overtime_minutes % 60 }} Mnt
                    @elseif($absen->is_overtime && $absen->overtime_status === 'pending')
                        <em>Pending</em>
                    @else
                        -
                    @endif
                </td>
                <td>{{ $absen->overtime_reason ?? $absen->notes ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Tidak ada data absensi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <table style="width: 100%; border: none; margin-top: 20px;">
        <tr style="border: none;">
            <td style="width: 50%; border: none; vertical-align: top; font-size: 11px; color: #475569; padding: 0;">
                <p style="margin: 0;">Total Catatan: <strong>{{ count($attendances) }}</strong> data</p>
            </td>
            <td style="width: 50%; border: none; vertical-align: top; text-align: right; padding: 0;">
                <div class="footer" style="margin-top: 0;">
                    <p style="margin: 0 0 50px 0;">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</p>
                    <p style="margin: 0;"><strong>( HRD / Admin Manajemen )</strong></p>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>