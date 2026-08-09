<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Peminjaman - {{ $peminjaman->no_transaksi }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            color: #1f2937;
            background-color: #f3f4f6;
            padding: 20px;
        }
        .ticket-container {
            max-width: 720px;
            margin: 0 auto;
            background: #ffffff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .header-logo {
            max-height: 50px;
            max-width: 120px;
        }
        .header-title h1 {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
        }
        .header-title p {
            font-size: 12px;
            color: #6b7280;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
            background-color: #f9fafb;
            padding: 12px 16px;
            border-radius: 6px;
            border: 1px solid #f3f4f6;
        }
        .info-item span.label {
            font-size: 11px;
            color: #6b7280;
            display: block;
            text-transform: uppercase;
        }
        .info-item span.value {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
        }
        .highlight-date {
            color: #dc2626 !important; /* Standout color for tgl kembali */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background-color: #f9fafb;
            font-size: 11px;
            text-transform: uppercase;
            color: #4b5563;
            font-weight: 600;
        }
        .fill-box {
            display: inline-block;
            min-width: 90px;
            height: 22px;
            border-bottom: 1px solid #6b7280;
            vertical-align: bottom;
        }
        .stamp-box {
            width: 80px;
            height: 70px;
            border: 1.5px dashed #9ca3af;
            border-radius: 4px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .stamp-box span {
            font-size: 9px;
            color: #d1d5db;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .footer-ttd {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
        }
        .ttd-box {
            text-align: center;
            min-width: 200px;
        }
        .ttd-box p.jabatan {
            font-size: 12px;
            color: #4b5563;
            margin-bottom: 60px;
        }
        .ttd-box p.nama {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
            text-decoration: underline;
        }
        .action-buttons {
            max-width: 720px;
            margin: 16px auto 0 auto;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn {
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        .btn-primary {
            background-color: #2563eb;
            color: #ffffff;
        }
        .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .ticket-container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .action-buttons {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="action-buttons">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak Bukti</button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>

    <div class="ticket-container" style="margin-top: 10px;">
        <div class="header">
            <div class="header-title">
                <h1>{{ $namaPerpus }}</h1>
                <p>BUKTI PEMINJAMAN BUKU</p>
            </div>
            @if ($logoPath)
                <img src="{{ str_starts_with($logoPath, 'upload/') ? asset($logoPath) : asset('storage/'.$logoPath) }}" class="header-logo" alt="Logo">
            @endif
        </div>

        <div class="info-grid">
            <div class="info-item">
                <span class="label">No. Peminjaman</span>
                <span class="value">{{ $peminjaman->no_transaksi }}</span>
            </div>
            <div class="info-item">
                <span class="label">Peminjam (Anggota)</span>
                <span class="value">{{ $peminjaman->anggota->nama }} ({{ $peminjaman->anggota->nis }})</span>
            </div>
            <div class="info-item">
                <span class="label">Tanggal Pinjam</span>
                <span class="value">{{ $peminjaman->tanggal->format('d/m/Y') }}</span>
            </div>
            <div class="info-item">
                <span class="label">Tanggal Kembali (Jatuh Tempo)</span>
                <span class="value highlight-date">{{ $peminjaman->tanggal_jatuh_tempo->format('d/m/Y') }}</span>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 36px;">No</th>
                    <th style="width: 100px;">Kode Buku</th>
                    <th>Judul Buku</th>
                    <th style="width: 110px; text-align: center;">Tgl Dikembalikan</th>
                    <th style="width: 90px; text-align: center;">Stempel</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($peminjaman->details as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="font-family: monospace; font-weight: bold;">{{ $detail->buku->kode }}</td>
                        <td>{{ $detail->buku->judul }}</td>
                        <td style="text-align: center;">
                            <span class="fill-box"></span>
                        </td>
                        <td style="text-align: center; padding: 6px 8px;">
                            <div class="stamp-box"><span>Stempel</span></div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer-ttd">
            <div class="ttd-box">
                <p class="jabatan">{{ $ttdJabatan }}</p>
                <p class="nama">{{ $ttdNama }}</p>
            </div>
        </div>
    </div>

    <script>
        // Auto trigger print window on page open
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
