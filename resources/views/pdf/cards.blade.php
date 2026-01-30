<!DOCTYPE html>
<html>
<head>
    <title>Cetak Kartu</title>
    <style>
        @page { margin: 1cm; }
        body { font-family: Arial, sans-serif; font-size: 10pt; margin: 0; padding: 0; }

        /* Container per halaman */
        .page {
            width: 100%;
            height: 100%;
            /* Clearfix untuk float */
            overflow: visible;
        }

        /* Desain Kartu (9cm x 5.4cm) */
        .card {
            width: 8.8cm;
            height: 5.4cm;
            border: 1px solid #000;
            float: left;
            margin-right: 0.5cm;
            margin-bottom: 0.5cm; /* Jarak bawah antar kartu */
            position: relative;
            padding: 5px;
            box-sizing: border-box;
            background: #fff;
        }

        /* Hilangkan margin kanan untuk kartu di kolom kedua (genap) */
        .card:nth-child(even) { margin-right: 0; }

        /* Page Break: Paksa pindah halaman setelah setiap chunk */
        .page-break { page-break-after: always; clear: both; }

        /* Kop Kartu */
        .card-header { border-bottom: 2px solid #000; padding-bottom: 2px; margin-bottom: 5px; display: table; width: 100%; }
        .header-logo { display: table-cell; width: 40px; vertical-align: middle; }
        .header-logo img { width: 35px; height: auto; }
        .header-text { display: table-cell; vertical-align: middle; text-align: center; line-height: 1; }
        .header-text h3 { margin: 0; font-size: 8pt; font-weight: bold; }
        .header-text h4 { margin: 0; font-size: 6pt; }

        .event-title { text-align: center; font-weight: bold; font-size: 9pt; background-color: #eee; border: 1px solid #ccc; margin-bottom: 5px; padding: 2px; text-transform: uppercase; }

        .card-body { display: table; width: 100%; font-size: 8pt; }
        .photo-box { display: table-cell; width: 2cm; height: 2.5cm; border: 1px solid #999; text-align: center; vertical-align: middle; font-size: 7pt; color: #999; }
        .bio-box { display: table-cell; vertical-align: top; padding-left: 5px; }
        .bio-row { margin-bottom: 2px; }
        .label { display: inline-block; width: 45px; font-weight: bold; }

        .card-footer { margin-top: 2px; text-align: right; font-size: 7pt; }
        .ttd-img { height: 25px; display: block; margin-left: auto; margin-right: 5px; }
    </style>
</head>
<body>

    <!-- Loop per Halaman (Chunk) -->
    @foreach($chunks as $pageUsers)
        <div class="page">
            @foreach($pageUsers as $u)
                <div class="card">
                    <!-- Header -->
                    <div class="card-header">
                        <div class="header-logo">
                            @if(!empty($school['logo_left'])) <img src="{{ $school['logo_left'] }}"> @endif
                        </div>
                        <div class="header-text">
                            <h3>{{ \Illuminate\Support\Str::limit($school['school_name'], 25) }}</h3>
                            <h4>{{ \Illuminate\Support\Str::limit($school['school_address'], 50) }}</h4>
                        </div>
                        <div class="header-logo" style="text-align: right;">
                            @if(!empty($school['logo_right'])) <img src="{{ $school['logo_right'] }}"> @endif
                        </div>
                    </div>

                    <!-- Judul -->
                    <div class="event-title">
                        {{ $cardInfo['title_1'] }}<br>
                        <span style="font-size: 7pt; font-weight: normal;">{{ $cardInfo['title_2'] }}</span>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <div class="photo-box">FOTO<br>2x3</div>
                        <div class="bio-box">
                            @if($cardInfo['type'] == 'student')
                                <div class="bio-row"><span class="label">Nama</span>: {{ \Illuminate\Support\Str::limit($u->name, 16) }}</div>
                                <div class="bio-row"><span class="label">NIS</span>: {{ $u->nis }}</div>
                                <div class="bio-row"><span class="label">Kelas</span>: {{ $u->classroom->name ?? '-' }}</div>
                            @else
                                <div class="bio-row"><span class="label">Nama</span>: {{ \Illuminate\Support\Str::limit($u->user->name, 16) }}</div>
                                <div class="bio-row"><span class="label">NIP</span>: {{ $u->nip ?? '-' }}</div>
                                <div class="bio-row"><span class="label">Status</span>: PENGAWAS</div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer">
                        <div>Jakarta, {{ $cardInfo['date'] }}</div>
                        <div>Kepala Sekolah,</div>
                        @if(!empty($school['sign_image'])) <img src="{{ $school['sign_image'] }}" class="ttd-img"> @else <br><br> @endif
                        <div style="font-weight: bold; text-decoration: underline;">{{ $school['sign_name'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Page Break setelah setiap chunk, KECUALI yang terakhir -->
        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

</body>
</html>
