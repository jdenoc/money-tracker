<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatsController extends Controller {

    public function __invoke(Request $request) {
        return view('stats');
    }

}
