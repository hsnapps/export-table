<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TableExport;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $schema = env('DB_DATABASE');
        $tables = DB::table('information_schema.TABLES')
            ->select('table_name')
            ->where([
                ['table_schema', '=', $schema],
            ])
            ->get()
            ->map(function ($item) {
                return $item->table_name;
            })
            ->toArray();

        return view('welcome', [
            'tables' => $tables
        ]);
    }

    public function export(Request $request)
    {
        $fileName = sprintf('%s.%s', $request->table, strtolower($request->format));

        return Excel::download(new TableExport($request->table, $request->columnHeaders), $fileName, $request->format);
    }
}
