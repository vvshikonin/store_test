<?php

namespace Tests\Feature;

use App\Models\V1\Invoice;
use App\Models\V1\InvoiceProduct;
use App\Models\User;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Carbon;
use App\Services\Entities\InvoiceService;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{

    public function testCreate()
    {
        Gate::shouldReceive('allows')->andReturn(true);

        $user = User::factory()->create();
        $this->actingAs($user);

        $data = $this->makeDataForCreate();
        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->create($data);

        $this->assertInstanceOf(Invoice::class, $invoice);

        $this->assertDatabaseHas('invoices', [
            'number' => $data['number'],
            'date' => Carbon::parse($data['date'])->format('Y-m-d'),
            'contractor_id' => $data['contractor_id'],
            'comment' => $data['comment'],
            'payment_method_id' => $data['payment_method_id'],
            'legal_entity_id' => $data['legal_entity_id'],
            'payment_status' => $data['payment_status'],
            'payment_confirm' => $data['payment_confirm'],
            'delivery_type' => $data['delivery_type']
        ]);

        foreach ($data['new_products'] as $newInvoiceProduct) {
            $this->assertDatabaseHas('invoice_products', [
                'invoice_id' => $invoice->id,
                'product_id' => $newInvoiceProduct['product_id'],
                'price' => $newInvoiceProduct['price'],
                'amount' =>  $newInvoiceProduct['amount'],
                'received' => $newInvoiceProduct['received'],
                'refused' =>  $newInvoiceProduct['refused'],
                'planned_delivery_date_from' => Carbon::parse($newInvoiceProduct['planned_delivery_date_from'])->format('Y-m-d'),
                'planned_delivery_date_to' => Carbon::parse($newInvoiceProduct['planned_delivery_date_to'])->format('Y-m-d'),
            ]);
        }
    }

    public function testUpdate()
    {
        Gate::shouldReceive('allows')->andReturn(true);
        Gate::shouldReceive('any')->andReturn(true);

        $user = User::factory()->create();
        $this->actingAs($user);

        $data = $this->makeDataForCreate();
        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->create($data);

        $newData = $this->makeDataForUpdate($invoice);
        $invoice = $invoiceService->update($invoice, $newData);

        $this->assertInstanceOf(Invoice::class, $invoice);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'number' => $newData['number'],
            'date' => Carbon::parse($newData['date'])->format('Y-m-d'),
            'contractor_id' => $newData['contractor_id'],
            'comment' => $newData['comment'],
            'payment_method_id' => $newData['payment_method_id'],
            'legal_entity_id' => $newData['legal_entity_id'],
            'payment_status' => $newData['payment_status'],
            'payment_confirm' => $newData['payment_confirm'],
            'delivery_type' => $newData['delivery_type']
        ]);

        foreach ($newData['new_products'] as $newInvoiceProduct) {
            $this->assertDatabaseHas('invoice_products', [
                'invoice_id' => $invoice->id,
                'product_id' => $newInvoiceProduct['product_id'],
                'price' => $newInvoiceProduct['price'],
                'amount' =>  $newInvoiceProduct['amount'],
                'received' => $newInvoiceProduct['received'],
                'refused' =>  $newInvoiceProduct['refused'],
                'planned_delivery_date_from' => Carbon::parse($newInvoiceProduct['planned_delivery_date_from'])->format('Y-m-d'),
                'planned_delivery_date_to' => Carbon::parse($newInvoiceProduct['planned_delivery_date_to'])->format('Y-m-d'),
            ]);
        }

        foreach ($newData['products'] as $InvoiceProduct) {
            $this->assertDatabaseHas('invoice_products', [
                'invoice_id' => $invoice->id,
                'product_id' => $InvoiceProduct['product_id'],
                'price' => $InvoiceProduct['price'],
                'amount' =>  $InvoiceProduct['amount'],
                'received' => $InvoiceProduct['received'],
                'refused' =>  $InvoiceProduct['refused'],
                'planned_delivery_date_from' => Carbon::parse($InvoiceProduct['planned_delivery_date_from'])->format('Y-m-d'),
                'planned_delivery_date_to' => Carbon::parse($InvoiceProduct['planned_delivery_date_to'])->format('Y-m-d'),
            ]);
        }

        foreach ($newData['deleted_products'] as $InvoiceProduct) {
            $this->assertSoftDeleted('invoice_products', [
                'id' => $InvoiceProduct,
                'invoice_id' => $invoice->id,
            ]);
        }
    }

    public function testDelete()
    {
        Gate::shouldReceive('allows')->andReturn(true);

        $user = User::factory()->create();
        $this->actingAs($user);

        $data = $this->makeDataForCreate();
        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->create($data);

        $invoiceService->delete($invoice);

        $this->assertSoftDeleted('invoices', [
            'id' => $invoice->id,
        ]);
    }

    private function makeDataForCreate()
    {
        $data = Invoice::factory()->make()->toArray();
        $data['new_products'] = $this->makeRandomCountInvoiceProductsData();

        return $data;
    }

    private function makeDataForUpdate($invoice)
    {
        $data = Invoice::factory()->make()->toArray();
        $data['new_products'] = $this->makeRandomCountInvoiceProductsData();

        $data['products'] = [];
        foreach ($invoice->invoiceProducts() as $invocieProduct) {
            $id = $invocieProduct->id;
            $newData = InvoiceProduct::factory()->make()->toArray();
            $newData['id'] = $id;
            $data['products'][] = $newData;
        }

        $deletedProducts = $invoice->invoiceProducts()->inRandomOrder()->limit(random_int(1, $invoice->invoiceProducts->count()))->get();
        $data['deleted_products'] = [];
        foreach ($deletedProducts as $product) {
            $data['deleted_products'][] = $product->id;
        }

        return $data;
    }

    private function makeRandomCountInvoiceProductsData()
    {
        return InvoiceProduct::factory()->count(random_int(1, 20))->make()->toArray();
    }
}
