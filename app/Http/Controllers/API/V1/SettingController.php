<?php

namespace App\Http\Controllers\API\V1;

use App\Models\V1\Setting;
use App\Http\Resources\V1\SettingResource;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::select('*')->get();
        return SettingResource::collection($settings);
    }
}
