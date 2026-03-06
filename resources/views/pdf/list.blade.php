<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Reset dan base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.4;
            background-color: #fff;
            padding: 20px;
        }
        
        /* Header */
        .header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #4361ee;
        }
        
        .header h1 {
            color: #4361ee;
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 700;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 12px;
            color: #666;
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
        }
        
        .meta-item strong {
            color: #333;
            font-size: 13px;
        }
        
        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }
        
        thead {
            background-color: #4361ee;
            color: white;
        }
        
        th {
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }
        
        tbody tr {
            border-bottom: 1px solid #e1e5eb;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        td {
            padding: 10px 8px;
            border: none;
        }
        
        /* Column specific styles */
        .code-col {
            font-weight: 600;
            color: #4361ee;
        }
        
        .stock-col {
            text-align: center;
            font-weight: 600;
        }
        
        .price-col {
            text-align: right;
            font-weight: 600;
            color: #2e7d32;
        }
        
        /* Status badges untuk stock */
        .stock-high {
            background-color: #d1fae5;
            color: #065f46;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
        }
        
        .stock-medium {
            background-color: #fef3c7;
            color: #92400e;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
        }
        
        .stock-low {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
        }
        
        /* Summary */
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #4361ee;
        }
        
        .summary-title {
            font-weight: 600;
            color: #4361ee;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .summary-content {
            display: flex;
            justify-content: space-between;
        }
        
        .summary-item {
            text-align: center;
            padding: 0 15px;
        }
        
        .summary-value {
            font-size: 18px;
            font-weight: 700;
            color: #333;
        }
        
        .summary-label {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e1e5eb;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        
        /* Watermark untuk draft */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(67, 97, 238, 0.1);
            font-weight: bold;
            z-index: -1;
            white-space: nowrap;
        }
        
        /* Print styles */
        @media print {
            body {
                padding: 10px;
            }
            
            .no-print {
                display: none;
            }
            
            .watermark {
                opacity: 0.05;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>

    <!-- Watermark (opsional) -->
    <div class="watermark">DAFTAR BARANG</div>

    <!-- Header -->
    <div class="header">
        <h1>Daftar Barang Inventori</h1>
        <div class="subtitle">Dokumen resmi daftar barang dalam sistem</div>
        
        <div class="meta-info">
            <div class="meta-item">
                <span>Tanggal Cetak</span>
                <strong>{{ date('d/m/Y H:i') }}</strong>
            </div>
            <div class="meta-item">
                <span>Total Barang</span>
                <strong>{{ count($list) }} item</strong>
            </div>
            <div class="meta-item">
                <span>Halaman</span>
                <strong>1/1</strong>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode Barang</th>
                <th width="40%">Nama Barang</th>
                <th width="15%" style="text-align: center;">Stok</th>
                <th width="25%" style="text-align: right;">Harga (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @if(count($list) > 0)
                @php
                    $total_stock = 0;
                    $total_value = 0;
                @endphp
                
                @foreach($list as $index => $item)
                    @php
                        $total_stock += $item['stock'];
                        $total_value += ($item['price'] * $item['stock']);
                        
                        // Tentukan kelas stock berdasarkan jumlah
                        if($item['stock'] > 20) {
                            $stock_class = 'stock-high';
                            $stock_status = 'Tinggi';
                        } elseif($item['stock'] > 5) {
                            $stock_class = 'stock-medium';
                            $stock_status = 'Sedang';
                        } else {
                            $stock_class = 'stock-low';
                            $stock_status = 'Rendah';
                        }
                    @endphp
                    
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="code-col">{{ $item['code'] }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td class="stock-col">
                            <span class="{{ $stock_class }}" title="{{ $stock_status }}">
                                {{ $item['stock'] }} unit
                            </span>
                        </td>
                        <td class="price-col">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #666;">
                        <em>Tidak ada data barang</em>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Summary Section -->
    @if(count($list) > 0)
        <div class="summary">
            <div class="summary-title">Ringkasan Inventori</div>
            <div class="summary-content">
                <div class="summary-item">
                    <div class="summary-value">{{ count($list) }}</div>
                    <div class="summary-label">Total Jenis Barang</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">{{ number_format($total_stock, 0, ',', '.') }}</div>
                    <div class="summary-label">Total Unit Barang</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">Rp {{ number_format($total_value, 0, ',', '.') }}</div>
                    <div class="summary-label">Total Nilai Inventori</div>
                </div>
                <div class="summary-item">
                    @php
                        $avg_price = count($list) > 0 ? $total_value / $total_stock : 0;
                    @endphp
                    <div class="summary-value">Rp {{ number_format($avg_price, 0, ',', '.') }}</div>
                    <div class="summary-label">Rata-rata Harga per Unit</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div>Dokumen ini dicetak otomatis dari Sistem Manajemen Inventori</div>
        <div>Halaman 1 dari 1 • {{ date('d F Y, H:i:s') }}</div>
        <div style="margin-top: 5px; font-size: 9px; color: #999;">
            * Stok Tinggi (>20), Stok Sedang (6-20), Stok Rendah (≤5)
        </div>
    </div>

</body>
</html>