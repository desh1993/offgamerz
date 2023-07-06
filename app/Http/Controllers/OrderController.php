<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * 1.0 Start
     * 2.0 Input:  amount ,  currency , user id , points
     * 3.0 Check if currency exist in the table
     * 4.0 If No, call the free currency api and get the conversion rate
     * 5.0 if Yes, no need to call the api , just get it from the db
     * 6.0 calculate the total_payable_amount(every 1 point equivalent to USD$0.01.) , so total_payable_amount =  amount - (how many points * usd0.01)
     * 7.0 Key in Points when customers order is completed
     * 8.0 Calculate the points , based on the total_payable_amount
     */
    public function createOrder()
    {
    }
}
