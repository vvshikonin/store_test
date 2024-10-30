<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use RetailCrm\Api\Factory\SimpleClientFactory;
use App\Models\V1\Order;
use App\Models\V1\OrderStatusGroup;
use App\Models\V1\OrderStatus;
use App\Facades\RetailCRM;

class FillStatusesAndGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Очистить мусорные записи
        OrderStatusGroup::select('*')->delete();

        $link = config('app.crm_url');
        $token = config('app.crm_token');

        // order status groups
        $client = SimpleClientFactory::createClient($link, $token);
        $statusGroups = $client->references->statusGroups()->statusGroups;
        // Log::debug(print_r($statusGroups, true));
        foreach ($statusGroups as $statusGroup) {
            $code = $statusGroup->code;
            $name = $statusGroup->code;
            Log::debug($code);
            $statusGroup = OrderStatusGroup::where('symbolic_code', $code)->first();
            if (!$statusGroup) {
                OrderStatusGroup::create([
                    'symbolic_code' => $code,
                    'name' => $name,
                ]);
            }
            Log::debug(print_r($statusGroup, true));
        }

        // orders statuses
        $orderStatuses = $client->references->statuses()->statuses;
        foreach ($orderStatuses as $orderStatus) {
            $status = OrderStatus::where('symbolic_code', $orderStatus->code)->first();
            $statusGroup = OrderStatusGroup::where('symbolic_code', $orderStatus->group)->first();
            if (!$status) {
                OrderStatus::create([
                    'symbolic_code' => $orderStatus->code,
                    'name' => $orderStatus->name,
                    'status_group_id' => $statusGroup->id,
                ]);
            } else {
                $status->status_group_id = $statusGroup->id;
                $status->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
