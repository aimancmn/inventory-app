<?php

namespace App\Http\Controllers;

use App\Services\InventoryService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller
{
    private $inventoryService;

    public function __construct()
    {
        $this->inventoryService = new InventoryService();
    }

    /*****
     * API : Upload
     * Param : $_FILE
     * Response : JSON
     * Description : Received uploaded file and extract the data to update on the inventory info.
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', //limit to 5MB
        ]);

        if( $validator->fails()){
            return response()->json(['status' => false, 'message' => $validator->errors()], 400);
        }

        $file = $request->file('file');
        $data = array();
        $response = [
            'status' => false
        ];
        
        // store to temp instead of model/obj
        $path = $file->store('temp');

        // fetch data and convert to array
        $data = Excel::toCollection(null, $path)
            ->collapse() // Flatten the collection
            ->toArray();

        // log param
        Log::info('Upload - Param : ', $data);

        // --- validation start ---
        if( empty($data) ){
            return response()->json(['status' => true, 'message' => 'File was empty.']);
        }
        
        $validateDataResponse = $this->inventoryService->validateData($data);
        if( empty($validateDataResponse) || !$validateDataResponse['status'] ){
            $response['message'] = $validateDataResponse['message'];

            return response()->json($response, 400);
        }
        
        $inventory = $validateDataResponse['inventory'] ?? [];
        if( empty($inventory) ){
            $response['message'] = 'Data was corrupted';

            return response()->json($response, 400);
        }

        $updateInventoryResponse = $this->inventoryService->updateInventory($inventory);
        if( empty($updateInventoryResponse) || !$updateInventoryResponse['status'] ){
            $response['message'] = $updateInventoryResponse['message'];

            return response()->json($response, 400);
        }

        // set success response
        $response['status']    = true;
        $response['message']   = 'Inventory updated successfully';
        $response['inventory'] = $updateInventoryResponse['inventory'];

        return response()->json($response);
    }
}
