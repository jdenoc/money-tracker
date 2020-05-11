<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StatsController extends Controller {

    public function display(){
        return view('stats');
    }

}