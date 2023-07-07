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
    public function createOrder(OrderRequest $request)
    {
        $result = $this->orderService->service($request);
        return response()->json([
            'status' => 'successful',
            'message' => 'order created'
        ]);
    }
}
