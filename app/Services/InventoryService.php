<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
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

            if($data[5] == 'Buy'){
                $rawData['purchaseList'][] = $temp;
            }else{
                $rawData['soldList'][] = $temp;
            }
        }

        Log::info('Inventory Service - Format Data :', $rawData);

        return $rawData;
    }

    public function updateInventory(array $inventory) : array
    {
        $response = [
            'status' => false,
        ];

        $validatedProductIds = array();

        $purchasedItems = $inventory['purchaseList'] ?? [];
        $soldItems      = $inventory['soldList'] ?? [];

        if( !empty($purchasedItems) ){
            foreach( $purchasedItems as $key => $purchaseItem ){
                $validatedProduct = $this->validateProduct($purchaseItem, 'purchase');
                if( $validatedProduct['status'] ){
                    $product = $validatedProduct['product'];
                    $validatedProductIds[] = $product->id;
                }
            }
        }

        if( !empty($soldItems) ){
            foreach( $soldItems as $key => $soldItem ){
                $validatedProduct = $this->validateProduct($purchaseItem, 'sold');
                if( $validatedProduct['status'] ){
                    $product = $validatedProduct['product'];
                    $validatedProductIds[] = $product->id;
                }
            }
        }

        Log::info('Inventory Service - Update Inventory Response : ', $response);

        // get latest record of all updated products
        $products = Product::whereIn('id', array_unique($validatedProductIds))->get();

        $response['status']    = true;
        $response['message']   = 'Data validated successfully.';
        $response['inventory'] = $products; 

        Log::info('Inventory Service - Update Inventory Response : ', $response);

        return $response;
    }

    public function validateProduct(array $item, string $transactionType) : array
    {
        $response = [
            'status' => false
        ];

        Log::info('Inventory Service - Validate Product Param : ', array('item'=>$item, 'transactionType'=>$transactionType));

        // purchase means adding the quantity
        if( $transactionType == 'purchase' ){
            
            $product = Product::firstOrCreate(
                ['serial_number' => $item['ProductID']],
                ['serial_number' => $item['ProductID'], 'unit_price' => 1.00, 'quantity' => 1]
            ); 

            // add quantity to existing record
            if ( !$product->wasRecentlyCreated ) {
                $product->quantity += 1;
                $product->save();
            }

            $transactionNo = Carbon::today()->toDateString() . '-' . $product->serial_number . '-' . Carbon::now()->micro;
            $transactionAmount = $product->unit_price * $product->quantity;

            // add transaction record
            $transaction = Transaction::create([
                'transaction_no' => $transactionNo,
                'transaction_type' => $transactionType,
                'transaction_amount' => $transactionAmount,
                'transaction_status' => 1,
            ]);

            $response['status'] = true;

        }else if( $transactionType == 'sold' ){
            $product = Product::where('serial_number', $item['ProductID'])->first();

            if ($product && $product->exists() && $product->quantity > 0) {
                $product->quantity -= 1;
                $product->save();

                $transactionNo = Carbon::today()->toDateString() . '-' . $product->serial_number . '-' . Carbon::now()->micro;
                $transactionAmount = $product->unit_price * $product->quantity;

                // add transaction record
                $transaction = Transaction::create([
                    'transaction_no' => $transactionNo,
                    'transaction_type' => $transactionType,
                    'transaction_amount' => $transactionAmount,
                    'transaction_status' => 1
                ]);

                $response['status'] = true;

            }else{                
                $transactionNo = Carbon::today()->toDateString() . '-' . $item['ProductID'] . '-' . Carbon::now()->micro;

                // add transaction record
                $transaction = Transaction::create([
                    'transaction_no' => $transactionNo,
                    'transaction_type' => $transactionType,
                    'transaction_amount' => 0.00,
                    'transaction_status' => 0
                ]);
            }
        }

        $response['product'] = ( $response['status'] ) ? $product : '';

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