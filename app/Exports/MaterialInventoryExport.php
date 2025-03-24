<?php

namespace App\Exports;

use App\Models\MaterialInventory;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Auth;

class MaterialInventoryExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithEvents
{
    protected $groupedMaterials;

    public function __construct($groupedMaterials)
    {
        $this->groupedMaterials = $groupedMaterials;
    }

    /**
     * Prepare and sort data before exporting.
     */
    public function array(): array
    {
        $exportData = [];

        foreach ($this->groupedMaterials as $categoryName => $materials) {
            // Add category title
            $exportData[] = ["Category: {$categoryName}"];

            // Add material data
            foreach ($materials as $material) {
                $exportData[] = [
                    $material->material_name,
                    $material->total_stocks . ' ' . $material->unit,
                    '₱' . number_format($material->cost_per_unit, 2),
                    $material->low_stock_limit ?? 'N/A',
                    $this->getRemarks($material),
                ];
            }

            // Add an empty row after each category
            $exportData[] = [''];
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
            ['MATERIAL INVENTORY REPORT'],
            ['DATE OF INVENTORY: ' . now()->format('m/d/Y')],
            [''],
            ['Material Name', 'Total Stocks', 'Price Per Unit', 'Low Stock Limit', 'Remarks'],
        ];
    }

    /**
     * Set the worksheet title.
     */
    public function title(): string
    {
        return 'Material Inventory';
    }

    /**
     * Apply base styles to the sheet.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['italic' => true, 'size' => 12, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            3 => ['font' => ['size' => 12, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            4 => ['font' => ['size' => 12, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            6 => ['font' => ['bold' => true, 'size' => 16, 'name' => 'Arial'], 'alignment' => ['horizontal' => 'center']],
            9 => [
                'font' => ['bold' => true, 'name' => 'Arial'],
                'alignment' => ['horizontal' => 'center'],
                'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK]],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
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

                // Merge header cells
                foreach (range(1, 6) as $row) {
                    $sheet->mergeCells("A{$row}:E{$row}");
                }

                // Merge cells for DATE OF INVENTORY
                $sheet->mergeCells("A7:E7");

                // Freeze header
                $sheet->freezePane('A10');

                // Adjust column widths
                $sheet->getColumnDimension('A')->setWidth(30); // Material Name
                $sheet->getColumnDimension('B')->setWidth(20); // Total Stocks
                $sheet->getColumnDimension('C')->setWidth(20); // Price Per Unit
                $sheet->getColumnDimension('D')->setWidth(20); // Low Stock Limit
                $sheet->getColumnDimension('E')->setWidth(20); // Remarks

                // Apply borders and styles
                $lastRow = $sheet->getHighestRow();
                $cellRange = 'A9:E' . $lastRow;

                $sheet->getStyle($cellRange)->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 11],
                    'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'indent' => 2,
                        'wrapText' => true,
                    ],
                ]);

                // Footer
                $generatedOn = now()->format('F d, Y h:i A');
                $generatedBy = Auth::guard('admin')->user()->full_name ?? 'System';
                $footerRow = $lastRow + 3;

                $sheet->setCellValue("B{$footerRow}", "Generation Date:");
                $sheet->setCellValue("C{$footerRow}", $generatedOn);
                $sheet->setCellValue("B" . ($footerRow + 1), "Generated by:");
                $sheet->setCellValue("C" . ($footerRow + 1), $generatedBy);

                $sheet->getStyle("B{$footerRow}:C" . ($footerRow + 1))->applyFromArray([
                    'font' => ['italic' => true, 'name' => 'Arial', 'size' => 11],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
                ]);

                // Logo
                $drawing = new Drawing();
                $drawing->setName('OREMPCO Logo');
                $drawing->setDescription('Company Logo');
                $drawing->setPath(public_path('images/orempcologo.png')); // Ensure the logo exists in the public/images directory
                $drawing->setHeight(80);
                $drawing->setCoordinates('A1');
                $drawing->setOffsetX(10);
                $drawing->setWorksheet($sheet);
            },
        ];
    }

    /**
     * Get remarks based on stock status.
     */
    private function getRemarks($material)
    {
        if ($material->total_stocks == 0) {
            return 'Out of Stock';
        } elseif ($material->low_stock_limit && $material->total_stocks <= $material->low_stock_limit) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }
}