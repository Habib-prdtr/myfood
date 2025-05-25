<?php

namespace App\Http\Controllers;

use App\Models\Barcode;
use Illuminate\Http\Request;

class QRController extends Controller
{
    public function storeResult(Request $request)
    {
        $request->validate([
            'table_number' => 'required|string',
        ]);

        session(['table_number' => $request->table_number]);

        return response()->json(['status' => 'success']);
    }

    public function checkCode($code)
    {
        if (preg_match('/^[a-zA-Z]\d{4}$/', $code)) {
            $exists = Barcode::where('table_number', $code)->exists();

            if ($exists) {
                session(['table_number' => $code]);
                return view('home', [
                    'message' => 'Welcome! Code verified successfully.',
                ]);
            } else {
                return view('invalid', [
                    'message' => 'Code not found in the database.',
                ]);
            }
        }
    }
}