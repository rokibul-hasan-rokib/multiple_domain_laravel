<?php

namespace App\Http\Controllers\Domain3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    final public function index()
    {
         echo "Welcome to Domain 3!";
    }
}
