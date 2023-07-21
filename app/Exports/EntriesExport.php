<?php

namespace App\Exports;

use App\Models\Entry;
use App\Traits\ExportsHelper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EntriesExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithCustomCsvSettings {
    use ExportsHelper;

    private $filterData;

    public function __construct($filterData) {
        $this->filterData = $filterData;
    }

    public function headings(): array {
        return $this->getCsvHeaderLine();
    }

    public function map($row): array {
        $income_value = $row->expense ? '' : $row->entry_value;
        $expense_value = $row->expense ? $row->entry_value : '';
        $has_attachment = $row->has_attachments();
        $is_transfer = !is_null($row->transfer_entry_id);
        $tags = $row->has_tags() ? $row->get_tag_ids() : [];

        return [
            $row->id,
            $row->entry_date,
            $row->memo,
            $income_value,
            $expense_value,
            $row->account_type_id,
            $has_attachment,
            $is_transfer,
            $tags,
        ];
    }

    public function columnFormats(): array {
        return [
            'B' => NumberFormat::FORMAT_DATE_YYYYMMDD,  // entry date
            'D' => NumberFormat::FORMAT_NUMBER_00,      // income
            'E' => NumberFormat::FORMAT_NUMBER_00,      // expense
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection() {
        $total_count = Entry::count_collection_of_entries($this->filterData);
        return Entry::get_collection_of_entries($this->filterData, $total_count);
    }

    public function getCsvSettings(): array {
        return [
            'delimiter' => ',',
            'line_ending'=>"\n"
        ];
    }

}
