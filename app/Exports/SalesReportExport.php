<?php

namespace App\Exports;

use App\Models\SalesTransaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SalesReportExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    protected $fromDate;
    protected $toDate;
    protected $grandTotal = 0;
    protected $itemSummary = [];

    public function __construct($fromDate, $toDate)
    {
        $this->fromDate = Carbon::parse($fromDate)->format('F d, Y');
        $this->toDate = Carbon::parse($toDate)->format('F d, Y');
    }

    public function array(): array
    {
        $sales = SalesTransaction::with(['salesItems', 'staff', 'customer'])
            ->whereBetween('created_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ])
            ->get();
    
        $exportData = [];
        $salesSummary = [];
    
        foreach ($sales as $sale) {
            foreach ($sale->salesItems as $item) {
                $subtotal = $item->subtotal;
                $this->grandTotal += $subtotal;
    
                // Count items sold per product
                $this->itemSummary[$item->product_name] = ($this->itemSummary[$item->product_name] ?? 0) + $item->quantity;
    
                // Determine membership
                $membershipType = strtolower($sale->customer->membership_status) === 'member' ? 'Member' : 'Non-Member';
    
                // Group sales by payment method & membership
                $chargeType = $sale->credit_payment_method ?? 'Cash Sales';
                if (!isset($salesSummary[$chargeType])) {
                    $salesSummary[$chargeType] = [
                        'Member' => 0, 
                        'Non-Member' => 0, 
                        'Total' => 0
                    ];
                }
    
                $salesSummary[$chargeType][$membershipType] += $subtotal;
                $salesSummary[$chargeType]['Total'] += $subtotal;
    
                // Sales Data
                $exportData[] = [
                    Carbon::parse($sale->created_at)->format('F d, Y'),
                    $sale->po_number,
                    $sale->staff->full_name ?? 'N/A',
                    $sale->customer->full_name ?? 'N/A',
                    $sale->customer->department ?? 'N/A',
                    ucfirst($sale->customer->membership_status ?? 'Non-Member'),
                    $item->product_name,
                    number_format($item->price, 2),
                    $item->quantity,
                    number_format($subtotal, 2),
                    ucfirst($sale->payment_method),
                    $sale->credit_payment_method ?? '-',
                    $sale->remarks ?? 'N/A',
                ];
            }
        }
    
        // Append Grand Total
        if (!empty($exportData)) {
            $exportData[] = ['', '', '', '', '', '', '', '', 'Grand Total:', number_format($this->grandTotal, 2), '', ''];
            $exportData[] = ['', '', '', '', '', '', '', '', '', '', '', ''];
        }
    
        // Append Summary of Sales
        $exportData[] = ['TOTAL SALES', '', '', '', '', '', '', '', '', '', '', ''];
        $exportData[] = ['Charge Type', 'Total Sales', 'Member', 'Non-Member', '', '', '', '', '', '', '', ''];
    
        foreach ($salesSummary as $type => $data) {
            $exportData[] = [
                $type,
                number_format($data['Total'], 2),
                number_format($data['Member'], 2),
                number_format($data['Non-Member'], 2),
                '', '', '', '', '', '', '', ''
            ];
        }
    
        return $exportData;
    }    
    
    public function headings(): array
    {
        return [
            ['ORMECO EMPLOYEES MULTI-PURPOSE COOPERATIVE (OREMPCO)'],
            ['Sta. Isabel, Calapan City, Oriental Mindoro'],
            ['CDA Registration No.: 9520-04002679'],
            ['NVAT-Exempt TIN: 004-175-226-000'],
            [''],
            ['WATER STATION - SALES REPORT'],
            ["From: {$this->fromDate} - {$this->toDate}"],
            ['Date', 'SO Number', 'Staff Name', 'Customer Name', 'Department', 'Member', 'Item Sold', 'Price', 'Quantity', 'Total', 'Cash/Credit', 'Charge To', 'Remarks']
        ];
    }

    public function title(): string
    {
        return ' WATER STATION - SALES REPORT';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['italic' => true, 'size' => 12, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            3 => ['font' => ['size' => 12, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            4 => ['font' => ['size' => 12, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            6 => ['font' => ['bold' => true, 'size' => 16, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            7 => ['font' => ['bold' => true, 'size' => 12, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            8 => [
                'font' => ['bold' => true, 'name' => 'Arial'],
                'alignment' => ['horizontal' => 'center'],
                'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK]],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
    
                // Merge header cells
                foreach (range(1, 7) as $row) {
                    $sheet->mergeCells("A{$row}:M{$row}");
                }
    
                // Freeze headers
                $sheet->freezePane('A9');
    
                // Get last row and column
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();
    
                // Apply styles to sales data table
                $sheet->getStyle("A8:{$lastColumn}{$lastRow}")->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 11],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                    'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                ]);
    
                // Insert spacing before the summary table
                $summaryStartRow = $lastRow + 3; // Adds 3 empty rows before the summary
    
                // Format Summary Table Headers
                $sheet->mergeCells("A{$summaryStartRow}:M{$summaryStartRow}");
                $sheet->setCellValue("A{$summaryStartRow}", 'TOTAL SALES SUMMARY');
                $sheet->getStyle("A{$summaryStartRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => 'center'],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
                ]);
    
                // Format charge type row
                $sheet->getStyle("A" . ($summaryStartRow + 1) . ":D" . ($summaryStartRow + 1))->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => 'center'],
                    'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                ]);
    
                // Remove borders from empty columns in summary
                $sheet->getStyle("E{$summaryStartRow}:M" . ($summaryStartRow + count($this->itemSummary)))->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE]],
                ]);
    
                // Format Grand Total row
                $grandTotalRow = $summaryStartRow + count($this->itemSummary) + 3;
                $sheet->mergeCells("B{$grandTotalRow}:D{$grandTotalRow}");
                $sheet->setCellValue("B{$grandTotalRow}", "GRAND TOTAL: " . number_format($this->grandTotal, 2));
                $sheet->getStyle("B{$grandTotalRow}:D{$grandTotalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => 'center'],
                    'borders' => ['top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK]],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
                ]);
    
                // Footer Information
                $footerRow = $grandTotalRow + 3;
                $generatedOn = now()->format('F d, Y h:i A');
                $generatedBy = Auth::guard('sales')->user()->full_name ?? 'System';
    
                $sheet->setCellValue("L{$footerRow}", "Generation Date:");
                $sheet->setCellValue("M{$footerRow}", $generatedOn);
                $sheet->setCellValue("L" . ($footerRow + 1), "Generated by:");
                $sheet->setCellValue("M" . ($footerRow + 1), $generatedBy);
    
                $sheet->getStyle("L{$footerRow}:M" . ($footerRow + 1))->applyFromArray([
                    'font' => ['italic' => true, 'size' => 11, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => 'right'],
                ]);
    
                // Add Logo
                $drawing = new Drawing();
                $drawing->setName('OREMPCO Logo');
                $drawing->setDescription('Company Logo');
                $drawing->setPath(public_path('images/orempcologo.png'));
                $drawing->setHeight(80);
                $drawing->setCoordinates('A1');
                $drawing->setOffsetX(10);
                $drawing->setWorksheet($sheet);
            },
        ];
    }    
}
