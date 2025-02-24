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
use Maatwebsite\Excel\Events\AfterSheet;

class CustomersExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithEvents, ShouldAutoSize
{
    /**
     * Prepare and sort data before exporting.
     */
    public function array(): array
    {
        // Fetch all customers and group them by 'type' (Department, Employee, Outside)
        $customers = Customer::all();

        // Define type order
        $typeOrder = ['Department', 'Employee', 'Outside'];

        $exportData = [];

        foreach ($typeOrder as $type) {
            // Filter and sort customers by full_name within the current type
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

            // Add blank row after each type if there were any customers
            if ($filtered->isNotEmpty()) {
                $exportData[] = ['', '', '', '', ''];
            }
        }

        // Remove last blank row if present
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
            [''], // Blank row for spacing
            ['Customer List'], // Title row
            ['EMPLOYEE ID', 'FULL NAME', 'DEPARTMENT', 'TYPE', 'MEMBER/NON-MEMBER'], // Headers
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
            // Style the header row
            7 => ['font' => ['bold' => true, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center'], 'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK]]],
        ];
    }

    /**
     * Apply borders, font, freeze header, auto-sizing, and padding.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge cells for each of the header lines
                foreach (range(1, 6) as $row) {
                    $sheet->mergeCells("A{$row}:E{$row}");
                }

                // Freeze the header row (after Customer List title and table headers)
                $sheet->freezePane('A8'); // Data scrolls, headers stay

                // Apply border to all data cells
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();

                $cellRange = 'A7:' . $lastColumn . $lastRow;

                // Apply borders, Arial 11 font, and padding
                $sheet->getStyle($cellRange)->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'size' => 11,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => '000000'], // Black borders
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'indent' => 1, // Add padding inside the cell
                    ],
                ]);
            },
        ];
    }
}
