<!DOCTYPE html>
<html>
<head>
    <title>OREMPCO Sales Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8px;
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
            table-layout: auto; /* Allow columns to adjust based on content */
        }

        table, th, td {
            border: 1px solid #000;
        }

        th, td {
            padding: 4px 6px;
            text-align: left;
            font-size: 8px;
            white-space: nowrap; /* Prevent text from wrapping */
        }

        th {
            background-color: #f2f2f2;
        }

        .summary-table {
            margin-top: 20px;
            width: 80%; /* Reduce table width */
        }

        .footer {
            text-align: right;
            font-size: 8px;
            margin-top: 20px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header" style="display: flex; align-items: center; justify-content: center; gap: 10px;">
        <div>
            <img src="{{ public_path('images/orempcologo.png') }}" alt="OREMPCO Logo" style="width: 50px; height: auto;">
        </div>
        <div>
            <h2>ORMECO EMPLOYEES MULTI-PURPOSE COOPERATIVE (OREMPCO)</h2>
            <h4>Sta. Isabel, Calapan City, Oriental Mindoro</h4>
            <h4>CDA Registration No.: 9520-04002679</h4>
            <h4>NVAT-Exempt TIN: 004-175-226-000</h4>
            <h3 style="margin-top: 10px;">
                Sales Report ({{ \Carbon\Carbon::parse($fromDate)->format('F j, Y') }} - {{ \Carbon\Carbon::parse($toDate)->format('F j, Y') }})
            </h3>
        </div>
    </div>

    <!-- Sales Table -->
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>SO Number</th>
                <th>Staff Name</th>
                <th>Customer Name</th>
                <th>Department</th>
                <th>Member</th>
                <th>Item Sold</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Cash/Credit</th>
                <th>Charge To</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotal = 0;
                $itemSummary = [];
                $chargeSummary = [
                    'Cash Sales' => ['total' => 0, 'member' => 0, 'non_member' => 0],
                    'Salary Deduction' => ['total' => 0, 'member' => 0, 'non_member' => 0],
                    'Charge to ORMECO' => ['total' => 0, 'member' => 0, 'non_member' => 0],
                    'Charge to OREMPCO' => ['total' => 0, 'member' => 0, 'non_member' => 0],
                    'Charge to KKOPI. Tea' => ['total' => 0, 'member' => 0, 'non_member' => 0],
                    'Charge to La Pasta' => ['total' => 0, 'member' => 0, 'non_member' => 0],
                    'Charge to Canteen' => ['total' => 0, 'member' => 0, 'non_member' => 0],
                    'Charge to Power One' => ['total' => 0, 'member' => 0, 'non_member' => 0],
                    'Charge to Others' => ['total' => 0, 'member' => 0, 'non_member' => 0]    
                ];
            @endphp
            @foreach($sales as $sale)
                @foreach($sale->salesItems as $item)
                    @php
                        $formattedDate = \Carbon\Carbon::parse($sale->created_at)->format('m/d/y');
                        $grandTotal += $item->subtotal;

                        // Item Summary
                        if (!isset($itemSummary[$item->product_name])) {
                            $itemSummary[$item->product_name] = [
                                'name' => $item->product_name,
                                'quantity' => 0,
                                'total' => 0,
                            ];
                        }
                        $itemSummary[$item->product_name]['quantity'] += $item->quantity;
                        $itemSummary[$item->product_name]['total'] += $item->subtotal;

                        // Charge Summary
                        $chargeType = $sale->credit_payment_method ?: 'Cash Sales';
                        $isMember = strtolower($sale->customer->membership_status) === 'member';

                        $chargeSummary[$chargeType]['total'] += $item->subtotal;
                        if ($isMember) {
                            $chargeSummary[$chargeType]['member'] += $item->subtotal;
                        } else {
                            $chargeSummary[$chargeType]['non_member'] += $item->subtotal;
                        }
                    @endphp
                    <tr>
                        <td>{{ $formattedDate }}</td>
                        <td>{{ $sale->po_number }}</td>
                        <td>{{ $sale->staff->full_name }}</td>
                        <td>{{ $sale->customer->full_name }}</td>
                        <td>{{ $sale->customer->department }}</td>
                        <td>{{ $sale->customer->membership_status }}</td>
                        <td>{{ $item->product_name }}</td> 
                        <td>₱{{ number_format($item->price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₱{{ number_format($item->subtotal, 2) }}</td>
                        <td>{{ ucfirst($sale->payment_method) }}</td>
                        <td>{{ $sale->credit_payment_method ?? '-' }}</td>
                        <td>{{ $sale->remarks }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <!-- Summary Table -->
    <h3>Summary</h3>
    <table class="summary-table">
        <thead>
            <tr>
                <th style="width: 50%;">Item Name</th>
                <th style="width: 25%;">Quantity Sold</th>
                <th style="width: 25%;">Total Sales</th>
            </tr>
        </thead>
        <tbody>
            @php
                $overallTotalQuantity = 0;
            @endphp
            @foreach($itemSummary as $summary)
                @php
                    $overallTotalQuantity += $summary['quantity'];
                @endphp
                <tr>
                    <td>{{ $summary['name'] }}</td>
                    <td>{{ $summary['quantity'] }}</td>
                    <td>₱{{ number_format($summary['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="text-align: right;">
                    Overall Total Quantity Sold: <strong>{{ $overallTotalQuantity }}</strong> | 
                    Grand Total Sales: <strong>₱{{ number_format($grandTotal, 2) }}</strong>
                </th>
            </tr>
        </tfoot>
    </table>

    <!-- Total Sales Table -->
    <h3>Total Sales</h3>
    <table class="summary-table">
        <thead>
            <tr>
                <th style="width: 40%;">Charge Type</th>
                <th style="width: 20%;">Total Sales</th>
                <th style="width: 20%;">Member</th>
                <th style="width: 20%;">Non-Member</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalChargeSales = 0;
                $totalMemberSales = 0;
                $totalNonMemberSales = 0;
            @endphp
            @foreach($chargeSummary as $chargeType => $chargeData)
                @php
                    $totalChargeSales += $chargeData['total'];
                    $totalMemberSales += $chargeData['member'];
                    $totalNonMemberSales += $chargeData['non_member'];
                @endphp
                <tr>
                    <td>{{ $chargeType }}</td>
                    <td>₱{{ number_format($chargeData['total'], 2) }}</td>
                    <td>₱{{ number_format($chargeData['member'], 2) }}</td>
                    <td>₱{{ number_format($chargeData['non_member'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th style="text-align: right;">Grand Total:</th>
                <th>₱{{ number_format($totalChargeSales, 2) }}</th>
                <th>₱{{ number_format($totalMemberSales, 2) }}</th>
                <th>₱{{ number_format($totalNonMemberSales, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <!-- Footer (Appears after all tables) -->
    <div class="footer">
        <p>Generation Date: {{ \Carbon\Carbon::now()->format('m/d/y h:i A') }}</p>
        <p>Generated by: {{ Auth::guard('admin')->user()->full_name }}</p>
    </div>

</body>
</html>