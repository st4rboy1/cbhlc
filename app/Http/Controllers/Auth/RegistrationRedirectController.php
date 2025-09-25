<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class RegistrationRedirectController extends Controller
{
    /**
     * Redirect registration attempts to home page.
     * Registration is disabled for this application.
     */
    public function redirect()
    {
        return redirect('/');
    }
}
