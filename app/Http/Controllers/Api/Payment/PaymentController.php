<?php

namespace App\Http\Controllers\Api\Payment;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function stripeCheckout(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['error' => 'User ID is required.'], 400);
        }

        $request->validate([
            'email' => 'required|email',
            'products' => 'required|array', // Modify to accept an array of products
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.price' => 'required|numeric|min:0.01', // Price of each product
        ]);

        $stripe = new StripeClient(env('STRIPE_SECRET'));

        $lineItems = [];
        $totalPrice = 0;

        foreach ($request->products as $product) {
            $lineItems[] = [
                'price_data' => [
                    'product_data' => [
                        'name' => $product['product_id'],
                    ],
                    'unit_amount' => 100 * $product['price'],
                    'currency' => 'EGP',
                ],
                'quantity' => 1,
            ];
            $totalPrice += $product['price'];
        }

        $response = $stripe->checkout->sessions->create([
            'success_url' => route('stripe.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url('https://release--chatbroker.netlify.app/'),
            'customer_email' => $request->email,
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'allow_promotion_codes' => false,
            'metadata' => [
                'user_id' => $userId,
            ],
        ]);

        // Retrieve session ID from the Stripe API response
        $session_id = $response->id;

        // Create order using the retrieved session ID
        $order = new Order();
        $order->user_id = $userId;
        $order->total_amount = $totalPrice;
        $order->status = 'pending';
        $order->session_id = $session_id; // Assign session ID from the Stripe response
        $order->save();

        foreach ($request->products as $product) {
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $product['product_id'];
            $orderItem->price = $product['price'];
            $orderItem->model = $product['model'];
            $orderItem->save();
        }


        // Create payment record
        $payment = new Payment();
        $payment->order_id = $order->id;
        $payment->payment_method = 'card'; // Assuming payment method is always card
        $payment->total_price = $totalPrice;
        $payment->currency = 'EGP';
        $payment->status = 'pending'; // Set payment status to pending initially
        $payment->save();

        // Return the checkout URL and session ID to the frontend
        return response()->json(['url' => $response->url, 'session_id' => $session_id]);
    }

    public function stripeCheckoutSuccess(Request $request)
    {
        try {
            // Retrieve the session ID from the request query parameters
            $session_id = $request->query('session_id');

            // Ensure session_id is provided
            if (!$session_id) {
                return response()->json(['error' => 'Invalid session ID.'], 400);
            }

            // Retrieve order based on the session ID
            $order = Order::where('session_id', $session_id)->firstOrFail();

            // Update order status to 'completed'
            $order->status = 'completed';
            $order->save();

            // Return success response
            return view('payment.success');
        } catch (\Exception $e) {
            Log::error('Stripe Checkout Success Error: ' . $e->getMessage());
            return response()->json(['error' => 'Payment processing failed.'], 500);
        }
    }
}
