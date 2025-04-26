<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $sales->no_nota }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        
        #invoice-POS {
            width: 100%;
            margin: 0 auto;
            padding: 5px;
            background: #FFF;
        }

        h1, h2, h3, h4 {
            margin: 2px 0;
        }

        h1 {
            font-size: 16px;
        }

        h2 {
            font-size: 14px;
        }

        p {
            margin: 2px 0;
            font-size: 12px;
        }

        .border-bottom {
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .text-center {
            text-align: center;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        .company-info {
            text-align: center;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }

        .table-header {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .item-row td {
            border-bottom: 1px dotted #ddd;
            padding: 2px 0;
        }

        .total-row {
            font-weight: bold;
        }

        .receipt-header {
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            margin: 10px 0;
        }

        .invoice-info {
            text-align: right;
            font-size: 11px;
        }

        .customer-info {
            margin: 5px 0;
        }

        .legal-copy {
            border-top: 1px solid #333;
            margin-top: 10px;
            padding-top: 5px;
            font-size: 10px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div id="invoice-POS">
        <!-- Company Header Section -->
        <div class="border-bottom">
            <div class="company-name">{{ $company->name }}</div>
            <div class="company-info">
                {{ $company->address }}<br>
                Tel: {{ $company->phone_number }} | Email: {{ $company->email }}
            </div>
            
            <div class="receipt-header">BUKTI PEMBAYARAN SAH</div>
            
            <div class="invoice-info">
                No. Invoice: {{ $sales->noNota }}<br>
                Tanggal: {{ date('d/m/Y', strtotime($sales->sales_date)) }}
            </div>
        </div>

        <!-- Customer Information Section -->
        <div class="border-bottom customer-info">
            <h2>Informasi Pembeli</h2>
            <p>
                Nama Customer: {{ $sales->customer->name }}<br>
                Email: {{ $sales->customer->email }}<br>
                Nomer Hp: {{ $sales->customer->phone_number }}
            </p>
        </div>

        <!-- Items Section -->
        <div>
            <table>
                <tr class="table-header">
                    <td width="60%">Item</td>
                    <td width="15%">Qty</td>
                    <td width="25%">Sub Total</td>
                </tr>

                @foreach ($sales->salesDetail as $item)
                    <tr class="item-row">
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->total_quantity }}</td>
                        <td>Rp.{{ number_format($item->total_price) }}</td>
                    </tr>
                @endforeach
            </table>

            <table>
                <tr class="total-row">
                    <td width="75%">Total</td>
                    <td width="25%">Rp.{{ number_format($sales->total_price) }}</td>
                </tr>

                <tr>
                    <td>Discount</td>
                    <td>
                        Rp.{{ number_format($sales->discount) }}
                        ({{ number_format(($sales->discount / ($sales->discount + $sales->total_price)) * 100, 2) }}%)
                    </td>
                </tr>
                
                <tr class="total-row">
                    <td>Grand Total</td>
                    <td>Rp.{{ number_format($sales->total_price - $sales->discount) }}</td>
                </tr>
            </table>

            <div class="legal-copy">
                <strong>Terimakasih atas pembelian anda!</strong><br>
                Bukti pembayaran yang sah ini berlaku hingga 7 hari!
            </div>
        </div>
    </div>
</body>

</html>