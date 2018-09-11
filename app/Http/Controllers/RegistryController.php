<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RegistryController extends Controller {

    public function proxy(Request $r) {
        Proxy::updateOrCreate();
    }

    public function client(Request $r) {

    }
}