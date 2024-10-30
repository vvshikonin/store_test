<?php

use App\Models\V1\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RetailCrm\Api\Factory\SimpleClientFactory;
use App\Models\V1\OrderStatusGroup;
use App\Models\V1\OrderStatus;
use App\Facades\RetailCRM;

class OrderProductsDataMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */



    public function up()
    {

        // external id
        $orders = Order::withTrashed()->get();
        foreach ($orders as $order) {
            $order->external_id = $order->crm_id;
            $order->save();
        }

        $link = config('app.crm_url');
        $token = config('app.crm_token');

        // order status groups
        $client = SimpleClientFactory::createClient($link, $token);
        $statusGroups = $client->references->statusGroups()->statusGroups;
        foreach ($statusGroups as $statusGroup) {
            $code = $statusGroup->code;
            $statusGroup = OrderStatusGroup::where('symbolic_code', $code)->first();
            if (!$statusGroup) {
                $statusGroup = OrderStatusGroup::create([
                    'symbolic_code' => $statusGroup ? $statusGroup->code : null,
                    'name' => $statusGroup ? $statusGroup->name : null,
                ]);
            }
        }

        // orders statuses
        $orderStatuses = $client->references->statuses()->statuses;
        foreach ($orderStatuses as $orderStatus) {
            $status = OrderStatus::where('symbolic_code', $orderStatus->code)->first();
            if (!$status) {
                $statusGroup = OrderStatusGroup::where('symbolic_code', $orderStatus->group)->first();
                $status = OrderStatus::create([
                    'symbolic_code' => $orderStatus->code,
                    'name' => $orderStatus->name,
                    'status_group_id' => $statusGroup ? $statusGroup->id : null,
                ]);
            }
        }

        // orders status and states
        $reservingStatusCodes = ['availability-confirmed', 'confirmed', 'oplachen-doplatit', 'nedostatochno-ostatka'];
        $outcomingStatusCodes = ['send-to-delivery'];
        $cancelingStatusCodes = ['cancel-other', 'zadvoenie'];
        $refundStatusCodes = ['vozvratt'];
        $defectStatusCodes = ['brak'];
        $reservingStatusIDs = OrderStatus::select('id')->whereIn('symbolic_code', $reservingStatusCodes)->get();
        $outcomingStatusIDs = OrderStatus::select('id')->whereIn('symbolic_code', $outcomingStatusCodes)->get();
        $cancelingStatusIDs = OrderStatus::select('id')->whereIn('symbolic_code', $cancelingStatusCodes)->get();
        $refundStatusIDs = OrderStatus::select('id')->whereIn('symbolic_code', $refundStatusCodes)->get();
        $defectStatusIDs = OrderStatus::select('id')->whereIn('symbolic_code', $defectStatusCodes)->get();

        $orders = Order::withTrashed()->get();
        foreach($orders as $order){
            $order->deleted_at = null;
            if($order->external_id){
                $crmOrder = RetailCRM::getOrder($order->external_id);
                if($crmOrder)
                    $order->order_status_id = $crmOrder['order_status_id'];

                if (count($reservingStatusIDs->where('id', $order->order_status_id))) {
                    $order->state = 'reserved';
                } else if (count($outcomingStatusIDs->where('id', $order->order_status_id))) {
                    $order->state = 'outcomed';
                } else if (count($cancelingStatusIDs->where('id', $order->order_status_id))) {
                    $order->state = 'canceled';
                } else if (count($defectStatusIDs->where('id', $order->order_status_id))) {
                    $order->state = 'defect';
                } else if (count($refundStatusIDs->where('id', $order->order_status_id))) {
                    $order->state = 'refund';
                }
            }
            $order->save();
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
