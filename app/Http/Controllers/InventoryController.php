<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller
{
    //
    public function upload(Request $request)
    {
        $file = $request->file('file');

        $data = array();
        
        // Validate the file if needed

        // Process the data as needed
        $path = $file->store('temp');
        $data = Excel::toCollection(null, $path)
            ->collapse() // Flatten the collection
            ->toArray();

        return response()->json($data);
    }
}
