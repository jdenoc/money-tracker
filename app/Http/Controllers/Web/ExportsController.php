<?php

namespace App\Http\Controllers\Web;

use App\Exports\EntriesExport;
use App\Http\Controllers\Controller;
use App\Traits\ExportsHelper;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportsController extends Controller {

    use ExportsHelper;

    public function export(Request $request){
        $export_filename = $this->generateExportFilename();
        $post_body = $request->getContent();
        $filter_data = json_decode($post_body, true);

        return Excel::download(
            new EntriesExport($filter_data),
            $export_filename,
            \Maatwebsite\Excel\Excel::CSV,
            ['Content-Type' => 'text/csv',]
        );
    }

}
