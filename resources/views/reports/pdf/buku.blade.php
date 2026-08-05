<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Buku</title>
    <style>
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Laporan Buku</h2>
    <table>
        <thead><tr>@foreach ($headings as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>