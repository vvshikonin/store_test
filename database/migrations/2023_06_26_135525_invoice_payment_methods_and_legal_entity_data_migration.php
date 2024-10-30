<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\V1\LegalEntity;
use App\Models\V1\PaymentMethod;
use App\Models\V1\Invoice;

class InvoicePaymentMethodsAndLegalEntityDataMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $gribanov = LegalEntity::where('name', 'ИП Грибанов')->first();
        if (!$gribanov) {
            $gribanov = LegalEntity::create(['name' => 'ИП Грибанов']);
        }

        $shikonon = LegalEntity::where('name', 'ИП Шиконин')->first();
        if (!$shikonon) {
            $shikonon = LegalEntity::create(['name' => 'ИП Шиконин']);
        }

        $nalGribanov = PaymentMethod::where('name', 'Наличными')->where('legal_entity_id', $gribanov->id)->first();
        if (!$nalGribanov) {
            $nalGribanov = PaymentMethod::create(['name' => 'Наличными', 'legal_entity_id' => $gribanov->id, 'type' => 0]);
        }

        $nalShikonin = PaymentMethod::where('name', 'Наличными')->where('legal_entity_id', $shikonon->id)->first();
        if (!$nalShikonin) {
            $nalShikonin = PaymentMethod::create(['name' => 'Наличными', 'legal_entity_id' => $shikonon->id, 'type' => 0]);
        }

        $sber = PaymentMethod::where('name', 'РС Сбер')->where('legal_entity_id', $shikonon->id)->first();
        if (!$sber) {
            $sber = PaymentMethod::create(['name' => 'РС Сбер', 'legal_entity_id' => $shikonon->id, 'type' => 1]);
        }

        $alfa = PaymentMethod::where('name', 'РС Альфа')->where('legal_entity_id', $gribanov->id)->first();
        if (!$alfa) {
            $alfa = PaymentMethod::create(['name' => 'РС Альфа', 'legal_entity_id' => $gribanov->id, 'type' => 1]);
        }

        $tks = PaymentMethod::where('name', 'Карта ТКС')->where('legal_entity_id', $gribanov->id)->first();
        if (!$tks) {
            $tks = PaymentMethod::create(['name' => 'Карта ТКС', 'legal_entity_id' => $gribanov->id, 'type' => 1]);
        }

        $rai = PaymentMethod::where('name', 'Карта Райффайзен')->where('legal_entity_id', $gribanov->id)->first();
        if (!$rai) {
            $rai = PaymentMethod::create(['name' => 'Карта Райффайзен', 'legal_entity_id' => $gribanov->id, 'type' => 1]);
        }

        $invoices = Invoice::all();
        foreach ($invoices as $invoice) {
            $invoice->legal_entity_id = LegalEntity::where('name', $invoice->legal_entity)->first() ? LegalEntity::where('name', $invoice->legal_entity)->first()->id : null;
            if ($invoice->payment_type == 0) {

                if ($invoice->legal_entity_id == $gribanov->id) {
                    $invoice->payment_method_id = $nalGribanov->id;
                } else if ($invoice->legal_entity_id == $shikonon->id) {
                    $invoice->payment_method_id = $nalShikonin->id;
                }
            } else if ($invoice->payment_type == 1) {
                if ($invoice->legal_entity_id == $shikonon->id) {
                    $invoice->payment_method_id = $sber->id;
                } else if ($invoice->legal_entity_id == $gribanov->id) {
                    $invoice->payment_method_id = PaymentMethod::where('name', $invoice->cashless_payment_type)->where('legal_entity_id', $gribanov->id)->first() ? PaymentMethod::where('name', $invoice->cashless_payment_type)->where('legal_entity_id', $gribanov->id)->first()->id : null;
                }
            }

            $invoice->save();
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
