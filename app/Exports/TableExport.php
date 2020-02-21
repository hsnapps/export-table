<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Exception;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class TableExport implements FromCollection, WithHeadings
{
    protected $columnHeaders = [];
    protected $columns = [];
    protected $table;
    protected $schema;

    /**
     * Initialise
     * @param string $table
     * @param array|null $columnNames
     * @param array|string $columnHeaders
     * @return void
     * @throws InvalidArgumentException
     */
    function __construct($table, $columnHeaders = 'column_comment')
    {
        $this->schema = env('DB_DATABASE');
        $this->table = $table;
        $this->columns = $this->extractColumns();

        if ($columnHeaders == 'column_comment') {
            $this->columnHeaders = $this->generateHeadings();
        } elseif ($columnHeaders == 'column_name') {
            $this->columnHeaders = $this->extractColumns();
        } else {
            if (is_array($columnHeaders)) {
                $this->columnHeaders = $columnHeaders;
            }
        }
    }

    public function collection()
    {
        $data = DB::table($this->table)
            ->select($this->columns)
            ->get();

        return $data;
    }

    public function headings(): array
    {
        return $this->columnHeaders;
    }

    /**
     * Generates columns headers of the excel sheet
     * @return array
     */
    private function generateHeadings()
    {
        return \Illuminate\Support\Facades\DB::table('information_schema.COLUMNS')
            ->select('column_comment')
            ->where([
                ['table_name', '=', $this->table],
                ['table_schema', '=', $this->schema],
            ])
            ->get()
            ->map(function ($item) {
                return $item->column_comment;
            })
            ->toArray();
    }

    /**
     * Extract table columns
     * @return array
     */
    private function extractColumns()
    {
        return \Illuminate\Support\Facades\DB::table('information_schema.COLUMNS')
            ->select('column_name')
            ->where([
                ['table_name', '=', $this->table],
                ['table_schema', '=', $this->schema],
            ])
            ->get()
            ->map(function ($item) {
                return $item->column_name;
            })
            ->toArray();
    }
}
