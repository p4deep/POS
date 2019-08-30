<?php

return [
	
	 /*
    |--------------------------------------------------------------------------
    | App Constants
    |--------------------------------------------------------------------------
    |List of all constants for the app
    */

    'langs' => [
    	'en' => 'English',
        'es' => 'Español - Spanish',
        'sq' => 'Shqip - Albanian',
        'hi' => 'हिंदी - Hindi',
        'nl' => 'Dutch',
        'fr' => 'Français - French',
        'de' => 'Deutsch - German',
        'ar' => 'Arabic - العَرَبِيَّة'
    ],

    'langs_rtl' => ['ar'],
    
    'document_size_limit' => '1000000', //in Bytes,

    'asset_version' => 19,

    'disable_expiry' => false,

    'disable_purchase_in_other_currency' => true,
    
    'iraqi_selling_price_adjustment' => false,

    'currency_precision' => 2,

    'product_img_path' => 'public/img',

    'image_size_limit' => '500000', //in Bytes
];
