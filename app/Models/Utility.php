<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Mail\CommonEmailTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;

class Utility extends Model
{
    public static function settings()
    {
        $data = DB::table('settings');
        if(\Auth::check())
        {
            $data = $data->where('created_by','=',\Auth::user()->creatorId())->get();
            if(count($data)==0){
                $data =DB::table('settings')->where('created_by', '=', 1 )->get();
            }

        }
        else
        {
            $data->where('created_by', '=', 1 );
            $data     = $data->get();

        }
        $settings = [
            "site_currency" => "USD",
            "site_currency_symbol" => "$",
            "site_currency_symbol_position" => "pre",
            "site_date_format" => "M j, Y",
            "site_time_format" => "g:i A",
            "employee_prefix" => "#EMP",
            "client_prefix" => "#CLT",
            "estimate_prefix" => "#EST",
            "invoice_prefix" => "#INV",
            "company_name" => "",
            "company_address" => "",
            "company_city" => "",
            "company_state" => "",
            "company_zipcode" => "",
            "company_country" => "",
            "company_telephone" => "",
            "company_email" => "",
            "company_email_from_name" => "",
            "footer_title" => "",
            "footer_notes" => "",
            "invoice_template" => "template1",
            "invoice_color" => "ffffff",
            "estimate_template" => "template1",
            "estimate_color" => "ffffff",
            "company_start_time" => "09:00",
            "company_end_time" => "18:00",
            "default_language" => "en",
            "enable_stripe" => "",
            "enable_paypal" => "",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "stripe_key" => "",
            "stripe_secret" => "",
            "registration_number" => "",
            "vat_number" => "",
            "footer_link_1" => "Support",
            "footer_value_1" => "#",
            "footer_link_2" => "Terms",
            "footer_value_2" => "#",
            "footer_link_3" => "Privacy",
            "footer_value_3" => "#",
            "display_landing_page" => "on",
            "title_text" => "",
            "footer_text" => "Copyright 2022",
            "journal_prefix" => "#JUR",
            "gdpr_cookie" => "",
            "cookie_text" => "",
            "SIGNUP" => '',
            "cust_theme_bg" => "on",
            "cust_darklayout" => "off",
            "color" => "theme-3",
             "SITE_RTL" => "off",
        ];

        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function settingsById($id)
    {
        $data = DB::table('settings');
        $data = $data->where('created_by', '=', $id);
        $data     = $data->get();
        $settings = [
            "site_currency" => "USD",
            "site_currency_symbol" => "$",
            "site_currency_symbol_position" => "pre",
            "site_date_format" => "M j, Y",
            "site_time_format" => "g:i A",
            "employee_prefix" => "#EMP",
            "client_prefix" => "#CLT",
            "estimate_prefix" => "#EST",
            "invoice_prefix" => "#INV",
            "company_name" => "",
            "company_address" => "",
            "company_city" => "",
            "company_state" => "",
            "company_zipcode" => "",
            "company_country" => "",
            "company_telephone" => "",
            "company_email" => "",
            "company_email_from_name" => "",
            "footer_title" => "",
            "footer_notes" => "",
            "invoice_template" => "template1",
            "invoice_color" => "ffffff",
            "estimate_template" => "template1",
            "estimate_color" => "ffffff",
            "company_start_time" => "09:00",
            "company_end_time" => "18:00",
            "default_language" => "en",
            "enable_stripe" => "",
            "enable_paypal" => "",
            "paypal_mode" => "",
            "paypal_client_id" => "",
            "paypal_secret_key" => "",
            "stripe_key" => "",
            "stripe_secret" => "",
            "registration_number" => "",
            "vat_number" => "",
            "footer_link_1" => "Support",
            "footer_value_1" => "#",
            "footer_link_2" => "Terms",
            "footer_value_2" => "#",
            "footer_link_3" => "Privacy",
            "footer_value_3" => "#",
            "display_landing_page" => "on",
            "title_text" => "",
            "footer_text" => "",
            "journal_prefix" => "#JUR",
            "gdpr_cookie" => "",
             "cookie_text" => "",
             "cust_theme_bg" => "",
             "cust_darklayout" => "",
             "color" => "",
        ];

        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function getCompanyPaymentSettingWithOutAuth($user_id)
    {
        $data     = \DB::table('company_payment_settings');
        $settings = [];
        $data    = $data->where('created_by', '=', $user_id);
        $data = $data->get();
        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function languages()
    {
        $dir     = base_path() . '/resources/lang/';
        $glob    = glob($dir . "*", GLOB_ONLYDIR);
        $arrLang = array_map(
            function ($value) use ($dir){
                return str_replace($dir, '', $value);
            }, $glob
        );
        $arrLang = array_map(
            function ($value) use ($dir){
                return preg_replace('/[0-9]+/', '', $value);
            }, $arrLang
        );
        $arrLang = array_filter($arrLang);

        return $arrLang;
    }

    public static function getValByName($key)
    {
        $setting = Utility::settings();
        if(!isset($setting[$key]) || empty($setting[$key]))
        {
            $setting[$key] = '';
        }

        return $setting[$key];
    }

    public static function setEnvironmentValue(array $values)
    {
        $envFile = app()->environmentFilePath();
        $str     = file_get_contents($envFile);
        if(count($values) > 0)
        {
            foreach($values as $envKey => $envValue)
            {
                $keyPosition       = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine           = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
                // If key does not exist, add it
                if(!$keyPosition || !$endOfLinePosition || !$oldLine)
                {
                    $str .= "{$envKey}='{$envValue}'\n";
                }
                else
                {
                    $str = str_replace($oldLine, "{$envKey}='{$envValue}'", $str);
                }
            }
        }
        $str = substr($str, 0, -1);
        $str .= "\n";
        if(!file_put_contents($envFile, $str))
        {
            return false;
        }

        return true;
    }

    public static function templateData()
    {
        $arr              = [];
        $arr['colors']    = [
            '003580',
            '666666',
            'f2f6fa',
            'f50102',
            'f9b034',
            'fbdd03',
            'c1d82f',
            '37a4e4',
            '8a7966',
            '6a737b',
            '050f2c',
            '0e3666',
            '3baeff',
            '3368e6',
            'b84592',
            'f64f81',
            'f66c5f',
            'fac168',
            '46de98',
            '40c7d0',
            'be0028',
            '2f9f45',
            '371676',
            '52325d',
            '511378',
            '0f3866',
            '48c0b6',
            '297cc0',
            'ffffff',
            '000000',
        ];
        $arr['templates'] = [
            "template1" => "New York",
            "template2" => "Toronto",
            "template3" => "Rio",
            "template4" => "London",
            "template5" => "Istanbul",
            "template6" => "Mumbai",
            "template7" => "Hong Kong",
            "template8" => "Tokyo",
            "template9" => "Sydney",
            "template10" => "Paris",
        ];

        return $arr;
    }

    public static function priceFormat($settings, $price)
    {
        return (($settings['site_currency_symbol_position'] == "pre") ? $settings['site_currency_symbol'] : '') . number_format($price, 2) . (($settings['site_currency_symbol_position'] == "post") ? $settings['site_currency_symbol'] : '');
    }

    public static function currencySymbol($settings)
    {
        return $settings['site_currency_symbol'];
    }

    public static function dateFormat($settings, $date)
    {
        return date($settings['site_date_format'], strtotime($date));
    }

    public static function timeFormat($settings, $time)
    {
        return date($settings['site_time_format'], strtotime($time));
    }

    public static function invoiceNumberFormat($settings, $number)
    {
        return $settings["invoice_prefix"] . sprintf("%05d", $number);
    }

    public static function estimateNumberFormat($settings, $number)
    {
        return $settings["estimate_prefix"] . sprintf("%05d", $number);
    }

    public static function proposalNumberFormat($settings, $number)
    {
        return $settings["proposal_prefix"] . sprintf("%05d", $number);
    }

    public static function billNumberFormat($settings, $number)
    {
        return $settings["bill_prefix"] . sprintf("%05d", $number);
    }

    public static function tax($taxes)
    {
        $taxArr = explode(',', $taxes);
        $taxes  = [];
        foreach($taxArr as $tax)
        {
            $taxes[] = TaxRate::find($tax);
        }

        return $taxes;
    }

    public static function totalTaxRate($taxes)
    {
        $taxArr  = explode(',', $taxes);
        $taxRate = 0;
        foreach($taxArr as $tax)
        {
            $tax     = TaxRate::find($tax);
            $taxRate += !empty($tax->rate) ? $tax->rate : 0;
        }

        return $taxRate;
    }

    public static function taxRate($taxRate, $price, $quantity)
    {
        return ($taxRate / 100) * ($price * $quantity);
    }

    // get font-color code accourding to bg-color
    public static function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        if(strlen($hex) == 3)
        {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        }
        else
        {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = array(
            $r,
            $g,
            $b,
        );

        //return implode(",", $rgb); // returns the rgb values separated by commas
        return $rgb; // returns an array with the rgb values
    }


    public static function getFontColor($color_code)
    {
        $rgb = self::hex2rgb($color_code);
        $R   = $G = $B = $C = $L = $color = '';

        $R = (floor($rgb[0]));
        $G = (floor($rgb[1]));
        $B = (floor($rgb[2]));

        $C = [
            $R / 255,
            $G / 255,
            $B / 255,
        ];

        for($i = 0; $i < count($C); ++$i)
        {
            if($C[$i] <= 0.03928)
            {
                $C[$i] = $C[$i] / 12.92;
            }
            else
            {
                $C[$i] = pow(($C[$i] + 0.055) / 1.055, 2.4);
            }
        }

        $L = 0.2126 * $C[0] + 0.7152 * $C[1] + 0.0722 * $C[2];

        if($L > 0.179)
        {
            $color = 'black';
        }
        else
        {
            $color = 'white';
        }

        return $color;
    }

    public static function delete_directory($dir)
    {
        if(!file_exists($dir))
        {
            return true;
        }
        if(!is_dir($dir))
        {
            return unlink($dir);
        }
        foreach(scandir($dir) as $item)
        {
            if($item == '.' || $item == '..')
            {
                continue;
            }
            if(!self::delete_directory($dir . DIRECTORY_SEPARATOR . $item))
            {
                return false;
            }
        }

        return rmdir($dir);
    }

    public static function get_messenger_packages_migration()
    {
        $totalMigration = 0;
        $messengerPath  = glob(base_path() . '/vendor/munafio/chatify/database/migrations' . DIRECTORY_SEPARATOR . '*.php');
        if(!empty($messengerPath))
        {
            $messengerMigration = str_replace('.php', '', $messengerPath);
            $totalMigration     = count($messengerMigration);
        }

        return $totalMigration;

    }

    // Email Template Modules Function START
    // Common Function That used to send mail with check all cases
    public static function sendEmailTemplate($emailTemplate, $mailTo, $obj)
    {
        $usr = \Auth::user();

        //Remove Current Login user Email don't send mail to them
        unset($mailTo[$usr->id]);

        $mailTo = array_values($mailTo);

        if($usr->type != 'super admin')
        {
            // find template is exist or not in our record
            $template = EmailTemplate::where('slug', $emailTemplate)->first();

            if(isset($template) && !empty($template))
            {
                // check template is active or not by company
                $is_active = UserEmailTemplate::where('template_id', '=', $template->id)->where('user_id', '=', $usr->creatorId())->first();

                if($is_active->is_active == 1)
                {
                    $settings = self::settings();

                    // get email content language base
                    $content = EmailTemplateLang::where('parent_id', '=', $template->id)->where('lang', 'LIKE', $usr->lang)->first();

                    $content->from = $template->from;
                    if(!empty($content->content))
                    {

                        $content->content = self::replaceVariable($content->content, $obj);
                        // send email
                        try
                        {
                            Mail::to($mailTo)->send(new CommonEmailTemplate($content, $settings));
                        }
                        catch(\Exception $e)
                        {

                            $error = __('E-Mail has been not sent due to SMTP configuration');
                        }

                        if(isset($error))
                        {
                            $arReturn = [
                                'is_success' => false,
                                'error' => $error,
                            ];
                        }
                        else
                        {
                            $arReturn = [
                                'is_success' => true,
                                'error' => false,
                            ];
                        }
                    }
                    else
                    {
                        $arReturn = [
                            'is_success' => false,
                            'error' => __('Mail not send, email is empty'),
                        ];
                    }

                    return $arReturn;
                }
                else
                {
                    return [
                        'is_success' => true,
                        'error' => false,
                    ];
                }
            }
            else
            {
                return [
                    'is_success' => false,
                    'error' => __('Mail not send, email not found'),
                ];
            }
        }
    }

    // used for replace email variable (parameter 'template_name','id(get particular record by id for data)')
    public static function replaceVariable($content, $obj)
    {
        $arrVariable = [
            '{lead_name}',
            '{lead_email}',
            '{lead_subject}',
            '{lead_pipeline}',
            '{lead_stage}',
            '{deal_name}',
            '{deal_pipeline}',
            '{deal_stage}',
            '{deal_status}',
            '{deal_price}',
            '{estimation_id}',
            '{estimation_client}',
            '{estimation_category}',
            '{estimation_issue_date}',
            '{estimation_expiry_date}',
            '{estimation_status}',
            '{project_title}',
            '{project_category}',
            '{project_price}',
            '{project_client}',
            '{project_assign_user}',
            '{project_start_date}',
            '{project_due_date}',
            '{project_lead}',
            '{project}',
            '{task_title}',
            '{task_priority}',
            '{task_start_date}',
            '{task_due_date}',
            '{task_stage}',
            '{task_assign_user}',
            '{task_description}',
            '{invoice_id}',
            '{invoice_client}',
            '{invoice_issue_date}',
            '{invoice_due_date}',
            '{invoice_status}',
            '{invoice_total}',
            '{invoice_sub_total}',
            '{invoice_due_amount}',
            '{payment_total}',
            '{payment_date}',
            '{credit_note_date}',
            '{credit_amount}',
            '{credit_description}',
            '{support_title}',
            '{assign_user}',
            '{support_priority}',
            '{support_end_date}',
            '{support_description}',
            '{contract_subject}',
            '{contract_client}',
            '{contract_value}',
            '{contract_start_date}',
            '{contract_end_date}',
            '{contract_description}',
            '{app_name}',
            '{company_name}',
            '{app_url}',
            '{email}',
            '{password}',
        ];
        $arrValue    = [
            'lead_name' => '-',
            'lead_email' => '-',
            'lead_subject' => '-',
            'lead_pipeline' => '-',
            'lead_stage' => '-',
            'deal_name' => '-',
            'deal_pipeline' => '-',
            'deal_stage' => '-',
            'deal_status' => '-',
            'deal_price' => '-',
            'estimation_id' => '-',
            'estimation_client' => '-',
            'estimation_category' => '-',
            'estimation_issue_date' => '-',
            'estimation_expiry_date' => '-',
            'estimation_status' => '-',
            'project_title' => '-',
            'project_category' => '-',
            'project_price' => '-',
            'project_client' => '-',
            'project_assign_user' => '-',
            'project_start_date' => '-',
            'project_due_date' => '-',
            'project_lead' => '-',
            'project' => '-',
            'task_title' => '-',
            'task_priority' => '-',
            'task_start_date' => '-',
            'task_due_date' => '-',
            'task_stage' => '-',
            'task_assign_user' => '-',
            'task_description' => '-',
            'invoice_id' => '-',
            'invoice_client' => '-',
            'invoice_issue_date' => '-',
            'invoice_due_date' => '-',
            'invoice_status' => '-',
            'invoice_total' => '-',
            'invoice_sub_total' => '-',
            'invoice_due_amount' => '-',
            'payment_total' => '-',
            'payment_date' => '-',
            'credit_note_date' => '-',
            'credit_amount' => '-',
            'credit_description' => '-',
            'support_title' => '-',
            'assign_user' => '-',
            'support_priority' => '-',
            'support_end_date' => '-',
            'support_description' => '-',
            'contract_subject' => '-',
            'contract_client' => '-',
            'contract_value' => '-',
            'contract_start_date' => '-',
            'contract_end_date' => '-',
            'contract_description' => '-',
            'app_name' => '-',
            'company_name' => '-',
            'app_url' => '-',
            'email' => '-',
            'password' => '-',
        ];

        foreach($obj as $key => $val)
        {
            $arrValue[$key] = $val;
        }

        $arrValue['app_name']     = env('APP_NAME');
        $arrValue['company_name'] = self::settings()['company_name'];
        $arrValue['app_url']      = '<a href="' . env('APP_URL') . '" target="_blank">' . env('APP_URL') . '</a>';

        return str_replace($arrVariable, array_values($arrValue), $content);
    }

    // Make Entry in email_tempalte_lang table when create new language
    public static function makeEmailLang($lang)
    {
        $template = EmailTemplate::all();
        foreach($template as $t)
        {
            $default_lang                 = EmailTemplateLang::where('parent_id', '=', $t->id)->where('lang', 'LIKE', 'en')->first();
            $emailTemplateLang            = new EmailTemplateLang();
            $emailTemplateLang->parent_id = $t->id;
            $emailTemplateLang->lang      = $lang;
            $emailTemplateLang->subject   = $default_lang->subject;
            $emailTemplateLang->content   = $default_lang->content;
            $emailTemplateLang->save();
        }
    }
    // Email Template Modules Function END

    public static function add_landing_page_data()
    {
        $section_data   = [];
        $section_data[] = [
            'section_name' => 'section-1',
            'section_order' => 1,
            'default_content' => '{"logo":"landing_logo.png","image":"top-banner.png","button":{"text":"Login"},"menu":[{"menu":"Features","href":"#"},{"menu":"Pricing","href":"#"}],"text":{"text-1":"CRMGo","text-2":"Projects, Accounting, Leads, Deals & HRM Tool","text-3":"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.","text-4":"get started - its free","text-5":"no creadit card reqired "},"custom_class_name":""}',
            'content' => '{"logo":"landing_logo.png","image":"top-banner.png","button":{"text":"Login"},"menu":[{"menu":"Features","href":"#"},{"menu":"Pricing","href":"#"}],"text":{"text-1":"CRMGo","text-2":"Projects, Accounting, Leads, Deals & HRM Tool","text-3":"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.","text-4":"get started - its free","text-5":"no creadit card reqired"},"custom_class_name":""}',
            'section_demo_image' => 'top-header-section.png',
            'section_blade_file_name' => 'custome-top-header-section',
            'section_type' => 'section-1',
        ];
        $section_data[] = [
            'section_name' => 'section-2',
            'section_order' => 2,
            'default_content' => '{"image":"cal-sec.png","button":{"text":"try our system","href":"#"},"text":{"text-1":"Features","text-2":"Lorem Ipsum is simply dummy","text-3":"text of the printing","text-4":"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting"},"image_array":[{"id":1,"image":"nexo.png"},{"id":2,"image":"edge.png"},{"id":3,"image":"atomic.png"},{"id":4,"image":"brd.png"},{"id":5,"image":"trust.png"},{"id":6,"image":"keep-key.png"},{"id":7,"image":"atomic.png"},{"id":8,"image":"edge.png"}],"custom_class_name":""}',
            'content' => '{"image":"cal-sec.png","button":{"text":"try our system","href":"#"},"text":{"text-1":"Features","text-2":"Lorem Ipsum is simply dummy","text-3":"text of the printing","text-4":"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting"},"image_array":[{"id":1,"image":"nexo.png"},{"id":2,"image":"edge.png"},{"id":3,"image":"atomic.png"},{"id":4,"image":"brd.png"},{"id":5,"image":"trust.png"},{"id":6,"image":"keep-key.png"},{"id":7,"image":"atomic.png"},{"id":8,"image":"edge.png"}],"custom_class_name":""}',
            'section_demo_image' => 'logo-part-main-back-part.png',
            'section_blade_file_name' => 'custome-logo-part-main-back-part',
            'section_type' => 'section-2',
        ];
        $section_data[] = [
            'section_name' => 'section-3',
            'section_order' => 3,
            'default_content' => '{"image": "sec-2.png","button": {"text": "try our system","href": "#"},"text": {"text-1": "Features","text-2": "Lorem Ipsum is simply dummy","text-3": "text of the printing","text-4": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting"},"custom_class_name":""}',
            'section_demo_image' => 'simple-sec-even.png',
            'section_blade_file_name' => 'custome-simple-sec-even',
            'section_type' => 'section-3',
        ];
        $section_data[] = [
            'section_name' => 'section-4',
            'section_order' => 4,
            'default_content' => '{"image": "sec-3.png","button": {"text": "try our system","href": "#"},"text": {"text-1": "Features","text-2": "Lorem Ipsum is simply dummy","text-3": "text of the printing","text-4": "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting"},"custom_class_name":""}',
            'section_demo_image' => 'simple-sec-odd.png',
            'section_blade_file_name' => 'custome-simple-sec-odd',
            'section_type' => 'section-4',
        ];
        $section_data[] = [
            'section_name' => 'section-5',
            'section_order' => 5,
            'default_content' => '{"button": {"text": "TRY OUR SYSTEM","href": "#"},"text": {"text-1": "See more features","text-2": "All Features","text-3": "in one place","text-4": "Attractive Dashboard Customer & Vendor Login Multi Languages","text-5":"Invoice, Billing & Transaction Multi User & Permission Paypal & Stripe for Invoice User Friendly Invoice Theme Make your own setting","text-6":"Multi User & Permission Paypal & Stripe for Invoice User Friendly Invoice Theme Make your own setting","text-7":"Multi User & Permission Paypal & Stripe for Invoice User Friendly Invoice Theme Make your own setting User Friendly Invoice Theme Make your own setting","text-8":"Multi User & Permission Paypal & Stripe for Invoice"},"custom_class_name":""}',
            'section_demo_image' => 'features-inner-part.png',
            'section_blade_file_name' => 'custome-features-inner-part',
            'section_type' => 'section-5',
        ];
        $section_data[] = [
            'section_name' => 'section-6',
            'section_order' => 6,
            'default_content' => '{"system":[{"id":1,"name":"Dashboard","data":[{"data_id":1,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"},{"data_id":2,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-2.png"},{"data_id":3,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-3.png"},{"data_id":4,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"},{"data_id":5,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-2.png"}]},{"id":2,"name":"Functions","data":[{"data_id":1,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"},{"data_id":2,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-2.png"},{"data_id":3,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-3.png"}]},{"id":3,"name":"Reports","data":[{"data_id":1,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"},{"data_id":2,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-2.png"}]},{"id":4,"name":"Tables","data":[{"data_id":1,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"},{"data_id":2,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-2.png"},{"data_id":3,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-3.png"},{"data_id":4,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"}]},{"id":5,"name":"Settings","data":[{"data_id":1,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"},{"data_id":2,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-2.png"}]},{"id":6,"name":"Contact","data":[{"data_id":1,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"}]}],"custom_class_name":""}',
            'content' => '{"system":[{"id":1,"name":"Dashboard","data":[{"data_id":1,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"},{"data_id":2,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-2.png"},{"data_id":3,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-3.png"},{"data_id":4,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"},{"data_id":5,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-2.png"}]},{"id":2,"name":"Functions","data":[{"data_id":1,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"},{"data_id":2,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-2.png"},{"data_id":3,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-3.png"}]},{"id":3,"name":"Reports","data":[{"data_id":1,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"},{"data_id":2,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-2.png"}]},{"id":4,"name":"Tables","data":[{"data_id":1,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"},{"data_id":2,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-2.png"},{"data_id":3,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-3.png"},{"data_id":4,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"}]},{"id":5,"name":"Settings","data":[{"data_id":1,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"},{"data_id":2,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-2.png"}]},{"id":6,"name":"Contact","data":[{"data_id":1,"text":{"text_1":"Dashboard","text_2":"Main Page"},"button":{"text":"LIVE DEMO","href":"#"},"image":"tab-1.png"}]}],"custom_class_name":""}',
            'section_demo_image' => 'container-our-system-div.png',
            'section_blade_file_name' => 'custome-container-our-system-div',
            'section_type' => 'section-6',
        ];
        $section_data[] = [
            'section_name' => 'section-7',
            'section_order' => 7,
            'default_content' => '{"testimonials":[{"id":1,"text":{"text_1":"We have been building AI projects for a long time and we decided it was time to build a platform that can streamline the broken process that we had to put up with. Here are some of the key things we wish we had when we were building projects before.","text_2":"Lorem Ipsum","text_3":"Founder and CEO at Rajodiya Infotech"},"image":"testimonials-img.png"},{"id":2,"text":{"text_1":"We have been building AI projects for a long time and we decided it was time to build a platform that can streamline the broken process that we had to put up with. Here are some of the key things we wish we had when we were building projects before.","text_2":"Lorem Ipsum","text_3":"Founder and CEO at Rajodiya Infotech"},"image":"testimonials-img.png"},{"id":3,"text":{"text_1":"We have been building AI projects for a long time and we decided it was time to build a platform that can streamline the broken process that we had to put up with. Here are some of the key things we wish we had when we were building projects before.","text_2":"Lorem Ipsum","text_3":"Founder and CEO at Rajodiya Infotech"},"image":"testimonials-img.png"},{"id":4,"text":{"text_1":"We have been building AI projects for a long time and we decided it was time to build a platform that can streamline the broken process that we had to put up with. Here are some of the key things we wish we had when we were building projects before.","text_2":"Lorem Ipsum","text_3":"Founder and CEO at Rajodiya Infotech"},"image":"testimonials-img.png"},{"id":5,"text":{"text_1":"We have been building AI projects for a long time and we decided it was time to build a platform that can streamline the broken process that we had to put up with. Here are some of the key things we wish we had when we were building projects before.","text_2":"Lorem Ipsum","text_3":"Founder and CEO at Rajodiya Infotech"},"image":"testimonials-img.png"}],"custom_class_name":""}',
            'section_demo_image' => 'testimonials-section.png',
            'section_blade_file_name' => 'custome-testimonials-section',
            'section_type' => 'section-7',
        ];

        $section_data[] = [
            'section_name' => 'section-8',
            'section_order' => 9,
            'default_content' => '{"button":{"text":"Subscribe"},"text":{"text-1":"Try for free","text-2":"Lorem Ipsum is simply dummy text","text-3":"of the printing and typesetting industry","text-4":"Type your email address and click the button"},"custom_class_name":""}',
            'content' => '{"button":{"text":"Subscribe"},"text":{"text-1":"Try for free","text-2":"Lorem Ipsum is simply dummy text","text-3":"of the printing and typesetting industry","text-4":"Type your email address and click the button"},"custom_class_name":""}',
            'section_demo_image' => 'subscribe-part.png',
            'section_blade_file_name' => 'custome-subscribe-part',
            'section_type' => 'section-8',
        ];
        $section_data[] = [
            'section_name' => 'section-9',
            'section_order' => 10,
            'default_content' => '{"menu":[{"menu":"Facebook","href":"#"},{"menu":"LinkedIn","href":"#"},{"menu":"Twitter","href":"#"},{"menu":"Discord","href":"#"}],"custom_class_name":""}',
            'content' => '{"menu":[{"menu":"Facebook","href":"#"},{"menu":"LinkedIn","href":"#"},{"menu":"Twitter","href":"#"},{"menu":"Discord","href":"#"}],"custom_class_name":""}',
            'section_demo_image' => 'social-links.png',
            'section_blade_file_name' => 'custome-social-links',
            'section_type' => 'section-9',
        ];
        $section_data[] = [
            'section_name' => 'section-10',
            'section_order' => 11,
            'default_content' => '{"footer":{"logo":{"logo":"landing_logo.png"},"footer_menu":[{"id":1,"menu":"FIO Protocol","data":[{"menu_name":"Feature","menu_href":"#"},{"menu_name":"Download","menu_href":"#"},{"menu_name":"Integration","menu_href":"#"},{"menu_name":"Extras","menu_href":"#"}]},{"id":2,"menu":"Learn","data":[{"menu_name":"Feature","menu_href":"#"},{"menu_name":"Download","menu_href":"#"},{"menu_name":"Integration","menu_href":"#"},{"menu_name":"Extras","menu_href":"#"}]},{"id":3,"menu":"Foundation","data":[{"menu_name":"About Us","menu_href":"#"},{"menu_name":"Customers","menu_href":"#"},{"menu_name":"Resources","menu_href":"#"},{"menu_name":"Blog","menu_href":"#"}]}],"contact_app":[{"menu":"Contact","data":[{"id":1,"image":"app-store.png","image_href":"#"},{"id":2,"image":"google-pay.png","image_href":"#"}]}],"bottom_menu":{"text":"All rights reserved.","data":[{"menu_name":"Privacy Policy","menu_href":"#"},{"menu_name":"Github","menu_href":"#"},{"menu_name":"Press Kit","menu_href":"#"},{"menu_name":"Contact","menu_href":"#"}]}},"custom_class_name":""}',
            'content' => '{"footer":{"logo":{"logo":"landing_logo.png"},"footer_menu":[{"id":1,"menu":"FIO Protocol","data":[{"menu_name":"Feature","menu_href":"#"},{"menu_name":"Download","menu_href":"#"},{"menu_name":"Integration","menu_href":"#"},{"menu_name":"Extras","menu_href":"#"}]},{"id":2,"menu":"Learn","data":[{"menu_name":"Feature","menu_href":"#"},{"menu_name":"Download","menu_href":"#"},{"menu_name":"Integration","menu_href":"#"},{"menu_name":"Extras","menu_href":"#"}]},{"id":3,"menu":"Foundation","data":[{"menu_name":"About Us","menu_href":"#"},{"menu_name":"Customers","menu_href":"#"},{"menu_name":"Resources","menu_href":"#"},{"menu_name":"Blog","menu_href":"#"}]}],"contact_app":[{"menu":"Contact","data":[{"id":1,"image":"app-store.png","image_href":"#"},{"id":2,"image":"google-pay.png","image_href":"#"}]}],"bottom_menu":{"text":"All rights reserved.","data":[{"menu_name":"Privacy Policy","menu_href":"#"},{"menu_name":"Github","menu_href":"#"},{"menu_name":"Press Kit","menu_href":"#"},{"menu_name":"Contact","menu_href":"#"}]}},"custom_class_name":""}',
            'section_demo_image' => 'footer-section.png',
            'section_blade_file_name' => 'custome-footer-section',
            'section_type' => 'section-10',
        ];


        foreach($section_data as $section_key => $section_value)
        {

            LandingPageSection::create($section_value);
        }

        return true;
    }

    public static function getCompanyPaymentSetting()
    {
        $data     = \DB::table('company_payment_settings');
        $settings = [];
        if(\Auth::check())
        {
            $user_id = \Auth::user()->creatorId();
            $data    = $data->where('created_by', '=', $user_id);

        }
        $data = $data->get();
        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function error_res($msg = "", $args = array())
    {
        $msg       = $msg == "" ? "error" : $msg;
        $msg_id    = 'error.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg       = $msg_id == $converted ? $msg : $converted;
        $json      = array(
            'flag' => 0,
            'msg' => $msg,
        );

        return $json;
    }

    public static function success_res($msg = "", $args = array())
    {
        $msg       = $msg == "" ? "success" : $msg;
        $msg_id    = 'success.' . $msg;
        $converted = \Lang::get($msg_id, $args);
        $msg       = $msg_id == $converted ? $msg : $converted;
        $json      = array(
            'flag' => 1,
            'msg' => $msg,
        );

        return $json;
    }
    public static function getselectedThemeColor(){
        $color = env('THEME_COLOR');
        if($color == "" || $color == null){
            $color = 'blue';
        }
        return $color;
    }
    public static function getAllThemeColors(){
        $colors = [
            'blue','denim','sapphire','olympic','violet','black','cyan','dark-blue-natural','gray','light-blue','light-purple','magenta','orange-mute','pale-green','rich-magenta','rich-red','sky-gray'
        ];
        return $colors;
    }

    public static $chartOfAccountType = [
        'assets' => 'Assets',
        'liabilities' => 'Liabilities',
        'expenses' => 'Expenses',
        'income' => 'Income',
        'equity' => 'Equity',
    ];


    public static $chartOfAccountSubType = array(
        "assets" => array(
            '1' => 'Current Asset',
            '2' => 'Fixed Asset',
            '3' => 'Inventory',
            '4' => 'Non-current Asset',
            '5' => 'Prepayment',
            '6' => 'Bank & Cash',
            '7' => 'Depreciation',
        ),
        "liabilities" => array(
            '1' => 'Current Liability',
            '2' => 'Liability',
            '3' => 'Non-current Liability',
        ),
        "expenses" => array(
            '1' => 'Direct Costs',
            '2' => 'Expense',
        ),
        "income" => array(
            '1' => 'Revenue',
            '2' => 'Sales',
            '3' => 'Other Income',
        ),
        "equity" => array(
            '1' => 'Equity',
        ),

    );

    public static function chartOfAccountTypeData()
    {
        $chartOfAccountTypes = Self::$chartOfAccountType;
        foreach($chartOfAccountTypes as $k => $type)
        {

            $accountType = ChartOfAccountType::create(
                [
                    'name' => $type,
                    'created_by' => 1,
                ]
            );

            $chartOfAccountSubTypes = Self::$chartOfAccountSubType;

            foreach($chartOfAccountSubTypes[$k] as $subType)
            {
                ChartOfAccountSubType::create(
                    [
                        'name' => $subType,
                        'type' => $accountType->id,
                    ]
                );
            }
        }
    }

    public static $chartOfAccount = array(

        [
            'code' => '120',
            'name' => 'Accounts Receivable',
            'type' => 1,
            'sub_type' => 1,
        ],
        [
            'code' => '160',
            'name' => 'Computer Equipment',
            'type' => 1,
            'sub_type' => 2,
        ],
        [
            'code' => '150',
            'name' => 'Office Equipment',
            'type' => 1,
            'sub_type' => 2,
        ],
        [
            'code' => '140',
            'name' => 'Inventory',
            'type' => 1,
            'sub_type' => 3,
        ],
        [
            'code' => '857',
            'name' => 'Budget - Finance Staff',
            'type' => 1,
            'sub_type' => 6,
        ],
        [
            'code' => '170',
            'name' => 'Accumulated Depreciation',
            'type' => 1,
            'sub_type' => 7,
        ],
        [
            'code' => '200',
            'name' => 'Accounts Payable',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '205',
            'name' => 'Accruals',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '150',
            'name' => 'Office Equipment',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '855',
            'name' => 'Clearing Account',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '235',
            'name' => 'Employee Benefits Payable',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '236',
            'name' => 'Employee Deductions payable',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '255',
            'name' => 'Historical Adjustments',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '835',
            'name' => 'Revenue Received in Advance',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '260',
            'name' => 'Rounding',
            'type' => 2,
            'sub_type' => 8,
        ],
        [
            'code' => '500',
            'name' => 'Costs of Goods Sold',
            'type' => 3,
            'sub_type' => 11,
        ],
        [
            'code' => '600',
            'name' => 'Advertising',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '644',
            'name' => 'Automobile Expenses',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '684',
            'name' => 'Bad Debts',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '810',
            'name' => 'Bank Revaluations',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '605',
            'name' => 'Bank Service Charges',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '615',
            'name' => 'Consulting & Accounting',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '700',
            'name' => 'Depreciation',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '628',
            'name' => 'General Expenses',
            'type' => 3,
            'sub_type' => 12,
        ],
        [
            'code' => '460',
            'name' => 'Interest Income',
            'type' => 4,
            'sub_type' => 13,
        ],
        [
            'code' => '470',
            'name' => 'Other Revenue',
            'type' => 4,
            'sub_type' => 13,
        ],
        [
            'code' => '475',
            'name' => 'Purchase Discount',
            'type' => 4,
            'sub_type' => 13,
        ],
        [
            'code' => '400',
            'name' => 'Sales',
            'type' => 4,
            'sub_type' => 13,
        ],
        [
            'code' => '330',
            'name' => 'Common Stock',
            'type' => 5,
            'sub_type' => 16,
        ],
        [
            'code' => '300',
            'name' => 'Owners Contribution',
            'type' => 5,
            'sub_type' => 16,
        ],
        [
            'code' => '310',
            'name' => 'Owners Draw',
            'type' => 5,
            'sub_type' => 16,
        ],
        [
            'code' => '320',
            'name' => 'Retained Earnings',
            'type' => 5,
            'sub_type' => 16,
        ],
    );

    public static function chartOfAccountData($user)
    {
        $chartOfAccounts = Self::$chartOfAccount;
        foreach($chartOfAccounts as $account)
        {
            ChartOfAccount::create(
                [
                    'code' => $account['code'],
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'sub_type' => $account['sub_type'],
                    'is_enabled' => 1,
                    'created_by' => $user->id,
                ]
            );

        }
    }

    // get progress bar color
    public static function getProgressColor($percentage)
    {
        $color = '';

        if($percentage <= 20)
        {
            $color = 'danger';
        }
        elseif($percentage > 20 && $percentage <= 40)
        {
            $color = 'warning';
        }
        elseif($percentage > 40 && $percentage <= 60)
        {
            $color = 'info';
        }
        elseif($percentage > 60 && $percentage <= 80)
        {
            $color = 'primary';
        }
        elseif($percentage >= 80)
        {
            $color = 'success';
        }

        return $color;
    }


    public static function ownerIdforInvoice($id){
        $user = User::where(['id' => $id])->first();
        if(!is_null($user)){
            if($user->type == "company"){
                return $user->id;
            }else{
                $user1 = User::where(['id' => $user->created_by,$user->type => 'company'])->first();
                if(!is_null($user1)){
                    return $user->id;
                }
            }
        }
        return 0;
    }
    public static function getAdminPaymentSetting()
    {
        $data     = \DB::table('company_payment_settings');
        $settings = [];
        if(\Auth::check())
        {
            $user_id = 1;
            $data    = $data->where('created_by', '=', $user_id);

        }
        $data = $data->get();
        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }

    public static function invoice_payment_settings($id)
        {
            $data = [];

            $user = User::where(['id' => $id])->first();
            if(!is_null($user)){
                $data = DB::table('company_payment_settings');
                $data->where('created_by', '=', 1);
                $data = $data->get();
                //dd($data);
            }

            $res = [];

            foreach ($data as $key => $value) {
                $res[$value->name] = $value->value;
            }

            return $res;
        }

         public static function getNonAuthCompanyPaymentSetting($id)
        {
            $data     = \DB::table('company_payment_settings');
            $settings = [];
            $data    = $data->where('created_by', '=', $id);
            $data = $data->get();
            foreach($data as $row)
            {
                $settings[$row->name] = $row->value;
            }

            return $settings;
        }

        public static function second_to_time($seconds = 0)
        {
            $H = floor($seconds / 3600);
            $i = ($seconds / 60) % 60;
            $s = $seconds % 60;

            $time = sprintf("%02d:%02d:%02d", $H, $i, $s);

            return $time;
        }

        public static function diffance_to_time($start, $end)
    {
        $start         = new Carbon($start);
        $end           = new Carbon($end);
        $totalDuration = $start->diffInSeconds($end);

        return $totalDuration;
    }

    public static function send_slack_msg($msg) {

        $settings  = Utility::settings();
        try{
            if(isset($settings['slack_webhook']) && !empty($settings['slack_webhook'])){

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL, $settings['slack_webhook']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['text' => $msg]));

                    $headers = array();
                    $headers[] = 'Content-Type: application/json';
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                    $result = curl_exec($ch);
                    if (curl_errno($ch)) {
                        echo 'Error:' . curl_error($ch);
                    }
                    curl_close($ch);

                }
            }
            catch(\Exception $e){

            }

            }

         public static function send_telegram_msg($resp) {
            $settings  = Utility::settings();

            try{
            $msg = $resp;

            // Set your Bot ID and Chat ID.
            $telegrambot    = $settings['telegrambot'];
            $telegramchatid = $settings['telegramchatid'];

            // Function call with your own text or variable
            $url     = 'https://api.telegram.org/bot' . $telegrambot . '/sendMessage';
            $data    = array(
                'chat_id' => $telegramchatid,
                'text' => $msg,
            );

            $options = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-Type:application/x-www-form-urlencoded\r\n",
                    'content' => http_build_query($data),
                ),
            );

            $context = stream_context_create($options);

            $result  = file_get_contents($url, false, $context);
            $url     = $url;
        }
        catch(\Exception $e){

        }
    }
    public static function send_twilio_msg($to, $msg)
    {

        $settings  = Utility::settings();
        $account_sid    = $settings['twilio_sid'];
        $auth_token = $settings['twilio_token'];
        $twilio_number = $settings['twilio_from'];
        $client = new Client($account_sid, $auth_token);
        $client->messages->create($to, [
            'from' => $twilio_number,
            'body' => $msg]);

        //  dd('SMS Sent Successfully.');

    }

    public static function total_quantity($type, $quantity, $id)
    {
        $product = Item::find($id);

        $pro_quantity = $product->quantity;

        if($type == 'minus')
        {
            $product->quantity = $pro_quantity - $quantity;
        }
        else
        {
            $product->quantity = $pro_quantity + $quantity;
        }
        $product->save();
    }


    //add quantity in product stock
    public static function addProductStock($product_id, $quantity, $type, $description,$type_id)
    {

        $stocks             = new StockReport();
        $stocks->product_id = $product_id;
        $stocks->quantity   = $quantity;
        $stocks->type = $type;
        $stocks->type_id = $type_id;
        $stocks->description = $description;
        $stocks->created_by =\Auth::user()->creatorId();

        $stocks->save();
    }


    public static function colorset(){
        if(\Auth::user())
        {
            if(\Auth::user()->type == 'super admin')
            {
                $user = \Auth::user();
                $setting = DB::table('settings')->where('created_by',$user->id)->pluck('value','name')->toArray();
            }
            else
            {
                $setting = DB::table('settings')->where('created_by', \Auth::user()->creatorId())->pluck('value','name')->toArray();
            }
        }
        else
        {
            $user = User::where('type','company')->first();
            $setting = DB::table('settings')->where('created_by',$user->id)->pluck('value','name')->toArray();
        }
        return $setting;

        $is_dark_mode = $setting['cust_darklayout'];

        if($is_dark_mode == 'on'){
            return 'logo-light.png';
        }else{
            return 'logo-dark.png';
        }
    }

    public static function get_superadmin_logo(){

        $is_dark_mode = DB::table('settings')->where('created_by', '1')->pluck('value','name')->toArray();
        if(!empty($is_dark_mode['cust_darklayout'])){
            $is_dark_modes = $is_dark_mode['cust_darklayout'];

            if($is_dark_modes == 'on'){
                return 'logo-light.png';
            }else{
                return 'logo-dark.png';
            }
        }
        else{
            return 'logo-dark.png';
        }
    }

    public static function mode_layout()
    {
        $data = DB::table('settings');
        $data = $data->where('created_by', '=', 1);
        $data     = $data->get();
        $settings = [
            "cust_darklayout" => "off",
            "cust_theme_bg" => "off",
            'color' => 'theme-3'
        ];
        foreach($data as $row)
        {
            $settings[$row->name] = $row->value;
        }

        return $settings;
    }



}
