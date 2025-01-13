<?php

namespace Skeleton\Store\Factory;

use Stripe\StripeClient;

class Stripe
{
    public $stripe;
    public function __construct()
    {
        $this->stripe = new StripeClient(config('skeletonStore.payment_gateway.stripe.secret'));
    }

    public function createSession($user, $lineItems, $autoRenew = true, $successUrl = null, $cancelUrl = null, $allowPromotionCodes = true)
    {
        $customer = $user->stripe_id;
        if (empty($customer)) {
            $customer = $this->stripe->customers->create([
                'email' => $user->email,
            ])->id;
            $user->stripe_id = $customer;
            $user->save();
        }

        return $this->stripe->checkout->sessions->create([
            'customer' => $customer,
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => $autoRenew ? 'subscription' : 'payment',
            'success_url' => $successUrl ?? env('APP_URL') . '/success',
            'cancel_url' => $cancelUrl ?? env('APP_URL') . '/cancel',
            'allow_promotion_codes' => $allowPromotionCodes
        ]);
    }

    public function createPrice($price, $currency = 'gbp', $productName = 'Gold Plan', $interval = 'month', $intervalCount = 1)
    {
        // Convert price to cents
        $price = $price * 100;

        return $this->stripe->prices->create([
            'currency' => $currency,
            'unit_amount' => $price,
            'recurring' => [
                'interval'       => $interval,
                'interval_count' => $intervalCount
            ],
            'product_data' => ['name' => $productName],
        ]);
    }

    public function createProduct($name, $imageUrl = null, $isSubscription = false, $description = null)
    {
        $productData = [
            'name' => $name,
        ];

        if (!empty($imageUrl)) {
            $productData['images'] = [$imageUrl]; // Add images key only if $imageUrl is not empty
        }

        if ($isSubscription) {
            $productData['type'] = 'service';
        }
        if ($description) {
            $productData['description'] = $description;
        }

        return $this->stripe->products->create($productData);
    }


    public function createOneTimePrice($price, $productId, $currency = 'gbp')
    {
        // Convert price to cents
        $priceInCents = $price * 100;

        // Create the price for a one-time product
        return $this->stripe->prices->create([
            'currency' => $currency,
            'unit_amount' => $priceInCents,
            'product' => $productId, // Use the provided product ID
        ]);
    }


    public function cancelSubscription($subscriptionId)
    {
        // Cancel the subscription immediately
        return $this->stripe->subscriptions->cancel($subscriptionId);
    }

    public function createBillingPortalSession($customerId, $returnUrl = null)
    {
        return $this->stripe->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => $returnUrl ?? env('APP_URL') . '/account',
        ]);
    }

    public function getSubscription($subscriptionId)
    {
        try {
            // Retrieve the subscription details from Stripe
            return $this->stripe->subscriptions->retrieve($subscriptionId);
        } catch (\Exception $e) {
            // Handle any errors (such as subscription not found)
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create an invoice and mark it as paid based on the session ID.
     *
     * @param string $sessionId
     * @return array
     */
    public function createInvoiceAndMarkAsPaid($sessionId, $order)
    {
        try {
            // Retrieve the session using the session ID
            $session = $this->stripe->checkout->sessions->retrieve($sessionId);

            // Create an invoice for the customer
            $invoice = $this->stripe->invoices->create([
                'customer' => $session->customer,
                'auto_advance' => false, // Set to false so we can finalize manually
            ]);

            // Create invoice items for each order item
            foreach ($order->orderItems as $item) {
                $this->stripe->invoiceItems->create([
                    'customer' => $session->customer,
                    'price' => $item->item->stripe_price_id,
                    'quantity' => $item->quantity, // Make sure to include the quantity
                    'invoice' => $invoice->id,
                ]);
            }

            // Finalize the invoice
            $finalizedInvoice = $this->stripe->invoices->finalizeInvoice($invoice->id);
            // Mark the invoice as paid without charging
            $paidInvoice = $this->stripe->invoices->update($finalizedInvoice->id, [
                'paid' => true
            ]);

            // Return the finalized invoice URL
            return [
                'invoice_url' => $paidInvoice->hosted_invoice_url,
                'invoice_id' => $paidInvoice->id,
                'amount_paid' => $paidInvoice->amount_paid,
            ];
        } catch (\Exception $e) {
            // Handle any errors that occur during the invoice creation process
            return ['error' => $e->getMessage()];
        }
    }
}
