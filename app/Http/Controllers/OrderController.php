<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    protected $orderService;
    public function __construct(OrderService $os)
    {
        $this->orderService = $os;
    }

    function truncate_number($number, $precision = 2)
    {

        // Zero causes issues, and no need to truncate
        if (0 == (int)$number) {
            return $number;
        }

        // Are we negative?
        $negative = $number / abs($number);

        // Cast the number to a positive to solve rounding
        $number = abs($number);

        // Calculate precision number for dividing / multiplying
        $precision = pow(10, $precision);

        // Run the math, re-applying the negative value to ensure
        // returns correctly negative / positive
        return floor($number * $precision) / $precision * $negative;
    }
    /**
     * 1.0 Start
     * 2.0 Input:  amount ,  currency , user id , points (if there is)
     * 6.0 calculate the total_payable_amount(every 1 point equivalent to USD$0.01.) , so total_payable_amount =  amount - (how many points * usd0.01)
     * 7.0 Key in Points when customers order is completed
     * 8.0 Calculate the points , based on the total_payable_amount
     */
    public function createOrder(OrderRequest $request)
    {
        $result = $this->orderService->service($request);
        return response()->json([
            'result' => $result
        ]);
    }
}
