<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProgressVisitController extends Controller
{

    function index(Request $request)
    {
        if (checkUserSession($request, 'progress_visit', 'show') == false) {
            return view('exceptions.forbidden', ["pageTitle" => "Forbidden"]);
        }
    }
}
