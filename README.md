# Welcome to skeleton-store

Laravel Ecomece extenstion for skeleton admin package.

This a was autogenerate by skeleton-admin.


// Example how to subscribe the user
event(new UserSubscribedToPlan($user, $plan));
event(new UserUnsubscribedToPlan($user, $plan));


// Example create payment

$user = User::find(3);
$plan = Plan::find(2);
// CancelSubscription::dispatch($user, $plan);
$payment = [
    'user_id' => $user->id,
    'amount' => 5,
    'payment_method' => PaymentMethod::stripe->value,
    'status' => PaymentStatus::processing->value,
    'transaction_id' => 'txn_1234567890',
];
// event(new UserSubscribedToPlan($user, $plan, $payment, true));
ProcessSubscription::dispatch($user, $plan, $payment, true);

// Assuming you have a subscription instance
$subscription = Subscription::find(1); // Replace with the actual subscription ID

// Creating a new payment associated with the subscription
$payment = new Payment([
    'user_id' => $subscription->user_id,
    'amount' => 99.99, // Payment amount
    'payment_method' => PaymentMethod::stripe->value, // Replace with the actual payment method
    'status' => PaymentStatus::processing->value, // Replace with the actual status
    'transaction_id' => 'txn_1234567890', // Optional transaction ID
]);

// Save the payment using the polymorphic relationship
$subscription->payments()->save($payment);
