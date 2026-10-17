<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use Illuminate\Http\Request;

class CompoundingController extends Controller
{
    public function index()
    {
        $compounding_logs = [];
        $formulas = [];
        $drugs = [];

        try {
            $drugs = Drug::all();
        } catch (\Exception $e) {
            $drugs = collect();
        }

        return view('compounding', compact('compounding_logs', 'formulas', 'drugs'));
    }
}
