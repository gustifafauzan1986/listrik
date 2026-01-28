<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Absensi | {{ \App\Models\Setting::value('app_name', 'GATECH') }} {{ \App\Models\Setting::value('school_name', 'Sekolah') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $favicon = \App\Models\Setting::value('app_favicon');
    @endphp
    @if($favicon)
        <link rel="icon" href="{{ public_path('storage/'.$favicon) }}" type="image/x-icon"/>
    @else
        <link rel="icon" href="{{ public_path('backend/assets/images/favicon-32x32.png') }}" type="image/x-icon"/>
    @endif

    <style>
        @page {
            margin-top: {{ $school['margin_top'] ?? '2.5cm' }};
            margin-right: {{ $school['margin_right'] ?? '2.5cm' }};
            margin-bottom: {{ $school['margin_bottom'] ?? '2.5cm' }};
            margin-left: {{ $school['margin_left'] ?? '2.5cm' }};
        }
        body { font-family: sans-serif; font-size: 11px; }

        /* Kop Surat */
        .header-table { width: 100%; border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; }
        .header-table td { vertical-align: middle; }
        .logo-img { width: 80px; height: auto; object-fit: contain; }
        .school-info { text-align: center; }
        .school-info h1 { margin: 0; font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .school-info p { margin: 0; font-size: 9pt; }

        h3, h4, h5 { margin: 5px 0; text-align: center; text-transform: uppercase; }

        /* Tabel Data */
        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        table.data th, table.data td { border: 1px solid #000; padding: 4px; text-align: left; vertical-align: top; }
        table.data th { background-color: #f0f0f0; text-align: center; font-weight: bold; }

        .text-center { text-align: center; }

        /* Badge Status */
        .badge { padding: 2px 5px; border-radius: 3px; color: white; font-size: 9px; font-weight: bold; text-transform: uppercase; display: inline-block; min-width: 50px; text-align: center; }
        .bg-hadir { background-color: green; }
        .bg-terlambat { background-color: orange; color: black; }
        .bg-izin { background-color: blue; }
        .bg-sakit { background-color: purple; }
        .bg-alpa { background-color: red; }

        /* Section Jurnal */
        .journal-section { margin-top: 20px; page-break-inside: avoid; }
        .section-header { font-weight: bold; font-size: 12px; margin-bottom: 5px; border-bottom: 1px solid #000; display: block; padding-bottom: 2px; }

        /* Tanda Tangan */
        .footer-section { margin-top: 30px; width: 100%; page-break-inside: avoid; }
        .ttd-box { float: right; width: 250px; text-align: center; }

        /* Footer Print */
        .footer-print { position: fixed; bottom: -30px; left: 0; right: 0; text-align: center; font-size: 9px; color: #555; border-top: 1px solid #ccc; padding-top: 5px; }
    </style>
</head>
<body>

    <!-- KOP SURAT -->
    <table class="header-table">
        <tr>
            <td width="15%" class="text-center">
                @if(!empty($school['logo_left']))
                    <img src="{{ $school['logo_left'] }}" class="logo-img">
                @endif
            </td>
            <td width="70%" class="school-info">
                @if(isset($school['provinsi_name']))
                    <div style="font-size: 12pt;">PEMERINTAH {{ strtoupper($school['provinsi_name']) }}</div>
                @endif
                {{-- <div style="font-size: 12pt;">DINAS PENDIDIKAN</div> --}}
                <h1>{{ $school['school_name'] ?? 'SEKOLAH' }}</h1>
                <p>{{ $school['school_address'] ?? 'Alamat Sekolah' }}</p>
                <p>Telp: {{ $school['school_phone'] ?? '-' }} | Email: {{ $school['school_email'] ?? '-' }}</p>
                <p>Website: {{ $school['school_web'] ?? '-' }}</p>
            </td>
            <td width="15%" class="text-center">
                @if(!empty($school['logo_right']))
                    <img src="{{ $school['logo_right'] }}" class="logo-img">
                @endif
            </td>
        </tr>
    </table>

    <!-- JUDUL -->
    <h3>LAPORAN ABSENSI & JURNAL PEMBELAJARAN</h3>
    <h4>{{ $labelPeriode }}</h4>
    @if(isset($labelTambahan)) <h5>{{ $labelTambahan }}</h5> @endif

    <!-- A. TABEL KEHADIRAN SISWA -->
    <div class="section-header">A. DATA KEHADIRAN SISWA</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 12%">Tanggal</th>
                <th style="width: 8%">Jam</th>
                <th style="width: 10%">NIS</th>
                <th>Nama Siswa</th>
                <th style="width: 15%">Kelas</th>
                <th>Mata Pelajaran</th>
                <th style="width: 10%">Ket</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($row->date)->translatedFormat('d/m/y') }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($row->check_in_time)->format('H:i') }}</td>
                <td>{{ $row->student->nis }}</td>
                <td>{{ $row->student->name }}</td>
                <td class="text-center">{{ $row->student->classroom->name ?? '-' }}</td>
                <td>{{ $row->schedule->subject->name ?? '-' }}</td>
                <td class="text-center">
                    @php
                        $statusClass = match($row->status) {
                            'hadir' => 'bg-hadir', 'terlambat' => 'bg-terlambat',
                            'izin' => 'bg-izin', 'sakit' => 'bg-sakit', default => 'bg-alpa'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ ucfirst($row->status) }}</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center">Tidak ada data absensi.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- B. TABEL JURNAL PEMBELAJARAN -->
    @if(isset($journals) && $journals->count() > 0)
    <div class="journal-section">
        <div class="section-header">B. JURNAL PEMBELAJARAN</div>
        <table class="data">
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th style="width: 12%">Tanggal</th>
                    <th style="width: 20%">Mata Pelajaran</th>
                    <th style="width: 25%">Materi / Topik</th>
                    <th>Aktivitas / Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($journals as $idx => $j)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($j->date)->translatedFormat('d/m/y') }}</td>
                    <td>
                        {{ $j->schedule->subject->name ?? '-' }}
                        <br><small>({{ $j->schedule->classroom->name ?? '-' }})</small>
                    </td>
                    <td>{{ $j->topic }}</td>
                    <td>
                        <strong>Akt:</strong> {{ $j->activity }}
                        @if($j->notes) <br><em>Cat: {{ $j->notes }}</em> @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- TANDA TANGAN -->
    <div class="footer-section">
        <div class="ttd-box">
            <p>{{ $school['sign_city'] ?? 'Kota' }}, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>

            @if($isTeacher && isset($user->teacher))
                <p>Guru Mata Pelajaran,</p>
                <br><br><br>
                <p style="text-decoration: underline; font-weight: bold;">
                    {{ $user->name }}
                </p>
                <p>NIP. {{ $user->teacher->nip ?? '-' }}</p>
            @else
                <p>{{ $school['sign_title'] ?? 'Kepala Sekolah' }},</p>

                @if(!empty($school['sign_image']))
                    <div style="height: 60px; margin: 5px auto; display: flex; justify-content: center;">
                        <img src="{{ $school['sign_image'] }}" style="height: 60px; max-width: 100%;">
                    </div>
                @else
                    <br><br><br>
                @endif

                <p style="text-decoration: underline; font-weight: bold;">{{ $school['sign_name'] }}</p>
                <p>NIP. {{ $school['sign_nip'] }}</p>
            @endif
        </div>
        <div style="clear: both;"></div>
    </div>

    <!-- Page Number Script -->
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Hal {PAGE_NUM} dari {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("Helvetica", "italic");
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $pdf->page_text($pdf->get_width() - $width - 30, $pdf->get_height() - 20, $text, $font, $size);
        }
    </script>

</body>
</html>
