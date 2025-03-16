<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing; // ✅ Import Drawing class
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Auth;

class CustomersExportAdmin implements FromArray, WithHeadings, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    /**
     * Prepare and sort data before exporting.
     */
    public function array(): array
    {
        $customers = Customer::all();
        $typeOrder = ['Department', 'Employee', 'Outside'];
        $exportData = [];

        foreach ($typeOrder as $type) {
            $filtered = $customers->where('type', $type)->sortBy('full_name');

            foreach ($filtered as $customer) {
                $exportData[] = [
                    $customer->employee_id,
                    $customer->full_name,
                    $customer->department,
                    $customer->type,
                    $customer->membership_status,
                ];
            }

            if ($filtered->isNotEmpty()) {
                $exportData[] = ['', '', '', '', ''];
            }
        }

        if (!empty($exportData) && empty(array_filter(end($exportData)))) {
            array_pop($exportData);
        }

        return $exportData;
    }

    /**
     * Define table headers.
     */
    public function headings(): array
    {
        return [
            ['ORMECO EMPLOYEES MULTI-PURPOSE COOPERATIVE (OREMPCO)'],
            ['Sta. Isabel, Calapan City, Oriental Mindoro'],
            ['CDA Registration No.: 9520-04002679'],
            ['NVAT-Exempt TIN: 004-175-226-000'],
            [''], 
            ['Customer List'],
            ['EMPLOYEE ID', 'FULL NAME', 'DEPARTMENT', 'TYPE', 'MEMBER/NON-MEMBER'],
        ];
    }

    /**
     * Set the worksheet title.
     */
    public function title(): string
    {
        return 'Customers';
    }

    /**
     * Apply base styles to the sheet.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style Cooperative Name
            1 => ['font' => ['bold' => true, 'size' => 14, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            // Style Address
            2 => ['font' => ['italic' => true, 'size' => 12, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            // Style CDA Registration
            3 => ['font' => ['size' => 12, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            // Style NVAT TIN
            4 => ['font' => ['size' => 12, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            // Style the Customer List title
            6 => ['font' => ['bold' => true, 'size' => 16, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            // ✅ Style the table header row with yellow background
            7 => [
                'font' => ['bold' => true, 'name' => 'Arial'],
                'alignment' => ['horizontal' => 'center'],
                'borders' => [
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFF00'], // ✅ Yellow background
                ],
            ],
        ];
    }



    /**
     * Apply borders, images, footer, and more.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ✅ Merge header cells
                foreach (range(1, 6) as $row) {
                    $sheet->mergeCells("A{$row}:E{$row}");
                }

                // ✅ Freeze header
                $sheet->freezePane('A8');

                // ✅ Apply borders and styles
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();
                $cellRange = 'A7:' . $lastColumn . $lastRow;

                $sheet->getStyle($cellRange)->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 11],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'indent' => 1,
                    ],
                ]);

                // ✅ Add Footer
                $generatedOn = now()->format('F d, Y h:i A');
                $generatedBy = Auth::guard('admin')->user()->full_name ?? 'System';
                $footerRow = $lastRow + 3;

                $sheet->setCellValue("D{$footerRow}", "Generation Date:");
                $sheet->setCellValue("E{$footerRow}", $generatedOn);
                $sheet->setCellValue("D" . ($footerRow + 1), "Generated by:");
                $sheet->setCellValue("E" . ($footerRow + 1), $generatedBy);

                $sheet->getStyle("D{$footerRow}:E" . ($footerRow + 1))->applyFromArray([
                    'font' => ['italic' => true, 'name' => 'Arial', 'size' => 11],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
                ]);

                // ✅ Add Logo Image
                $drawing = new Drawing();
                $drawing->setName('OREMPCO Logo');
                $drawing->setDescription('Company Logo');
                $drawing->setPath(public_path('images/orempcologo.png')); // ✅ Path to your logo image
                $drawing->setHeight(80); // Adjust height
                $drawing->setCoordinates('A1'); // Place logo at A1
                $drawing->setOffsetX(10); // Optional: Adjust X offset
                $drawing->setWorksheet($sheet);
            },
        ];
    }
}
