<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Utility;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PaytmWallet;

class PaytmPaymentController extends Controller
{
    public function paymentConfig($user)
    {
        if($user->type == 'company')
        {
            $payment_setting = Utility::getAdminPaymentSetting();
        }
        else
        {
            $payment_setting = Utility::getCompanyPaymentSetting();
        }

        config(
            [
                'services.paytm-wallet.env' => isset($payment_setting['paytm_mode']) ? $payment_setting['paytm_mode'] : '',
                'services.paytm-wallet.merchant_id' => isset($payment_setting['paytm_merchant_id']) ? $payment_setting['paytm_merchant_id'] : '',
                'services.paytm-wallet.merchant_key' => isset($payment_setting['paytm_merchant_key']) ? $payment_setting['paytm_merchant_key'] : '',
                'services.paytm-wallet.merchant_website' => 'WEBSTAGING',
                'services.paytm-wallet.channel' => 'WEB',
                'services.paytm-wallet.industry_type' => isset($payment_setting['paytm_industry_type']) ? $payment_setting['paytm_industry_type'] : '',
            ]
        );
    }


    public function planPayWithPaytm(Request $request)
    {

        $payment    = $this->paymentConfig($user);
        $planID     = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $plan       = Plan::find($planID);
        $authuser   = \Auth::user();
        $coupons_id = '';
        if($plan)
        {

            $price = $plan->price;
            if(isset($request->coupon) && !empty($request->coupon))
            {
                $request->coupon = trim($request->coupon);
                $coupons         = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if(!empty($coupons))
                {
                    $usedCoupun             = $coupons->used_coupon();
                    $discount_value         = ($price / 100) * $coupons->discount;
                    $plan->discounted_price = $price - $discount_value;
                    $coupons_id             = $coupons->id;
                    if($usedCoupun >= $coupons->limit)
                    {
                        return Utility::error_res(__('This coupon code has expired.'));
                    }
                    $price = $price - $discount_value;
                }
                else
                {
                    return Utility::error_res(__('This coupon code is invalid or has expired.'));
                }
            }

            if($price <= 0)
            {
                $authuser->plan = $plan->id;
                $authuser->save();

                $assignPlan = $authuser->assignPlan($plan->id);

                if($assignPlan['is_success'] == true && !empty($plan))
                {

                    $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
                    Order::create(
                        [
                            'order_id' => $orderID,
                            'name' => null,
                            'email' => null,
                            'card_number' => null,
                            'card_exp_month' => null,
                            'card_exp_year' => null,
                            'plan_name' => $plan->name,
                            'plan_id' => $plan->id,
                            'price' => $price == null ? 0 : $price,
                            'price_currency' => !empty(env('CURRENCY')) ? env('CURRENCY') : 'USD',
                            'txn_id' => '',
                            'payment_type' => 'Paytm',
                            'payment_status' => 'succeeded',
                            'receipt' => null,
                            'user_id' => $authuser->id,
                        ]
                    );
                    $res['msg']  = __("Plan successfully upgraded.");
                    $res['flag'] = 2;

                    return $res;
                }
                else
                {
                    return Utility::error_res(__('Plan fail to upgrade.'));
                }
            }

            $call_back = route(
                'plan.paytm', [
                                $request->plan_id,
                                'coupon_id=' . $coupons_id,
                            ]
            );
            $payment   = PaytmWallet::with('receive');
            $payment->prepare(
                [
                    'order' => date('Y-m-d') . '-' . strtotime(date('Y-m-d H:i:s')),
                    'user' => Auth::user()->id,
                    'mobile_number' => $request->mobile,
                    'email' => Auth::user()->email,
                    'amount' => $price,
                    'plan' => $plan->id,
                    'callback_url' => $call_back,
                ]
            );

            return $payment->receive();
        }
        else
        {
            return Utility::error_res(__('Plan is deleted.'));
        }

    }

    public function getPaymentStatus(Request $request, $plan)
    {


        $planID  = \Illuminate\Support\Facades\Crypt::decrypt($plan);
        $plan    = Plan::find($planID);
        $user    = Auth::user();
        $orderID = time();
        if($plan)
        {
            try
            {
                $transaction = PaytmWallet::with('receive');
                $response    = $transaction->response();

                if($transaction->isSuccessful())
                {

                    if($request->has('coupon_id') && $request->coupon_id != '')
                    {
                        $coupons = Coupon::find($request->coupon_id);
                        if(!empty($coupons))
                        {
                            $userCoupon         = new UserCoupon();
                            $userCoupon->user   = $user->id;
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
                    }

                    $order                 = new Order();
                    $order->order_id       = $orderID;
                    $order->name           = $user->name;
                    $order->card_number    = '';
                    $order->card_exp_month = '';
                    $order->card_exp_year  = '';
                    $order->plan_name      = $plan->name;
                    $order->plan_id        = $plan->id;
                    $order->price          = isset($request->TXNAMOUNT) ? $request->TXNAMOUNT : 0;
                    $order->price_currency = env('CURRENCY_CODE');
                    $order->txn_id         = isset($request->TXNID) ? $request->TXNID : '';
                    $order->payment_type   = __('paytm');
                    $order->payment_status = 'success';
                    $order->receipt        = '';
                    $order->user_id        = $user->id;
                    $order->save();

                    $assignPlan = $user->assignPlan($plan->id, $request->payment_frequency);

                    if($assignPlan['is_success'])
                    {
                        return redirect()->route('plan.index')->with('success', __('Plan activated Successfully!'));
                    }
                    else
                    {
                        return redirect()->route('plan.index')->with('error', __($assignPlan['error']));
                    }
                }
                else
                {
                    return redirect()->route('plan.index')->with('error', __('Transaction has been failed! '));
                }
            }
            catch(\Exception $e)
            {
                return redirect()->route('plan.index')->with('error', __('Plan not found!'));
            }
        }
    }

    public function invoicePayWithPaytm(Request $request)
    {

        $invoiceID = $request->invoice_id;

         $invoiceID = \Crypt::decrypt($invoiceID);
        $invoice   = Invoice::find($invoiceID);

        if(\Auth::check())
        {
            $user=\Auth::user();
        }
        else
        {
            $user= User::where('id',$invoice->created_by)->first();
        } 

        $payment   = $this->paymentConfig($user);
        $invoice   = Invoice::find($invoiceID);

        if($invoice)
        {
            $price = $request->amount;
            if($price > 0)
            {
                $call_back = route(
                    'invoice.paytm', [
                                       $request->invoice_id,
                                       $price
                                   ]
                );
                $payment   = PaytmWallet::with('receive');

                $payment->prepare(
                    [
                        'order' => date('Y-m-d') . '-' . strtotime(date('Y-m-d H:i:s')),
                        'user' => $user->id,
                        'mobile_number' => $request->mobile,
                        'email' => $user->email,
                        'amount' => $price,
                        'invoice' => $invoice->id,
                        'callback_url' => $call_back,
                    ]
                );


                return $payment->receive();

            }
            else
            {
                $res['msg']  = __("Enter valid amount.");
                $res['flag'] = 2;

                return $res;
            }

        }
        else
        {
            return Utility::error_res(__('Invoice is deleted.'));
        }

    }

    public function getInvoicePaymentStatus(Request $request, $invoice_id,$amount)
    {
     
        $invoice   = Invoice::find($invoice_id);
        if(\Auth::check())
        {
            $user=\Auth::user();
        }
        else
        {
            $user= User::where('id',$invoice->created_by)->first();
            
        } 
        $payment  = $this->paymentConfig($user);
        $orderID  = strtoupper(str_replace('.', '', uniqid('', true)));

        $settings = DB::table('settings')->where('created_by', '=', $user->creatorId())->get()->pluck('value', 'name');

        $invoiceID = $invoice_id;
        $invoice   = Invoice::find($invoiceID);

        if($invoice)
        {
            try
            {
               
                $transaction = PaytmWallet::with('receive');
                

                $response    = $transaction->response();
                
             
                if($transaction->isSuccessful())
                {
                    

                    $payments = InvoicePayment::create(
                        [
                            'invoice' => $invoice->id,
                            'date' => date('Y-m-d'),
                            'amount' => $amount,
                            'payment_method' => 1,
                            'transaction' => $orderID,
                            'payment_type' => __('Paytm'),
                            'receipt' => '',
                            'notes' => __('Invoice') . ' ' . Utility::invoiceNumberFormat($settings, $invoice->invoice_id),
                        ]
                    );

                   

                   

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

                    $settings  = Utility::settings();
                    if(isset($settings['payment_create_notification']) && $settings['payment_create_notification'] ==1){

                        $msg = __('New payment of ').$amount.' '.__('created for ').$user->name.__(' by Paytm').'.';
                        //dd($msg);
                        Utility::send_slack_msg($msg); 
                           
                    }
                    if(isset($settings['telegram_payment_create_notification']) && $settings['telegram_payment_create_notification'] ==1){
                        $resp = __('New payment of ').$amount.' '.__('created for ').$user->name.__(' by Paytm').'.';
                            Utility::send_telegram_msg($resp);    
                    }
                    $client_namee = Client::where('user_id',$invoice->client)->first();
                    if(isset($settings['twilio_invoice_payment_create_notification']) && $settings['twilio_invoice_payment_create_notification'] ==1)
                    {
                         $message = __('New payment of ').$amount.' '.__('created for ').$user->name.__(' by Paytm').'.';
                         //dd($message);
                         Utility::send_twilio_msg($client_namee->mobile,$message);
                    }
                    return redirect()->back()->with('success', __(' Payment successfully added.'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Transaction has been failed! '));
                }
            }
            catch(\Exception $e)
            {
                if(\Auth::check())
                                {
                                     return redirect()->back()->with('error', __('Invoice not found!'));
                                }
                                else
                                {
                                    return redirect()->route('pay.invoice',\Illuminate\Support\Facades\Crypt::encrypt($invoice->id))->with('error', __('Invoice not found!'));
                                }

              
            }
        }
    }
}
