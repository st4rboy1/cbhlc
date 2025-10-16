<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SchoolInformation;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function index()
    {
        $information = SchoolInformation::getGrouped();

        return Inertia::render('ContactUs', [
            'schoolInformation' => $information,
        ]);
    }
}
