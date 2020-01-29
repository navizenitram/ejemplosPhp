<?php
namespace Structure;
use Domain\CreditCard;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentMethod;

final class CreditCardStripe implements CreditCard
{

    private $intent;

    public function __construct(array $intent)
    {
        Stripe::setApiKey(Config::get('stripe.secret_key'));
        $this->intent = $intent;
    }

    public function add(array $customerData): array
    {

        $customer = Customer::create($customerData);

        Customer::createTaxId(
            $customer->id,
            [
                'type' => 'eu_vat',
                'value' => $customerData['cif'],
            ]
        );


        $payment_method = PaymentMethod::retrieve($this->intent['payment_method']);
        $payment_method->attach(['customer' => $customer->id]);

        //TODO: Convert to ValueObject
       $cardInfo =  [
            'payment_method'=>$this->intent['payment_method'],
            'com_id'=>'',
            'brand'=>$payment_method->card->brand,
            'exp_year'=>$payment_method->card->exp_year,
            'exp_month'=>$payment_method->card->exp_month,
            'last4'=>$payment_method->card->last4,
            'three_d_secure'=> '',
            'source'=>'admin',
            'customer'=>$customer->id,
        ];

       return $cardInfo;

    }

}