<?php

namespace App\Http\Controllers;

use App\User,
    App\Business,
    App\TaxRate,
    App\Currency,
    App\Unit,
    Spatie\Permission\Models\Role,
    Spatie\Permission\Models\Permission;

use DateTimeZone;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Utils\BusinessUtil,
    App\Utils\RestaurantUtil;
    // App\Utils\ModuleUtil;

class BusinessController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | BusinessController
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new business/business as well as their
    | validation and creation.
    |
    */

    /**
     * All Utils instance.
     *
     */
    protected $businessUtil;
    protected $restaurantUtil;
    // protected $moduleUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(BusinessUtil $businessUtil, RestaurantUtil $restaurantUtil)
    {
        $this->businessUtil = $businessUtil;
        // $this->moduleUtil = $moduleUtil;

        $this->avlble_modules = array('tables' => [ 'name' => __('restaurant.tables'),
                                                    'tooltip' => __('restaurant.tooltip_tables')] ,
                                    'modifiers' => [ 'name' => __('restaurant.modifiers'),
                                                    'tooltip' => __('restaurant.tooltip_modifiers')] ,
                                    'service_staff' => [ 'name' => __('restaurant.service_staff'),
                                                        'tooltip' => __('restaurant.tooltip_service_staff') ],
                                    'kitchen' => [ 'name' => __('restaurant.kitchen_for_restaurant')]
                                );
    }

    /**
     * Shows registration form
     *
     * @return \Illuminate\Http\Response
     */
    public function getRegister()
    {
        if(!env('ALLOW_REGISTRATION', true)){
            return redirect('/');
        }

        $currencies = $this->businessUtil->allCurrencies();
        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $timezone_list = array();
        foreach ($timezones as $timezone) {
            $timezone_list[$timezone] = $timezone;
        }

        $months = array();
        for ($i=1; $i<=12 ; $i++) { 
            $months[$i] = __( 'business.months.' . $i );
        }

        $accounting_methods = array(
            'fifo' => __( 'business.fifo'),
            'lifo' => __('business.lifo')
        );

        return view ('business.register', compact('currencies', 'timezone_list', 'months', 
            'accounting_methods'));
    }

    /**
     * Handles the registration of a new business and it's owner
     *
     * @return \Illuminate\Http\Response
     */
    public function postRegister(Request $request)
    {
        if(!env('ALLOW_REGISTRATION', true)){
            return redirect('/');
        }
        
        try {
            $validator = $request->validate([
                'name' => 'required|max:255',
                'currency_id' => 'required|numeric',
                'country' => 'required|max:255',
                'state' => 'required|max:255',
                'city' => 'required|max:255',
                'zip_code' => 'required|max:255',
                'landmark' => 'required|max:255',
                'time_zone' => 'required|max:255',
                'surname' => 'max:10',
                'email' => 'sometimes|nullable|email|max:255',
                'first_name' => 'required|max:255',
                'username' => 'required|min:4|max:255|unique:users',
                'password' => 'required|min:4|max:255',
                'fy_start_month' => 'required',
                'accounting_method' => 'required',
            ],
            [
                'name.required' => __('validation.required', ['attribute' => __('business.business_name')]),
                'name.currency_id' => __('validation.required', ['attribute' => __('business.currency')]),
                'country.required' => __('validation.required', ['attribute' => __('business.country')]),
                'state.required' => __('validation.required', ['attribute' => __('business.state')]),
                'city.required' => __('validation.required', ['attribute' => __('business.city')]),
                'zip_code.required' => __('validation.required', ['attribute' => __('business.zip_code')]),
                'landmark.required' => __('validation.required', ['attribute' => __('business.landmark')]),
                'time_zone.required' => __('validation.required', ['attribute' => __('business.time_zone')]),
                'email.email' => __('validation.email', ['attribute' => __('business.email')]),
                'first_name.required' => __('validation.required', ['attribute' => 
                    __('business.first_name')]),
                'username.required' => __('validation.required', ['attribute' => __('business.username')]),
                'username.min' => __('validation.min', ['attribute' => __('business.username')]),
                'password.required' => __('validation.required', ['attribute' => __('business.username')]),
                'password.min' => __('validation.min', ['attribute' => __('business.username')]),
                'fy_start_month.required' => __('validation.required', ['attribute' => __('business.fy_start_month')]),
                'accounting_method.required' => __('validation.required', ['attribute' => __('business.accounting_method')]),
            ]);

            DB::beginTransaction();

            $business_details = $request->only(['name', 'start_date', 'currency_id', 'tax_label_1', 'tax_number_1', 'tax_label_2', 'tax_number_2', 'time_zone', 'accounting_method', 'fy_start_month']);

            $business_location = $request->only(['name', 'country', 'state', 'city', 'zip_code', 'landmark']);

            $owner_details = $request->only(['surname', 'first_name', 'last_name', 'username', 'email', 'password']);

            //Create the user
            $user = User::create_user($owner_details);
            
            //Create the business
            $business_details['owner_id'] = $user->id;
            if (!empty($business_details['start_date'])) {
                $business_details['start_date'] = Carbon::createFromFormat('m/d/Y', $business_details['start_date'])->toDateString();
            }
            
            $business_details['sell_price_tax'] = 'includes';

            $business_details['default_profit_percent'] = 25;
            
            //upload logo
            if ($request->hasFile('business_logo') && $request->file('business_logo')->isValid()) {
                $path = $request->business_logo->store('public/business_logos');
                $business_details['logo'] = str_replace('public/business_logos/', '', $path);
            }
            //Add POS shortcuts
            $business_details['keyboard_shortcuts'] = '{"pos":{"express_checkout":"shift+e","pay_n_ckeckout":"shift+p","draft":"shift+d","cancel":"shift+c","edit_discount":"shift+i","edit_order_tax":"shift+t","add_payment_row":"shift+r","finalize_payment":"shift+f","recent_product_quantity":"f2","add_new_product":"f4"}}';

            //Add prefixes
            $business_details['ref_no_prefixes'] = array(
                'purchase' => 'PO',
                'stock_transfer' => 'ST',
                'stock_adjustment' => 'SA',
                'sell_return' => 'CN',
                'expense' => 'EP',
                'contacts' => 'CO',
                'purchase_payment' => 'PP',
                'sell_payment' => 'SP',
                'business_location' => 'BL'
                );

            //Disable inline tax editing
            $business_details['enable_inline_tax'] = 0;
            
            $business = Business::create_business($business_details);

            //Update user with business id
            $user->business_id = $business->id;
            $user->save();

            //create Admin role and assign to user
            $role = Role::create( [ 'name' => 'Admin#' . $business->id, 
                                'business_id' => $business->id, 
                                'guard_name' => 'web', 'is_default' => 1
                            ]);
            $user->assignRole($role->name);

            //Create Cashier role for a new business
            $cashier_role = Role::create( [ 'name' => 'Cashier#' . $business->id, 
                                'business_id' => $business->id, 
                                'guard_name' => 'web'
                            ]);
            $cashier_role->syncPermissions(['sell.view', 'sell.create', 'sell.update', 'sell.delete', 'access_all_locations']);


            $this->businessUtil->newBusinessDefaultResources($business->id, $user->id);
            $new_location = $this->businessUtil->addLocation($business->id, $business_location);

            //create new permission with the new location
            Permission::create(['name' => 'location.' . $new_location->id ]);

            DB::commit();

            $output = array('success' => 1, 
                            'msg' => __('business.business_created_succesfully')
                        );

            return redirect('login')->with('status', $output);

        } catch(\Exception $e){
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = array('success' => 0, 
                            'msg' => 'Registration Failed'
                        );

            return back()->with('status', $output)->withInput();
        }
    }
    
    /**
     * Handles the validation username 
     *
     * @return \Illuminate\Http\Response
     */
    public function postCheckUsername(Request $request)
    {
        // $username = $request->input('username');
        // $count = User::where('username', $username)->count();
        // if( $count == 0 ){
            // echo "true"; exit;
        // } else {
            // echo "false"; exit;
        // }
    }
    
    /**
     * Shows business settings form
     *
     * @return \Illuminate\Http\Response
     */
    public function getBusinessSettings()
    {
        if (!auth()->user()->can('business_settings.access') ) {
            abort(403, 'Unauthorized action.');
        }

        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $timezone_list = array();
        foreach ($timezones as $timezone) {
            $timezone_list[$timezone] = $timezone;
        }

        $business_id = request()->session()->get('user.business_id');
        $business = Business::where('id', $business_id)->first();
        
        $currencies = $this->businessUtil->allCurrencies();
        $tax_details = TaxRate::forBusinessDropdown($business_id);
        $tax_rates = $tax_details['tax_rates'];

        $months = array();
        for ($i=1; $i<=12 ; $i++) { 
            $months[$i] = __( 'business.months.' . $i );
        }

        $accounting_methods = array(
                'fifo' => __( 'business.fifo'),
                'lifo' => __('business.lifo')
            );
        $commission_agent_dropdown = array(
                '' => __('lang_v1.disable'),
                'logged_in_user' => __('lang_v1.logged_in_user'),
                'user' => __('lang_v1.select_from_users_list'),
                'cmsn_agnt' => __('lang_v1.select_from_commisssion_agents_list')
            );

        $units_dropdown = Unit::forDropdown($business_id, true);

        $date_formats = array(
            'd-m-Y' => 'dd-mm-yyyy',
            'm-d-Y' => 'mm-dd-yyyy',
            'd/m/Y' => 'dd/mm/yyyy',
            'm/d/Y' => 'mm/dd/yyyy'
        );

        return view ('business.settings', compact('business', 'currencies', 'tax_rates', 'timezone_list', 'months', 'accounting_methods', 'commission_agent_dropdown', 'units_dropdown', 'date_formats'));
    }

    /**
     * Updates business settings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postBusinessSettings(Request $request)
    {
        if (!auth()->user()->can('business_settings.access') ) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            
            $business_details = $request->only(['name', 'start_date', 'currency_id', 'tax_label_1', 'tax_number_1', 'tax_label_2', 'tax_number_2', 'default_profit_percent', 'default_sales_tax', 'default_sales_discount', 'sell_price_tax', 'sku_prefix', 'time_zone', 'fy_start_month', 'accounting_method', 'transaction_edit_days', 'sales_cmsn_agnt', 'item_addition_method', 'currency_symbol_placement', 'on_product_expiry', 
                'stop_selling_before', 'default_unit', 'expiry_type', 'date_format', 'time_format', 'ref_no_prefixes']);

            if (!empty($business_details['start_date'])) {
                $business_details['start_date'] = Carbon::createFromFormat('m/d/Y', $business_details['start_date'])->toDateString();
            }

            if( !empty($request->input('enable_tooltip')) &&  $request->input('enable_tooltip') == 1){
                $business_details['enable_tooltip'] = 1;
            } else {
                $business_details['enable_tooltip'] = 0;
            }

            $business_details['enable_product_expiry'] = !empty($request->input('enable_product_expiry')) &&  $request->input('enable_product_expiry') == 1 ? 1 : 0;
            if($business_details['on_product_expiry'] == 'keep_selling'){
                $business_details['stop_selling_before'] = null;
            }

            $business_details['stock_expiry_alert_days'] = !empty($request->input('stock_expiry_alert_days')) ? $request->input('stock_expiry_alert_days') : 30;

            //Check for Purchase currency
            if( !empty($request->input('purchase_in_diff_currency')) &&  $request->input('purchase_in_diff_currency') == 1){
                $business_details['purchase_in_diff_currency'] = 1;
                $business_details['purchase_currency_id'] = $request->input('purchase_currency_id');
                $business_details['p_exchange_rate'] = $request->input('p_exchange_rate');
            } else {
                $business_details['purchase_in_diff_currency'] = 0;
                $business_details['purchase_currency_id'] = NULL;
                $business_details['p_exchange_rate'] = 1;
            }
            
            if ($request->hasFile('business_logo') && $request->file('business_logo')->isValid()) {
                $path = $request->business_logo->store('public/business_logos');
                $business_details['logo'] = str_replace('public/business_logos/', '', $path);
            }

            $checkboxes = ['enable_editing_product_from_purchase', 'enable_inline_tax', 
                'enable_brand', 'enable_category', 'enable_sub_category', 'enable_price_tax', 'enable_purchase_status', 
                'enable_lot_number', 'enable_racks', 'enable_row', 'enable_position'];
            foreach ($checkboxes as $value) {
                $business_details[$value] = !empty($request->input($value)) &&  $request->input($value) == 1 ? 1 : 0;
            }
            
            $business_id = request()->session()->get('user.business_id');
            $business = Business::where('id', $business_id)->first();

            //Update business settings
            if(!empty($business_details['logo'])){
                $business->logo = $business_details['logo'];
            } else{
                unset($business_details['logo']);
            }

            $business->fill($business_details);
            $business->save();

            //update session data
            $request->session()->put('business', $business);

            //Update Currency details
            $currency = Currency::find($business->currency_id);
            $request->session()->put('currency', [
                        'id' => $currency->id,
                        'code' => $currency->code,
                        'symbol' => $currency->symbol,
                        'thousand_separator' => $currency->thousand_separator,
                        'decimal_separator' => $currency->decimal_separator,
                        ]
                    );
            
            //update current financial year to session
            $financial_year = $this->businessUtil->getCurrentFinancialYear( $business->id );
            $request->session()->put('financial_year', $financial_year);
            
            $output = array('success' => 1, 
                            'msg' => __('business.settings_updated_success')
                        );

        } catch(\Exception $e){
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = array('success' => 0, 
                            'msg' => __('messages.something_went_wrong')
                        );
        }
        return redirect('business/settings')->with('status', $output);
    }

    /**
     * Shows system settings form
     *
     * @return \Illuminate\Http\Response
     */
    public function getSystemSettings()
    {
        if (!auth()->user()->can('business_settings.access') ) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $business = Business::where('id', $business_id)->first();
        
        $shortcuts = json_decode($business->keyboard_shortcuts, true);
        if(empty($business->pos_settings)){
            $pos_settings = $this->businessUtil->defaultPosSettings();
        } else {
            $pos_settings = json_decode($business->pos_settings, true);
        }

        $modules = $this->avlble_modules;

        return view ('business.system_settings')
                ->with(compact('business', 'shortcuts', 'pos_settings', 'modules'));
    }

    /**
     * Updates system settings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postSystemSettings(Request $request)
    {
        if (!auth()->user()->can('business_settings.access') ) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $shortcuts = $request->input('shortcuts');
            $update_data['keyboard_shortcuts'] = json_encode($shortcuts);

            //pos_settings
            $pos_settings = $request->input('pos_settings');
            $default_pos_settings = $this->businessUtil->defaultPosSettings();
            foreach ($default_pos_settings as $key => $value) {
                if(!isset($pos_settings[$key])){
                    $pos_settings[$key] = $value;
                }
            }
            $update_data['pos_settings'] = json_encode($pos_settings);

            //Enabled modules
            $update_data['enabled_modules'] = json_encode($request->input('enabled_modules'));
            $business_id = $request->session()->get('user.business_id');
            $business = Business::where('id', $business_id)->update($update_data);
            
            //update session data
            $business = Business::find($business_id);
            $request->session()->put('business', $business);

            $output = array('success' => 1, 
                            'msg' => __('business.settings_updated_success')
                        );

        } catch(\Exception $e){
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = array('success' => 0, 
                            'msg' => __('messages.something_went_wrong')
                        );
        }
        return redirect('system-settings')->with('status', $output);
    }
}
