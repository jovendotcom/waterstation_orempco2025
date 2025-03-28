<!DOCTYPE html>
<html>
<head>
    <title>Material Inventory Report</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h2, .header h4 {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
            table-layout: fixed; /* Ensures all tables are uniformly aligned */
        }

        table, th, td {
            border: 1px solid #000;
        }

        th, td {
            padding: 6px 8px;
            text-align: left;
            word-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
        }

        .footer {
            text-align: right;
            font-size: 8px;
            margin-top: 20px;
        }

        .inventory-date {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .category-title {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        /* Column Widths for Alignment */
        .col-name { width: 30%; }
        .col-stocks { width: 15%; }
        .col-price { width: 15%; }
        .col-limit { width: 15%; }
        .col-remarks { width: 25%; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header" style="display: flex; align-items: center; justify-content: center; gap: 20px;">
        <!-- Logo -->
        <div>
            <img src="{{ public_path('images/orempcologo.png') }}" alt="OREMPCO Logo" style="width: 80px; height: auto;">
        </div>

        <!-- Title and Details -->
        <div style="text-align: center;">
            <h2>ORMECO EMPLOYEES MULTI-PURPOSE COOPERATIVE (OREMPCO)</h2>
            <h4>Sta. Isabel, Calapan City, Oriental Mindoro</h4>
            <h4>CDA Registration No.: 9520-04002679</h4>
            <h4>NVAT-Exempt TIN: 004-175-226-000</h4>
            <h3 style="margin-top: 20px;">Material Inventory List</h3>
        </div>
    </div>

    <!-- Date of Inventory -->
    <p class="inventory-date"><strong>Date of Inventory:</strong> {{ \Carbon\Carbon::now()->format('m/d/Y') }}</p>

    <!-- Loop through each category -->
    @foreach($groupedMaterials as $categoryName => $materials)
        <!-- Category Title -->
        <div class="category-title">
            <strong>Category:</strong> {{ $categoryName }}
        </div>

        <!-- Inventory Table for the Category -->
        <table>
            <thead>
                <tr>
                    <th class="col-name">Material Name</th>
                    <th class="col-stocks">Total Stocks</th>
                    <th class="col-price">Price Per Unit</th>
                    <th class="col-limit">Low Stock Limit</th>
                    <th class="col-remarks">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($materials as $material)
                    <tr>
                        <td class="col-name">{{ $material->material_name }}</td>
                        <td class="col-stocks">{{ $material->total_stocks }} {{ $material->unit }}</td>
                        <td class="col-price">₱{{ number_format($material->cost_per_unit, 2) }}</td>
                        <td class="col-limit">{{ $material->low_stock_limit ?? 'N/A' }}</td>
                        <td class="col-remarks">
                            @if($material->total_stocks == 0)
                                <span style="color: red;">Out of Stock</span>
                            @elseif($material->low_stock_limit && $material->total_stocks <= $material->low_stock_limit)
                                <span style="color: orange;">Low Stock</span>
                            @else
                                <span style="color: green;">In Stock</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <!-- Footer -->
    <div class="footer">
        <p>Generated on: {{ now()->format('F d, Y h:i A') }}</p>
        <p>Generated by: {{ Auth::guard('admin')->user()->full_name }}</p>
    </div>

</body>
</html>
