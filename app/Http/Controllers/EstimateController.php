<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Estimate;
use App\Models\EstimateProduct;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\Item;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class EstimateController extends Controller
{

    public function __construct()
    {

    }

    public function index(Request $request)
    {
        if(\Auth::user()->type == 'company' || \Auth::user()->type == 'client')
        {
            if(\Auth::user()->type == 'company')
            {
                $query = Estimate::where('created_by', '=', \Auth::user()->creatorId());
            }
            else
            {
                $query = Estimate::where('client', '=', \Auth::user()->id);
            }

            if($request->status != '')
            {
                $query->where('status', $request->status);
            }

            if(!empty($request->start_date))
            {
                $query->where('issue_date', '>=', $request->start_date);
            }

            if(!empty($request->end_date))
            {
                $query->where('issue_date', '<=', $request->end_date);
            }

            $estimates = $query->get();
            $status    = Estimate::$statues;

            return view('estimate.index', compact('estimates', 'status'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }


    public function create()
    {
        $clients = User::where('type', '=', 'client')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $clients->prepend('Select Client', '');

        $categories = Category::where('type', 1)->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $categories->prepend('Select Category', '');

        $items = Item::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $items->prepend('Select Item', '');
        $estimateId = \Auth::user()->estimateNumberFormat($this->estimateNumber());

        return view('estimate.create', compact('clients', 'categories', 'estimateId', 'items'));
    }


    public function store(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'client' => 'required',
                                   'issue_date' => 'required',
                                   'expiry_date' => 'required',
                                   'category' => 'required',
                                   'items' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $status = Estimate::$statues;

            $estimate                 = new Estimate();
            $estimate->estimate       = $this->estimateNumber();
            $estimate->client         = $request->client;
            $estimate->status         = 0;
            $estimate->issue_date     = $request->issue_date;
            $estimate->expiry_date    = $request->expiry_date;
            $estimate->category       = $request->category;
            $estimate->discount_apply = isset($request->discount_apply) ? 1 : 0;
            $estimate->created_by     = \Auth::user()->creatorId();
            $estimate->save();

            $products = $request->items;

            for($i = 0; $i < count($products); $i++)
            {
                $estimateProduct              = new EstimateProduct();
                $estimateProduct->estimate    = $estimate->id;
                $estimateProduct->item        = $products[$i]['item'];
                $estimateProduct->quantity    = $products[$i]['quantity'];
                $estimateProduct->discount    = isset($products[$i]['discount']) ? $products[$i]['discount'] : 0;
                $estimateProduct->price       = $products[$i]['price'];
                $estimateProduct->tax         = $products[$i]['tax'];
                $estimateProduct->description = $products[$i]['description'];
                $estimateProduct->save();
            }

            $settings  = Utility::settings();
            if(isset($settings['estimation_create_notification']) && $settings['estimation_create_notification'] ==1){

                $msg = __('New Estimation created by the ').\Auth::user()->name.'.';
                //dd($msg);
                Utility::send_slack_msg($msg); 
                   
            }
            if(isset($settings['telegram_estimation_create_notification']) && $settings['telegram_estimation_create_notification'] ==1){
                    $response =__('New Estimation created by the ').\Auth::user()->name.'.';
                    Utility::send_telegram_msg($response);    
            }
            return redirect()->route('estimate.index', $estimate->id)->with('success', __('Estimate successfully created.'));
        }

    }


    public function show($ids)
    {
        $id       = \Crypt::decrypt($ids);
        $estimate = Estimate::find($id);
        $settings = Utility::settings();

        $status = [
            __('Draft'),
            __('Open'),
            __('Sent'),
            __('Close'),
        ];

        return view('estimate.view', compact('estimate', 'settings', 'status'));
    }


    public function edit($ids)
    {
        $id       = \Crypt::decrypt($ids);
        $estimate = Estimate::find($id);
        $clients  = User::where('type', '=', 'client')->where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $clients->prepend('Select Client', '');

        $categories = Category::where('type', 1)->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $categories->prepend('Select Category', '');

        $items = Item::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        $items->prepend('Select Item', '');
        $estimateId = \Auth::user()->estimateNumberFormat($estimate->estimate);

        $item = [];
        foreach($estimate->items as $estimatelItem)
        {
            $itemAmount                = $estimatelItem->quantity * $estimatelItem->price;
            $estimatelItem->itemAmount = $itemAmount;
            $estimatelItem->taxes      = Utility::tax($estimatelItem->tax);
            $item[]                    = $estimatelItem;
        }

        return view('estimate.edit', compact('clients', 'categories', 'estimateId', 'items', 'estimate', 'item'));
    }


    public function update(Request $request, Estimate $estimate)
    {

        if(\Auth::user()->type == 'company')
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'client' => 'required',
                                   'issue_date' => 'required',
                                   'expiry_date' => 'required',
                                   'category' => 'required',
                                   'items' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $status = Estimate::$statues;

            $estimate->client         = $request->client;
            $estimate->status         = 0;
            $estimate->issue_date     = $request->issue_date;
            $estimate->expiry_date    = $request->expiry_date;
            $estimate->category       = $request->category;
            $estimate->discount_apply = isset($request->discount_apply) ? 1 : 0;
            $estimate->save();

            $products = $request->items;

            for($i = 0; $i < count($products); $i++)
            {
                $estimateProduct = EstimateProduct::find($products[$i]['id']);
                if($estimateProduct == null)
                {
                    $estimateProduct           = new EstimateProduct();
                    $estimateProduct->estimate = $estimate->id;
                }

                if(isset($products[$i]['item']))
                {
                    $estimateProduct->item = $products[$i]['item'];
                }


                $estimateProduct->quantity = $products[$i]['quantity'];
                if(isset($request->discount_apply))
                {
                    $estimateProduct->discount = isset($products[$i]['discount']) ? $products[$i]['discount'] : 0;
                }
                else
                {
                    $estimateProduct->discount = 0;
                }
                $estimateProduct->tax         = $products[$i]['tax'];
                $estimateProduct->price       = $products[$i]['price'];
                $estimateProduct->description = $products[$i]['description'];
                $estimateProduct->save();
            }

            return redirect()->route('estimate.index', $estimate->id)->with('success', __('Estimate successfully created.'));
        }

    }


    public function destroy(Estimate $estimate)
    {
        $estimate->delete();
        EstimateProduct::where('estimate', '=', $estimate->id)->delete();

        return redirect()->back()->with('success', __('Estimate successfully deleted.'));
    }

    function estimateNumber()
    {
        $latest = Estimate::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->estimate + 1;
    }

    public function product(Request $request)
    {
        $data['product']     = $product = Item::find($request->product_id);
        $data['unit']        = (!empty($product->units)) ? $product->units->name : '';
        $data['taxRate']     = $taxRate = !empty($product->tax) ? $product->taxRate($product->tax) : 0;
        $data['taxes']       = !empty($product->tax) ? $product->tax($product->tax) : 0;
        $salePrice           = $product->sale_price;
        $quantity            = 1;
        $data['taxPrice']    = $taxPrice = ($taxRate / 100) * ($salePrice * $quantity);
        $data['totalAmount'] = ($salePrice * $quantity) + $taxPrice;

        return json_encode($data);
    }

    public function productDestroy(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            EstimateProduct::where('id', '=', $request->id)->delete();

            return redirect()->back()->with('success', __('Estimate product successfully deleted.'));
        }

    }

    public function send($id)
    {
        if(\Auth::user()->type == 'company')
        {
            $estimate = Estimate::find($id);
            if($estimate->status == 0)
            {
                $estimate->send_date = date('Y-m-d');
                $estimate->status    = 1;
                $estimate->save();
            }

            $client             = User::where('id', $estimate->client)->first();
            $estimate->name     = !empty($client) ? $client->name : '';
            $estimate->estimate = \Auth::user()->estimateNumberFormat($estimate->estimate);

            $estimateId    = \Crypt::encrypt($estimate->id);
            $estimate->url = route('estimate.pdf', $estimateId);

            $client = User::find($estimate->client);

            $estArr = [
                'estimation_id' => $estimate->estimate,
                'estimation_client' => $client->name,
                'estimation_category' => !empty($estimate->categories) ? $estimate->categories->name : '',
                'estimation_issue_date' => $estimate->issue_date,
                'estimation_expiry_date' => $estimate->expiry_date,
                'estimation_status' => Estimate::$statues[$estimate->status],
            ];

            // Send Email
            $resp = Utility::sendEmailTemplate('send_estimation', [$client->id => $client->email], $estArr);

            return redirect()->back()->with('success', __('Estimate successfully sent.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));

        }

    }

    public function previewEstimate($template, $color)
    {
        $objUser  = \Auth::user();
        $settings = Utility::settings();
        $estimate = new Estimate();

        $client               = new \stdClass();
        $client->company_name = '<Company Name>';
        $client->name         = '<Name>';
        $client->email        = '<Email>';
        $client->mobile       = '<Phone>';
        $client->address      = '<Address>';
        $client->country      = '<Country>';
        $client->state        = '<State>';
        $client->city         = '<City>';


        $totalTaxPrice = 0;
        $taxesData     = [];

        $items = [];
        for($i = 1; $i <= 3; $i++)
        {
            $item              = new \stdClass();
            $item->name        = 'Item ' . $i;
            $item->quantity    = 1;
            $item->tax         = 5;
            $item->discount    = 50;
            $item->price       = 100;
            $item->description = 'Item description';

            $taxes = [
                'Tax 1',
                'Tax 2',
            ];

            $itemTaxes = [];
            foreach($taxes as $k => $tax)
            {
                $taxPrice         = 10;
                $totalTaxPrice    += $taxPrice;
                $itemTax['name']  = 'Tax ' . $k;
                $itemTax['rate']  = '10 %';
                $itemTax['price'] = '$10';
                $itemTaxes[]      = $itemTax;
                if(array_key_exists('Tax ' . $k, $taxesData))
                {
                    $taxesData['Tax ' . $k] = $taxesData['Tax 1'] + $taxPrice;
                }
                else
                {
                    $taxesData['Tax ' . $k] = $taxPrice;
                }
            }
            $item->itemTax = $itemTaxes;
            $items[]       = $item;
        }

        $estimate->estimate_id = 1;
        $estimate->issue_date  = date('Y-m-d H:i:s');
        $estimate->due_date    = date('Y-m-d H:i:s');
        $estimate->items       = $items;

        $estimate->totalTaxPrice = 60;
        $estimate->totalQuantity = 3;
        $estimate->totalRate     = 300;
        $estimate->totalDiscount = 10;
        $estimate->taxesData     = $taxesData;


        $preview    = 1;
        $color      = '#' . $color;
        $font_color = Utility::getFontColor($color);

        // $logo         = asset(Storage::url('uploads\logo/'));
        // $company_logo = Utility::getValByName('company_logo');
        // $img          = asset($logo . '/' .  'logo.png');

        $logo = Utility::get_superadmin_logo();
        $company_logo=Utility::getValByName('company_logo');
        $company_small_logo=Utility::getValByName('company_small_logo');
        $img          =  asset(Storage::url('uploads/logo/'.$logo));

        return view('estimate.templates.' . $template, compact('estimate', 'preview', 'color', 'img', 'settings', 'client', 'font_color'));
    }

    public function saveEstimateTemplateSettings(Request $request)
    {
        if(\Auth::user()->type == 'company')
        {
            $post = $request->all();
            unset($post['_token']);

            if(isset($post['estimate_template']) && (!isset($post['estimate_color']) || empty($post['estimate_color'])))
            {
                $post['estimate_color'] = "ffffff";
            }

            foreach($post as $key => $data)
            {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                                                                                                                                                 $data,
                                                                                                                                                 $key,
                                                                                                                                                 \Auth::user()->creatorId(),
                                                                                                                                             ]
                );
            }

            return redirect()->back()->with('success', __('Estimate Setting updated successfully'));
        }
    }

    public function pdf($id)
    {
        $settings = Utility::settings();

        $estimateId = Crypt::decrypt($id);
        $estimate   = Estimate::where('id', $estimateId)->first();

        $data  = \DB::table('settings');
        $data  = $data->where('created_by', '=', $estimate->created_by);
        $data1 = $data->get();

        foreach($data1 as $row)
        {
            $settings[$row->name] = $row->value;
        }

        $client        = $estimate->clients;
        $client->company_name = !empty($estimate->clientDetail) ? $estimate->clientDetail->company_name : '';
        $client->mobile       = !empty($estimate->clientDetail) ? $estimate->clientDetail->mobile : '';
        $client->address      = !empty($estimate->clientDetail) ? $estimate->clientDetail->address_1 : '';
        $client->zip          = !empty($estimate->clientDetail) ? $estimate->clientDetail->zip_code : '';
        $client->city         = !empty($estimate->clientDetail) ? $estimate->clientDetail->city : '';
        $client->state        = !empty($estimate->clientDetail) ? $estimate->clientDetail->state : '';
        $client->country      = !empty($estimate->clientDetail) ? $estimate->clientDetail->country : '';
        $items         = [];
        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate     = 0;
        $totalDiscount = 0;
        $taxesData     = [];
        foreach($estimate->items as $product)
        {

            $item              = new \stdClass();
            $item->name        = !empty($product->items) ? $product->items->name : '';
            $item->quantity    = $product->quantity;
            $item->discount    = $product->discount;
            $item->price       = $product->price;
            $item->tax         = $product->tax;
            $item->description = $product->description;

            $totalQuantity += $item->quantity;
            $totalRate     += $item->price;
            $totalDiscount += $item->discount;

            $taxes     = \Utility::tax($item->tax);
            $itemTaxes = [];
            if(!empty($item->tax))
            {
                foreach($taxes as $tax)
                {

                    $taxPrice      = \Utility::taxRate($tax->rate, $item->price, $item->quantity);
                    $totalTaxPrice += $taxPrice;

                    $itemTax['name']  = $tax->name;
                    $itemTax['rate']  = $tax->rate . '%';
                    $itemTax['price'] = \App\Models\Utility::priceFormat($settings, $taxPrice);
                    $itemTaxes[]      = $itemTax;


                    if(array_key_exists($tax->name, $taxesData))
                    {
                        $taxesData[$tax->name] = $taxesData[$tax->name] + $taxPrice;
                    }
                    else
                    {
                        $taxesData[$tax->name] = $taxPrice;
                    }

                }

                $item->itemTax = $itemTaxes;
            }
            else
            {
                $item->itemTax = [];
            }

            $item->itemTax = $itemTaxes;
            $items[]       = $item;

        }

        $estimate->items         = $items;
        $estimate->totalTaxPrice = $totalTaxPrice;
        $estimate->totalQuantity = $totalQuantity;
        $estimate->totalRate     = $totalRate;
        $estimate->totalDiscount = $totalDiscount;
        $estimate->taxesData     = $taxesData;

        $estimate->items = $items;

        //Set your logo
        $logo         = asset(Storage::url('uploads\logo/'));
        $company_logo = Utility::getValByName('company_logo');
        $img          = asset($logo . '/' .  'logo.png');

        if($estimate)
        {

            $color      = '#' . $settings['estimate_color'];
            $font_color = Utility::getFontColor($color);

            return view('estimate.templates.' . $settings['estimate_template'], compact('estimate', 'color', 'settings', 'client', 'img', 'font_color'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function statusChange(Request $request)
    {

        if(\Auth::user()->type == 'company')
        {
            $status           = $request->status;
            $estimate         = Estimate::find($request->estimation_id);
            $estimate->status = $status;
            $estimate->save();

            return redirect()->back()->with('success', __('Estimate status changed successfully.'));
        }


    }

    public function items(Request $request)
    {
        $items = EstimateProduct::where('estimate', $request->estimate_id)->where('item', $request->items_id)->first();

        return json_encode($items);
    }

    public function convert($estimation_id)
    {

        $estimation             = Estimate::where('id', $estimation_id)->first();
        $estimation->is_convert = 1;
        $estimation->save();


        $convertInvoice              = new Invoice();
        $convertInvoice->invoice_id  = $this->estimateNumber();
        $convertInvoice->issue_date  = $estimation->issue_date;
        $convertInvoice->due_date    = $estimation->expiry_date;
        $convertInvoice->client      = $estimation->client;
        $convertInvoice->project     = 0;
        $convertInvoice->type        = 'Product';
        $convertInvoice->status      = 0;
        $convertInvoice->tax         = $estimation->tax;
        $convertInvoice->description = $estimation->description;
        $convertInvoice->created_by  = \Auth::user()->creatorId();
        $convertInvoice->save();

        if($convertInvoice)
        {
            $estimateProduct = EstimateProduct::where('estimate', $estimation_id)->get();
            foreach($estimateProduct as $product)
            {
                $duplicateProduct              = new InvoiceProduct();
                $duplicateProduct->invoice     = $convertInvoice->id;
                $duplicateProduct->item        = !empty($product->items) ? $product->items->name : '';
                $duplicateProduct->quantity    = $product->quantity;
                $duplicateProduct->tax         = $product->tax;
                $duplicateProduct->discount    = $product->discount;
                $duplicateProduct->price       = $product->price;
                $duplicateProduct->description = $product->description;
                $duplicateProduct->type        = 'product';
                $duplicateProduct->save();
            }
        }

        return redirect()->back()->with('success', __('Estimate to invoice converted successfully.'));
    }

    function invoiceNumber()
    {
        $latest = Estimate::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if(!$latest)
        {
            return 1;
        }

        return $latest->estimate + 1;
    }

    public function payestimate($estimate_id){

        if(!empty($estimate_id)){
            $id = \Illuminate\Support\Facades\Crypt::decrypt($estimate_id);

            $estimate = Estimate::where('id',$id)->first();

            if(!is_null($estimate)){

                $settings = Utility::settings();

                $items         = [];
                $totalTaxPrice = 0;
                $totalQuantity = 0;
                $totalRate     = 0;
                $totalDiscount = 0;
                $taxesData     = [];
                 //dd($invoice->items);

                foreach($estimate->items as $item)
                {
                    $totalQuantity += $item->quantity;
                    $totalRate     += $item->price;
                    $totalDiscount += $item->discount;
                    $taxes         = Utility::tax($item->tax);

                    $itemTaxes = [];
                    foreach($taxes as $tax)
                    {
                        if(!empty($tax))
                        {
                            $taxPrice            = Utility::taxRate($tax->rate, $item->price, $item->quantity);
                            $totalTaxPrice       += $taxPrice;
                            $itemTax['tax_name'] = $tax->tax_name;
                            $itemTax['tax']      = $tax->tax . '%';
                            $itemTax['price']    = Utility::priceFormat($settings, $taxPrice);
                            $itemTaxes[]         = $itemTax;

                            if(array_key_exists($tax->name, $taxesData))
                            {
                                $taxesData[$itemTax['tax_name']] = $taxesData[$tax->tax_name] + $taxPrice;
                            }
                            else
                            {
                                $taxesData[$tax->tax_name] = $taxPrice;
                            }
                        }
                        else
                        {
                            $taxPrice            = Utility::taxRate(0, $item->price, $item->quantity);
                            $totalTaxPrice       += $taxPrice;
                            $itemTax['tax_name'] = 'No Tax';
                            $itemTax['tax']      = '';
                            $itemTax['price']    = Utility::priceFormat($settings, $taxPrice);
                            $itemTaxes[]         = $itemTax;

                            if(array_key_exists('No Tax', $taxesData))
                            {
                                $taxesData[$tax->tax_name] = $taxesData['No Tax'] + $taxPrice;
                            }
                            else
                            {
                                $taxesData['No Tax'] = $taxPrice;
                            }

                        }
                    }
                    $item->itemTax = $itemTaxes;
                    $items[]       = $item;
                }
                $estimate->items         = $items;
                $estimate->totalTaxPrice = $totalTaxPrice;
                $estimate->totalQuantity = $totalQuantity;
                $estimate->totalRate     = $totalRate;
                $estimate->totalDiscount = $totalDiscount;
                $estimate->taxesData     = $taxesData;

                $company_setting = Utility::settings();

                $ownerId = Utility::ownerIdforInvoice($estimate->created_by);

                $payment_setting = Utility::invoice_payment_settings($ownerId);
                //dd($ownerId);
                $users = User::where('id',$estimate->created_by)->first();

                if(!is_null($users)){
                    \App::setLocale($users->lang);
                }else{
                    $users = User::where('type','company')->first();
                    \App::setLocale($users->lang);
                }

                return view('estimate.estimatepay',compact('estimate', 'company_setting','users','payment_setting'));
            }else{
                return abort('404', 'The Link You Followed Has Expired');
            }
        }else{
            return abort('404', 'The Link You Followed Has Expired');
        }
    }

    public function pdffromestimate($id)
    {
        $settings = Utility::settings();

        $estimateId = Crypt::decrypt($id);
        $estimate   = Estimate::where('id', $estimateId)->first();

        $data  = \DB::table('settings');
        $data  = $data->where('created_by', '=', $estimate->created_by);
        $data1 = $data->get();

        foreach($data1 as $row)
        {
            $settings[$row->name] = $row->value;
        }

        $user         = new User();
        $user->name   = $estimate->name;
        $user->email  = $estimate->contacts;
        $user->mobile = $estimate->contacts;

        $user->bill_address = $estimate->billing_address;
        $user->bill_zip     = $estimate->billing_postalcode;
        $user->bill_city    = $estimate->billing_city;
        $user->bill_country = $estimate->billing_country;
        $user->bill_state   = $estimate->billing_state;

        $user->address = $estimate->shipping_address;
        $user->zip     = $estimate->shipping_postalcode;
        $user->city    = $estimate->shipping_city;
        $user->country = $estimate->shipping_country;
        $user->state   = $estimate->shipping_state;


        $items         = [];
        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate     = 0;
        $totalDiscount = 0;
        $taxesData     = [];

        foreach($estimate->items as $product)
        {
            $item           = new \stdClass();
            $item->name     = !empty($product->items->name) ? $product->items->name : '--';
            $item->quantity = $product->quantity;
            $item->tax      = !empty($product->taxs) ? $product->taxs->rate : '--';
            $item->discount = $product->discount;
            $item->price    = $product->price;

            $totalQuantity += $item->quantity;
            $totalRate     += $item->price;
            $totalDiscount += $item->discount;

            $taxes     = \Utility::tax($product->tax);
            $itemTaxes = [];
            foreach($taxes as $tax)
            {
                $taxPrice      = \Utility::taxRate($tax->rate, $item->price, $item->quantity);
                $totalTaxPrice += $taxPrice;

                $itemTax['name']  = $tax->tax_name;
                $itemTax['rate']  = $tax->rate . '%';
                $itemTax['price'] = \App\Models\Utility::priceFormat($settings, $taxPrice);
                $itemTaxes[]      = $itemTax;


                if(array_key_exists($tax->tax_name, $taxesData))
                {
                    $taxesData[$tax->tax_name] = $taxesData[$tax->tax_name] + $taxPrice;
                }
                else
                {
                    $taxesData[$tax->tax_name] = $taxPrice;
                }

            }
            $item->itemTax = $itemTaxes;
            $items[]       = $item;
        }

        $estimate->items         = $items;
        $estimate->totalTaxPrice = $totalTaxPrice;
        $estimate->totalQuantity = $totalQuantity;
        $estimate->totalRate     = $totalRate;
        $estimate->totalDiscount = $totalDiscount;
        $estimate->taxesData     = $taxesData;

        //Set your logo
        $logo         = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo');
        $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo.png'));

        if($estimate)
        {
            $color      = '#' . $settings['estimate_color'];
            $font_color = Utility::getFontColor($color);

            return view('estimate.templates.' . $settings['estimate_template'], compact('estimate', 'user', 'color', 'settings', 'img', 'font_color'));
        }
        else
        {
            return redirect()->route('pay.estimate',\Illuminate\Support\Facades\Crypt::encrypt($invoiceId))->with('error', __('Permission denied.'));
        }
    }

}