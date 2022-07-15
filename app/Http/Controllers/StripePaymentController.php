<?php

namespace App\Http\Controllers;


use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Transaction;
use App\Models\UserCoupon;
use App\Models\Utility;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
use Stripe;
use Illuminate\Support\Facades\Validator;

class StripePaymentController extends Controller
{
    public $settings;


    public function index()
    {
        $objUser = \Auth::user();
        if($objUser->type == 'super admin')
        {
            $orders = Order::select(
                [
                    'orders.*',
                    'users.name as user_name',
                ]
            )->join('users', 'orders.user_id', '=', 'users.id')->orderBy('orders.created_at', 'DESC')->get();
        }
        else
        {
            $orders = Order::select(
                [
                    'orders.*',
                    'users.name as user_name',
                ]
            )->join('users', 'orders.user_id', '=', 'users.id')->orderBy('orders.created_at', 'DESC')->where('users.id', '=', $objUser->id)->get();
        }

        return view('order.index', compact('orders'));
    }


    public function stripe($code)
    {
        $admin_payment_setting = Utility::getAdminPaymentSetting();
        $plan_id               = \Illuminate\Support\Facades\Crypt::decrypt($code);
        $plan                  = Plan::find($plan_id);
        if($plan)
        {
            return view('plan/stripe', compact('plan', 'admin_payment_setting'));
        }
        else
        {
            return redirect()->back()->with('error', __('Plan is deleted.'));
        }
    }


    public function stripePost(Request $request)
    {

        $admin_payment_setting = Utility::getAdminPaymentSetting();
        $objUser               = \Auth::user();
        $planID                = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan                  = Plan::find($planID);

        if($plan)
        {
            try
            {
                $price = $plan->price;
                if(!empty($request->coupon))
                {
                    $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                    if(!empty($coupons))
                    {
                        $usedCoupun     = $coupons->used_coupon();
                        $discount_value = ($plan->price / 100) * $coupons->discount;
                        $price          = $plan->price - $discount_value;

                        if($coupons->limit == $usedCoupun)
                        {
                            return redirect()->back()->with('error', __('This coupon code has expired.'));
                        }
                    }
                    else
                    {
                        return redirect()->back()->with('error', __('This coupon code is invalid or has expired.'));
                    }
                }

                $orderID = strtoupper(str_replace('.', '', uniqid('', true)));

                if($price > 0.0)
                {
                    Stripe\Stripe::setApiKey($admin_payment_setting['stripe_secret']);
                    $data = Stripe\Charge::create(
                        [
                            "amount" => 100 * $price,
                            "currency" => env('CURRENCY'),
                            "source" => $request->stripeToken,
                            "description" => " Plan - " . $plan->name,
                            "metadata" => ["order_id" => $orderID],
                        ]
                    );
                }
                else
                {
                    $data['amount_refunded'] = 0;
                    $data['failure_code']    = '';
                    $data['paid']            = 1;
                    $data['captured']        = 1;
                    $data['status']          = 'succeeded';
                }


                if($data['amount_refunded'] == 0 && empty($data['failure_code']) && $data['paid'] == 1 && $data['captured'] == 1)
                {

                    Order::create(
                        [
                            'order_id' => $orderID,
                            'name' => $request->name,
                            'card_number' => isset($data['payment_method_details']['card']['last4']) ? $data['payment_method_details']['card']['last4'] : '',
                            'card_exp_month' => isset($data['payment_method_details']['card']['exp_month']) ? $data['payment_method_details']['card']['exp_month'] : '',
                            'card_exp_year' => isset($data['payment_method_details']['card']['exp_year']) ? $data['payment_method_details']['card']['exp_year'] : '',
                            'plan_name' => $plan->name,
                            'plan_id' => $plan->id,
                            'price' => $price,
                            'price_currency' => env('CURRENCY'),
                            'txn_id' => isset($data['balance_transaction']) ? $data['balance_transaction'] : '',
                            'payment_type' => __('STRIPE'),
                            'payment_status' => isset($data['status']) ? $data['status'] : 'succeeded',
                            'receipt' => isset($data['receipt_url']) ? $data['receipt_url'] : 'free coupon',
                            'user_id' => $objUser->id,
                        ]
                    );

                    if(!empty($request->coupon))
                    {
                        $userCoupon         = new UserCoupon();
                        $userCoupon->user   = $objUser->id;
                        $userCoupon->coupon = $coupons->id;
                        $userCoupon->order  = $orderID;
                        $userCoupon->save();

                        $usedCoupun = $coupons->used_coupon();
                        if($coupons->limit <= $usedCoupun)
                        {
                            $coupons->is_active = 0;
                            $coupons->save();
                        }

                    }
                    if($data['status'] == 'succeeded')
                    {
                        $assignPlan = $objUser->assignPlan($plan->id);
                        if($assignPlan['is_success'])
                        {
                            return redirect()->route('plan.index')->with('success', __('Plan successfully activated.'));
                        }
                        else
                        {
                            return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
                        }
                    }
                    else
                    {
                        return redirect()->route('plan.index')->with('error', __('Your payment has failed.'));
                    }
                }
                else
                {
                    return redirect()->route('plan.index')->with('error', __('Transaction has been failed.'));
                }
            }
            catch(\Exception $e)
            {
                return redirect()->route('plan.index')->with('error', __($e->getMessage()));
            }
        }
        else
        {
            return redirect()->route('plan.index')->with('error', __('Plan is deleted.'));
        }
    }

    public function addPayment(Request $request, $id)
    {
        $company_payment_setting = Utility::getCompanyPaymentSetting();
        $settings                = DB::table('settings')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('value', 'name');

        $objUser = \Auth::user();
        $invoice = Invoice::find($id);

        if($invoice)
        {
            if($request->amount > $invoice->getDue())
            {
                return redirect()->back()->with('error', __('Invalid amount.'));
            }
            else
            {
                try
                {
                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                    $price   = $request->amount;
                    Stripe\Stripe::setApiKey($company_payment_setting['stripe_secret']);
                    $data = Stripe\Charge::create(
                        [
                            "amount" => 100 * $price,
                            "currency" => Utility::getValByName('site_currency'),
                            "source" => $request->stripeToken,
                            "description" => __('Invoice') . Utility::invoiceNumberFormat($settings, $invoice->invoice_id),
                            "metadata" => ["order_id" => $orderID],
                        ]
                    );

                    if($data['amount_refunded'] == 0 && empty($data['failure_code']) && $data['paid'] == 1 && $data['captured'] == 1)
                    {

                        $payments = InvoicePayment::create(
                            [
                                'invoice' => $invoice->id,
                                'date' => date('Y-m-d'),
                                'amount' => $price,
                                'payment_method' => 1,
                                'transaction' => $orderID,
                                'payment_type' => __('STRIPE'),
                                'receipt' => $data['receipt_url'],
                                'notes' => __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id),
                            ]
                        );

                        $invoice = Invoice::find($id);

                        if($invoice->getDue() <= 0.0)
                        {
                            Invoice::change_status($invoice->id, 5);
                        }
                        elseif($invoice->getDue() > 0)
                        {
                            Invoice::change_status($invoice->id, 4);
                        }
                        else
                        {
                            Invoice::change_status($invoice->id, 3);
                        }

                        return redirect()->back()->with('success', __(' Payment successfully added.'));
                    }
                    else
                    {
                        return redirect()->back()->with('error', __('Transaction has been failed.'));
                    }
                }
                catch(\Exception $e)
                {
                    return redirect()->route(
                        'invoice.show', \Crypt::encrypt($invoice->id)
                    )->with('error', __($e->getMessage()));
                }
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function invoicePayWithStripe(Request $request)
    {
        $amount = $request->amount;

      

        $settings = Utility::settings();
        $validatorArray = [
            'amount' => 'required',
            'invoice_id' => 'required',
        ];
        $validator      = Validator::make(
            $request->all(), $validatorArray
        )->setAttributeNames(
            ['invoice_id' => 'Invoice']
        );
        if($validator->fails())
        {
            return Utility::error_res($validator->errors()->first());
        }

        $invoice = Invoice::find($request->invoice_id);
        //dd($invoice->id);
        $invoice_id = $invoice->id;
        $authuser = User::where('id', $invoice->created_by)->first();
        $amount = number_format((float)$request->amount, 2, '.', '');

        if(\Auth::check()){
            $company_payment = Utility::getCompanyPaymentSetting();
        }else{
            $company_payment = Utility::getNonAuthCompanyPaymentSetting($invoice->created_by);
        }
        $invoice_getdue = number_format((float)$invoice->getDue(), 2, '.', '');

        if($invoice_getdue < $amount){
            return Utility::error_res('not correct amount');
        }



            $stripe_formatted_price = in_array(
                $settings['site_currency'], [
                                   'MGA',
                                   'BIF',
                                   'CLP',
                                   'PYG',
                                   'DJF',
                                   'RWF',
                                   'GNF',
                                   'UGX',
                                   'JPY',
                                   'VND',
                                   'VUV',
                                   'XAF',
                                   'KMF',
                                   'KRW',
                                   'XOF',
                                   'XPF',
                               ]
            ) ? number_format($amount, 2, '.', '') : number_format($amount, 2, '.', '') * 100;

            $return_url_parameters = function ($return_type){
                return '&return_type=' . $return_type . '&payment_processor=stripe';
            };

            /* Initiate Stripe */
            \Stripe\Stripe::setApiKey($company_payment['stripe_secret']);

            
            // dd(route('invoice.stripe', [encrypt($request->invoice_id),$amount,'return_type'=>'success']));
            $stripe_session = \Stripe\Checkout\Session::create(
                [
                    'payment_method_types' => ['card'],
                    'line_items' => [
                        [
                            'name' => $settings['company_name'] . " - " . Utility::invoiceNumberFormat($settings,$invoice->invoice_id),
                            'description' => 'payment for Invoice',
                            'amount' => $stripe_formatted_price,
                            'currency' => $settings['site_currency'],
                            'quantity' => 1,
                        ],
                    ],
                    'metadata' => [
                        'user_id' => $authuser->id,
                        'invoice_id' => $request->invoice_id,
                    ],
                    'success_url' => route('invoice.stripe', [encrypt($request->invoice_id),$amount,'return_type'=>'success']),
                    'cancel_url' => route('invoice.stripe', [encrypt($request->invoice_id),$amount,'return_type'=>'cancel']),
                ]
            );


            $stripe_session = $stripe_session ?? false;
         
            try{
                return redirect()->to($stripe_session->url);
            }catch(\Exception $e)
            {
                \Log::debug($e->getMessage());
                return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice_id))->with('error', __('Transaction has been failed!'));
            }
        
    }

    public function getInvociePaymentStatus(Request $request,$invoice_id,$amount)
    {
        Session::forget('stripe_session');
        // try
        // {
            
            if($request->return_type == 'success')
            {
                if(!empty($invoice_id))
                {
                    $invoice_id = decrypt($invoice_id);
                    $invoice    = Invoice::where('id',$invoice_id)->first();
                    
                    if(\Auth::check()){
                        $company_payment = Utility::getCompanyPaymentSetting();
                    }else{
                        $company_payment = Utility::getNonAuthCompanyPaymentSetting($invoice->created_by);
                    }
                    if($invoice)
                    {
                        // try
                        // {
                            $settings = Utility::settingsById($invoice->created_by);
                            if($request->return_type == 'success')
                            {
                                $invoice_payment                 = new InvoicePayment();
                                $invoice_payment->transaction    =   time();;
                                $invoice_payment->invoice     = $invoice->id;
                                $invoice_payment->amount         = isset($amount) ? $amount : 0;
                                $invoice_payment->date           = date('Y-m-d');
                                $invoice_payment->payment_method   = __('STRIPE');
                                $invoice_payment->payment_type   = __('STRIPE');
                                $invoice_payment->notes          = Utility::invoiceNumberFormat($settings, $invoice->id);
                                $invoice_payment->save();
                               
                                $invoice_getdue = number_format((float)$invoice->getDue(), 2, '.', '');
                               // dd($invoice_getdue);
                                if($invoice_getdue <= 0.0)
                                {

                                    Invoice::change_status($invoice->id, 3);
                                }
                                else{

                                    Invoice::change_status($invoice->id, 2);
                                }


                                if(\Auth::check())
                                {
                                     $user = Auth::user();
                                }
                                else
                                {
                                   $user=User::where('id',$invoice->created_by)->first();
                                }

                                $amt = isset($amount) ? $amount : 0;
                                $settings  = Utility::settings();
                                if(isset($settings['payment_create_notification']) && $settings['payment_create_notification'] ==1){

                                    $msg = __('New payment of ').$amt.' '.__('created for ').$user->name.__(' by STRIPE').'.';
                                    //dd($msg);
                                    Utility::send_slack_msg($msg); 
                                       
                                }
                                if(isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] ==1){
                                        $resp = __('New payment of ').$amt.' '.__('created for ').$user->name.__(' by STRIPE').'.';
                                            Utility::send_telegram_msg($resp);    
                                }
                                $client_namee = Client::where('user_id',$invoice->client)->first();
                                if(isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] ==1)
                                {
                                     $message = __('New payment of ').$amount.' '.__('created for ').$user->name.__(' by STRIPE').'.';
                                     //dd($message);
                                     Utility::send_twilio_msg($client_namee->mobile,$message);
                                }

                                return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('success', __('Payment added Successfully'));
                            }else
                            {
                                return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('error', __('Transaction has been failed!'));
                            }
                        // }
                        // catch(\Exception $e){

                        //     return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('error', __('Transaction has been failed!'));
                        // }
                    }else{

                        return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('error', __('Invoice not found.'));
                    }
                }else{

                    return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('error', __('Invoice not found.'));
                }
            }
            else
            {

                return redirect()->route('pay.invoice',$invoice_id)->with('error', __('Transaction has been failed!'));
            }
            // catch(\Exception $exception)
            // {
            //     return redirect()->route('pay.invoice',$invoice_id)->with('error', $exception->getMessage());
    
            // }
    
    }

}
