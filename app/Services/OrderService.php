<?php

namespace App\Services;

use App\Http\Requests\OrderRequest;
use App\Models\Currency;
use App\Models\Customerpoints;
use App\Models\Order;
use App\Models\Orderhistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class OrderService
{
    public function service(OrderRequest $request)
    {
        $points = $request->input('points') ? $request->input('points') : 0;
        $amount = $request->input('amount');
        $currency = $request->input('currency');
        $userId = $request->input('user_id');
        $initial_points = null;

        $created_order = $this->createOrder($points, $amount, $currency, $userId);

        $awarded_points = $this->calculatePoints($created_order->total_payable_amount, $currency);


        if ($points > 0 && $created_order) {
            $order_hist = $this->createOrderHistory(
                $userId,
                $created_order->id,
                $this->getInitialPoints($userId),
                $points * -1
            );
            $newpointbalance = $this->deductPoints($userId, $points);
            $initial_points = $newpointbalance;
        }

        //update or create customer points 
        if ($created_order->status_id === 3) {
            $order_hist = $this->createOrderHistory(
                $userId,
                $created_order->id,
                $initial_points,
                $awarded_points,
            );
            if ($order_hist) {
                $points_to_update = $order_hist->current_points;
                $this->updateOrCreateCustomerPoints($userId, $points_to_update);
            }
        }
    }

    protected function getInitialPoints($userId)
    {
        $obj = User::find($userId)->points()->first();
        return $obj ? $obj->points : null;
    }

    protected function createOrderHistory($userId, $orderId, $initial_points, $point_adjustment)
    {
        DB::beginTransaction();
        try {
            $current_points = $initial_points + $point_adjustment;
            //create the order history
            $order_history = Orderhistory::create([
                'user_id' => (int)$userId,
                'order_id' => $orderId,
                'initial_points' => $initial_points == null ? 0 : round($initial_points),
                'points_adjustment' => $point_adjustment === null ? 0 : round($point_adjustment),
                'current_points' =>  $current_points
            ]);
            DB::commit();
            return  $order_history;
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            // Handle the exception if necessary
            throw $e;
        }
    }

    protected function updateOrCreateCustomerPoints($userId, $points)
    {
        DB::beginTransaction();
        try {
            //code...
            $expiryDate = Carbon::now()->addYear();

            // $customerPoints = CustomerPoints::updateOrCreate(
            //     ['user_id' => $userId],
            //     ['points' => DB::raw("points + {$points}"), 'points_expiry' => $expiryDate]
            // );

            $customerPoints = CustomerPoints::updateOrCreate(
                ['user_id' => $userId],
                [
                    'points' => $points, 'points_expiry' => $expiryDate
                ]
            );

            DB::commit();
            return $customerPoints;
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            // Handle the exception if necessary
            throw $e;
        }
    }

    protected function deductPoints($customerId, $pointsToDeduct)
    {
        DB::beginTransaction();

        try {
            $customerPoints = Customerpoints::where('user_id', $customerId)->first();

            if ($customerPoints) {
                $currentPoints = $customerPoints->points;

                // Check if the customer has enough points to deduct
                if ($currentPoints >= $pointsToDeduct) {
                    $newPointsBalance = $currentPoints - $pointsToDeduct;

                    // Update the points balance in the model
                    $customerPoints->points = $newPointsBalance;
                    $customerPoints->save();

                    DB::commit();

                    return $newPointsBalance;
                    // Deduction successful
                    // Perform necessary actions
                } else {
                    DB::rollBack();
                }
            } else {
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the exception if necessary
            throw $e;
        }
    }


    public function createOrder($points, $amount, $currency, $userId)
    {
        DB::beginTransaction();
        try {
            //code...
            $total_payable_amount = $this->calculateTotalPayableAmount($points, $amount, $currency);
            //create the order
            $order = Order::create([
                'user_id' => (int)$userId,
                'status_id' => 3,
                'currency_id' => $this->getCurrencyId($currency),
                'amount' => $amount,
                'total_payable_amount' =>  $total_payable_amount
            ]);
            DB::commit();

            return $order;
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            // Handle the exception if necessary
            throw $e;
        }
    }

    protected function getCurrencyId($currency)
    {
        $currency = Currency::where([
            'currency_name' => $currency
        ])->first();
        if ($currency) {
            return $currency->id;
        }
        return null;
    }


    protected function calculateTotalPayableAmount($points = 0, $amount, $currency)
    {
        if ($points > 0) {
            $discount = $this->getDiscount($currency, $points);
            $total_payable_amount = floatval($amount) - $discount;
            return  $total_payable_amount;
        }
        return $amount;
    }

    /**
     * Every USD$1 sales amount will be rewarded with 1 point,
     * 1.0 start
     * 2.0 convert the user currency to usd first
     * 3.0 then * 1 to get the points
     * 4.0 end
     */
    public function calculatePoints($amount, $currency)
    {
        $currency = strtoupper(trim($currency));
        $rate = $this->getExchangeRate($currency);
        //convert amount to usd
        $amountInUsd = $amount * $rate;
        $points = round(($amountInUsd * 1));
        return $points;
    }

    /**
     * Every USD$1 sales amount will be rewarded with 1 point, if the sales amount is not USD,
     * convert to equivalent amount in USD for reward amount calculation.
     */
    protected function getExchangeRate($currency)
    {
        $currency = Currency::where([
            'currency_name' => $currency
        ])->first();
        $rate = 1 / ($currency->exchange_rate);
        return $rate;
    }

    //1 point equivalent to USD$0.01.
    protected function getDiscount($currency, $points)
    {
        $currency = Currency::where([
            'currency_name' => $currency
        ])->first();
        $totalUSD = $points * 0.01;
        $discount = $totalUSD * $currency->exchange_rate; //discount in their respective currency
        return round($discount, 2);
    }
}
