<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller {

    public function display(){
        return view('home');
    }

    public function laravelWelcome(){
        return view('laravel-welcome');
    }

}