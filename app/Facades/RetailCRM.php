<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class RetailCRM extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'retail-crm-service';
    }
}
