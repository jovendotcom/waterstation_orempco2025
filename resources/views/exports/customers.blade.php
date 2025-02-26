<!DOCTYPE html>
<html>
<head>
    <title>OREMPCO Water Station - Customers List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            position: relative;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2, .header h4 {
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px 12px; /* Padding for better spacing */
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        thead {
            display: table-header-group; /* Keeps table headers on each page */
        }
        /* Footer styling */
        .footer {
            position: fixed;
            bottom: 20px;
            right: 30px;
            font-size: 10px;
            text-align: right;
        }
    </style>
</head>
<body>
    <!-- Header for First Page Only -->
    <div class="header" style="display: flex; align-items: center; justify-content: center; gap: 20px;">
        <!-- Logo -->
        <div>
            <img src="{{ ('images/orempcologo.png') }}" alt="OREMPCO Logo" style="width: 80px; height: auto;">
        </div>

        <!-- Title and Details -->
        <div style="text-align: center;">
            <h2>ORMECO EMPLOYEES MULTI-PURPOSE COOPERATIVE (OREMPCO)</h2>
            <h4>Sta. Isabel, Calapan City, Oriental Mindoro</h4>
            <h4>CDA Registration No.: 9520-04002679</h4>
            <h4>NVAT-Exempt TIN: 004-175-226-000</h4>
            <h3 style="margin-top: 20px;">Customer List</h3>
        </div>
    </div>

    <!-- Customer Table -->
    <table>
        <thead>
            <tr>
                <th>EMPLOYEE ID</th>
                <th>FULL NAME</th>
                <th>DEPARTMENT</th>
                <th>TYPE</th>
                <th>MEMBER/NON-MEMBER</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Sort customers by type and full name
                $sortedCustomers = $customers->sortBy(function($customer) {
                    $typeOrder = ['Department', 'Employee', 'Outside'];
                    return array_search($customer->type, $typeOrder) . $customer->full_name;
                });
            @endphp

            @foreach($sortedCustomers as $customer)
                <tr>
                    <td>{{ $customer->employee_id }}</td>
                    <td>{{ $customer->full_name }}</td>
                    <td>{{ $customer->department }}</td>
                    <td>{{ $customer->type }}</td>
                    <td>{{ $customer->membership_status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer" style="text-align: right; font-size: 12px; margin-top: 20px; font-style: italic;">
        <p>Generation Date: {{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}</p>
        <p>Generated by: {{ Auth::guard('sales')->user()->full_name }}</p>
    </div>
</body>
</html>
