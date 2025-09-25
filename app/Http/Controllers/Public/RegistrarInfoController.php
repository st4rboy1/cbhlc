<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class RegistrarInfoController extends Controller
{
    /**
     * Display the registrar information page.
     */
    public function index()
    {
        return Inertia::render('public/registrar');
    }
}
