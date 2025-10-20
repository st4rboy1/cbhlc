<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function pending()
    {
        return Inertia::render('admin/documents/pending');
    }
}
