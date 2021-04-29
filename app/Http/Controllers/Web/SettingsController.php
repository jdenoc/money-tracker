<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class SettingsController extends Controller {

    public function display(){
        return view('settings');
    }

}
