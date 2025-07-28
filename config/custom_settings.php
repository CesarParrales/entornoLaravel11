<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Partner Discount Percentage
    |--------------------------------------------------------------------------
    |
    | This value represents the discount percentage applied to the PVP (Precio
    | Venta Público) to calculate the PVS (Precio Venta Socio).
    | For example, 0.25 means a 25% discount.
    | This should eventually be configurable from the admin panel.
    |
    */
    'partner_discount_percentage' => 0.25, // 25%

    /*
    |--------------------------------------------------------------------------
    | VAT (Value Added Tax) Rate
    |--------------------------------------------------------------------------
    |
    | The standard VAT rate to be applied to products.
    | For example, 0.15 means 15%.
    | This should eventually be configurable from the admin panel,
    | and the system might need to support multiple tax rates or types.
    |
    */
    'vat_rate' => 0.15, // 15% for Ecuador

    /*
    |--------------------------------------------------------------------------
    | Other Custom Settings
    |--------------------------------------------------------------------------
    |
    | You can add other global custom settings for the application here.
    |
    */
];