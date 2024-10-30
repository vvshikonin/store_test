<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\LegalEntityResource;
use App\Models\V1\LegalEntity;
use Illuminate\Support\Facades\Cache;

class LegalEntityController extends Controller
{
    public function index()
    {
        return LegalEntityResource::collection(
            Cache::remember('legal_entities_all', config('cache.time'), function () {
                return LegalEntity::all();
            })
        );
    }
}
