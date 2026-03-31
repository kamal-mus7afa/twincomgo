<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ItemsExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    protected $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function array(): array
    {
        return $this->items->map(function ($item, $index) {
            return [
                $index + 1,
                $item['no'] ?? '-',
                $item['name'],
                $item['price'] ?? 0,
                $item['availableToSell'] ?? 0,
                preg_replace(
                    '/^[\d.,]+\s*(?=PCS\b)/i',
                    '',
                    trim(str_replace(['[', ']'], '', $item['availableToSellInAllUnit'] ?? '-'))
                ),
            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode',
            'Nama Produk',
            'Harga (Rp)',
            'Stok',
            'Satuan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            4 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                // ===============================
                // HEADER TITLE
                // ===============================
                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'DAFTAR PRODUK - TWINCOMGO');

                $sheet->mergeCells('A2:F2');
                $sheet->setCellValue('A2', 'Dicetak: ' . now()->format('d M Y H:i'));

                // ===============================
                // HEADER STYLE
                // ===============================
                $sheet->getStyle('A1:F1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => '136B35'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                $sheet->getStyle('A2:F2')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10,
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                // ===============================
                // TABLE HEADER (row 4)
                // ===============================
                $sheet->getStyle('A5:F5')->applyFromArray([
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '136B35'],
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                // ===============================
                // BORDER ALL TABLE
                // ===============================
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A5:F{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                        ],
                    ],
                ]);

                // ===============================
                // FORMAT RUPIAH
                // ===============================
                $sheet->getStyle("D6:D{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                // ===============================
                // ALIGNMENT
                // ===============================
                $sheet->getStyle("A5:F{$lastRow}")
                    ->getAlignment()
                    ->setVertical('center');

                $sheet->getStyle("A4:A{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal('center');

                $sheet->getStyle("D5:F{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal('center');
            },
        ];
    }
}
