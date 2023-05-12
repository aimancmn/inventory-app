<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Calculation\Logical\Boolean;

class InventoryService
{
    public function __construct()
    {
        
    }

    public function validateData(array $requestData) : array
    {
        $response = [
            'status' => false
        ];

        // header need to be array
        if( !is_array($requestData[0]) ){
            $response['message'] = 'Invalid header. Please use provided template.';

            return $response;
        }

        // format data
        $rawData = $this->formatData($requestData);
        if( empty($rawData) ){
            $response['message'] = 'Data corrupted or file has no data. Please reupload.';

            return $response;
        }
        
        $response['status']    = true;
        $response['message']   = 'Data validated successfully.';
        $response['inventory'] = $rawData; 
        
        Log::info('Inventory Service - Validate Data Response : ', $response);
        
        return $response;
    }

    public function updateInventory(array $inventory) : array
    {
        $response = [
            'status' => false,
        ];

        $updatedInventory = array();

        foreach($inventory as $key => $item){
            
            $isValidItem = $this->validateProduct($item['ProductID'], $item['Status']);
            if( !$isValidItem['status'] ){
                continue;
            }

            $updatedInventory[] = $isValidItem['item'];
        }

        $response['status']    = true;
        $response['message']   = 'Data validated successfully.';
        $response['inventory'] = $updatedInventory; 

        Log::info('Inventory Service - Update Inventory Response : ', $response);

        return $response;
    }

    public function formatData(array $requestData) : array
    {
        $rawData = array();

        $headerArr = $requestData[0];

        unset($requestData[0]);

        foreach($requestData as $key => $data){
            $temp[str_replace(' ', '', $headerArr[0])] = $data[0];
            $temp[str_replace(' ', '', $headerArr[1])] = $data[1];
            $temp[str_replace(' ', '', $headerArr[2])] = $data[2];
            $temp[str_replace(' ', '', $headerArr[3])] = $data[3];
            $temp[str_replace(' ', '', $headerArr[4])] = $data[4];
            $temp[str_replace(' ', '', $headerArr[5])] = $data[5];

            $rawData[] = $temp;
        }

        return $rawData;
    }

    public function validateProduct(string $productId, string $transactionType) : array
    {
        $response = [
            'status' => false
        ];
        
        $product = Product::where('serial_number', $productId)->first();;
        if( !$product->exists ){

            if( $transactionType == 'Sold' ){
                return $response;
            }else{
                $product->serial_number = $productId;
                $product->unit_price    = 0.00;
                $product->quantity      = $product->quantity + 1;
                $product->save();

                return true;
            }
        }

        if( $transactionType == 'Sold' ){
            if( $product->quantity == 0 ){
                return $response;
            }

            $product->quantity = $product->quantity - 1;
            $product->save();
        }else{
            $product->quantity = $product->quantity + 1;
            $product->save();   
        }

        $response['item'] = $product;

        return $response;
    }

    public function validateBrand() : array
    {
        $response = [
            'status' => false
        ];

        return $response;
    }

    public function validateCategory() : array
    {
        $response = [
            'status' => false
        ];

        return $response;
    }

    public function validateSeries() : array
    {
        $response = [
            'status' => false
        ];

        return $response;
    }

    public function validateSpecification() : array
    {
        $response = [
            'status' => false
        ];

        return $response;
    }
}