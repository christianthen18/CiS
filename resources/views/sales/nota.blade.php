@extends('layouts.conquer')
@section('content')
    
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 70%;
            margin: 20px auto;
            border: 1px solid #ddd;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
        }

        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }

        .company-info p {
            margin: 5px 0;
        }

        .customer-info, .sales-info {
            margin-bottom: 20px;
        }

        .customer-info h3, .sales-info h3 {
            margin-bottom: 10px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .details-table th, .details-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        .details-table th {
            background-color: #f4f4f4;
        }

        .total-section {
            text-align: right;
            margin-top: 20px;
        }

        .total-section h2 {
            margin: 0;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Nota Penjualan</h1>
    </div>

    <div class="company-info">
        <p><strong>Toko Amin Elektronik</strong></p>
        <p>Jalan Raya No. 123, Jakarta</p>
        <p>Telepon: 021-12345678</p>
    </div>

    <div class="sales-info">
        <h3>Informasi Penjualan</h3>
        <p>Nomor Nota: <?php echo "001234"; ?></p>
        <p>Tanggal: <?php echo date("d-m-Y"); ?></p>
    </div>

    <div class="customer-info">
        <h3>Informasi Pelanggan</h3>
        <p>Nama: <?php echo "John Doe"; ?></p>
        <p>Alamat: <?php echo "Jalan Mawar No. 45, Jakarta"; ?></p>
        <p>Telepon: <?php echo "081234567890"; ?></p>
    </div>

    <table class="details-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Contoh data produk
            $produk = [
                ["nama" => "TV Samsung", "jumlah" => 1, "harga" => 3000000],
                ["nama" => "Kulkas LG", "jumlah" => 2, "harga" => 2500000],
                ["nama" => "AC Panasonic", "jumlah" => 1, "harga" => 4000000]
            ];

            $totalKeseluruhan = 0;
            foreach ($produk as $index => $item) {
                $total = $item['jumlah'] * $item['harga'];
                $totalKeseluruhan += $total;
                echo "<tr>
                        <td>" . ($index + 1) . "</td>
                        <td>" . $item['nama'] . "</td>
                        <td>" . $item['jumlah'] . "</td>
                        <td>Rp " . number_format($item['harga'], 0, ',', '.') . "</td>
                        <td>Rp " . number_format($total, 0, ',', '.') . "</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="total-section">
        <h2>Total Pembayaran: Rp <?php echo number_format($totalKeseluruhan, 0, ',', '.'); ?></h2>
    </div>
</div>

</body>
</html>

@endsection

