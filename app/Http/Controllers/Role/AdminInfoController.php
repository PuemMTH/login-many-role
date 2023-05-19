<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\admin_info;

class AdminInfoController extends Controller
{
    public function index()
    {
        return response()->json(admin_info::all(), 200);
    }
}
