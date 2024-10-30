<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

use App\Models\V1\Order;
use App\Models\V1\OrderProduct;
use App\Models\V1\OrderStatus;
use App\Models\V1\OrderStatusGroup;
use App\Models\V1\Product;
use App\Models\V1\Contractor;
use Response;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Model\Request\BySiteRequest;
use RetailCrm\Api\Enum\ByIdentifier;
use RetailCrm\Api\Model\Request\Orders\OrdersEditRequest;
use RetailCrm\Api\Model\Entity\Packs\OrderProductPack;
use RetailCrm\Api\Model\Request\Packs\PacksRequest;
use RetailCrm\Api\Model\Filter\Packs\OrderProductPackFilter;
use RetailCrm\Api\Model\Request\Packs\PacksCreateRequest;

class RetailCRMService
{
    private $client = null;

    /**
     * RetailCRMService constructor.
     *
     * @param string $link URL-адрес API RetailCRM.
     * @param string $token Токен для подключения к API RetailCRM.
     */
    function __construct($link, $token)
    {
        $this->client = SimpleClientFactory::createClient($link, $token);
    }

    /**
     * Получает заказ из RetailCRM по external_id. Возвращает collection.
     *
     * @param int $externalID Идентификатор заказа в RetailCRM.
     * @return Illuminate\Support\Collection|null Возвращает null, если заказ не удалось загрузить из CRM.
     */
    public function getOrder($externalID)
    {
        try {
            $response = $this->client->orders->get($externalID, new BySiteRequest(ByIdentifier::ID, 'site'));

            $order = collect([
                "external_id" => $response->order->id,
                "number" => $response->order->number,
                "order_status_id" => $this->getOrderStatus($response->order->status)->id,
                "orderProducts" => collect(),
                'customFields' => [
                    'tip_zameny' => isset($response->order->customFields['tip_zameny']) ? $response->order->customFields['tip_zameny'] : null,
                    'vozvrat_tovara' => isset($response->order->customFields['vozvrat_tovara']) ? $response->order->customFields['vozvrat_tovara'] : null,
                    'vozvrat_tovaraa' => isset($response->order->customFields['vozvrat_tovaraa']) ? $response->order->customFields['vozvrat_tovaraa'] : null,
                    'tovar_zabran' => isset($response->order->customFields['tovar_zabran']) ? $response->order->customFields['tovar_zabran'] : null
                ]
            ]);

            foreach ($response->order->items as $item) {
                if ($item->offer->article) {
                    $order['orderProducts']->push([
                        "external_id" => $item->id,
                        "amount" => $item->quantity,
                        "avg_price" => $this->getItemPurchasePrice($item),
                        "product_id" => $this->getItemProduct($item)->id,
                        "contractor_id" => $this->getItemContractor($response->order, $item)->id
                    ]);
                } else {
                    // Смена статуса заказа на "Товар без артикула"
                    if ($response->order->status == "send-to-delivery") {
                        $errorOrder = new Order;
                        $errorOrder->external_id = $order['external_id'];
                        $errorOrder->order_status_id = OrderStatus::select('id')->where('symbolic_code', "tovar-bez-artikula")->first()->id;

                        $this->updateOrder($errorOrder);
                        return Response('В заказе есть товар без артикула', 422);
                    }
                }
            }

            $order['orderProducts'] = $order['orderProducts']->all();

            return $order;
        } catch (ApiExceptionInterface | ClientExceptionInterface $e) {
            return null;
        }
    }

    /**
     * Создаёт новый заказ в CRM.
     */
    public function createOrder()
    {
        // Заглушка на создание заказа в CRM.
    }

    /**
     * Получает закупочную цену товара в заказе
     */
    private function getItemPurchasePrice($item)
    {
        // Пытаемся получить закупочную цену товара из упаковок
        $packs = $this->getItemPacks($item);
        if ($packs) {
            foreach ($packs as $pack) {
                if ($pack->quantity && $pack->purchasePrice != 0)
                    return $pack->purchasePrice;
            }
        }

        // Если не удалось получить цену из упаковок, то получаем цену из item напрямую
        return $item->purchasePrice;
    }

    /**
     * Получает данные об упаковках позиции в заказе
     */
    private function getItemPacks($item)
    {
        $request = new PacksRequest;
        $request->filter = new OrderProductPackFilter;
        $request->filter->itemId = $item->id;

        $packs = $this->client->packs->list($request);

        return $packs->packs;
    }

    /**
     * Обновляет данные в упаковках позиции в заказе
     */
    private function updateItemPacks($item)
    {
        // Log::info("Обновление упаковок для позиции: {$item->id}");

        $request = new PacksRequest;
        $request->filter = new OrderProductPackFilter;
        $request->filter->itemId = $item->id;

        // Log::info("Отправка запроса на получение упаковок для позиции: {$item->id}");
        $packs = $this->client->packs->list($request);

        foreach ($packs->packs as $pack) {
            // Log::info("Обработка упаковки: {$pack->id}");

            $packRequest = new PacksCreateRequest();
            $packRequest->pack = new OrderProductPack;
            $packRequest->pack->purchasePrice = $item->purchasePrice;

            // Log::info("Отправка запроса на обновление упаковки: {$pack->id}");
            // Log::info("Содержимое запроса на обновление упаковки: ", (array) $packRequest);
            $response = $this->client->packs->edit($pack->id, $packRequest);
            // Log::info("Ответ API на запрос обновления: ", (array) $response);
        }

        // Log::info("Завершено обновление упаковок для позиции: {$item->id}");
    }

    /**
     * Обновляет заказ в CRM.
     */
    public function updateOrder($order)
    {
        // Log::info("Обновление заказа: {$order->external_id}");

        $externalID = $order->external_id;
        $crmOrder = $this->client->orders->get($externalID, new BySiteRequest(ByIdentifier::ID, 'site'))->order;
        $orderStatusSC = OrderStatus::where('id', $order->order_status_id)->first()->symbolic_code;
        $crmOrder->status = $orderStatusSC;

        $orderProducts = $order->orderProducts;
        foreach ($orderProducts as $orderProduct) {
            foreach ($crmOrder->items as $item) {
                if ($item->id == $orderProduct->external_id) {
                    // Log::info("Обновление позиции заказа: {$item->id}");
                    $item->purchasePrice = $orderProduct->avg_price;
                    $this->updateItemPacks($item);
                }
            }
        }

        $request = new OrdersEditRequest();
        $request->by    = ByIdentifier::ID;
        $request->site  = $crmOrder->site;
        $request->order = $crmOrder;
        $this->removeKeyFromObject($request->order, 'delivery');

        // Log::info("Отправка запроса на обновление заказа в CRM: {$externalID}");
        $response = $this->client->orders->edit($externalID, $request);

        // Log::info("Завершено обновление заказа: {$externalID}");
        return $response;
    }

    /**
     * Удаляет заказ в CRM.
     */
    public function destroyOrder()
    {
        // Заглушка на удаление заказа в CRM.
    }

    /**
     * Возвращает статус заказа из БД по символьному коду из CRM.
     * Если в БД такого статуса нет, создаёт его и группу из CRM для него, если необходимо.
     *
     * @param string $orderStatusCode
     * @return App\Modeles\V1\OrderStatus
     */
    private function getOrderStatus($orderStatusCode)
    {
        $status = OrderStatus::where('symbolic_code', $orderStatusCode)->first();
        if (!$status) {
            $orderStatuses = $this->client->references->statuses()->statuses;
            foreach ($orderStatuses as $orderStatus) {
                if ($orderStatus->code == $orderStatusCode) {

                    $statusGroup = $this->getOrderStatusGroup($orderStatus->group);
                    $status = OrderStatus::create([
                        'symbolic_code' => $orderStatus->code,
                        'name' => $orderStatus->name,
                        'status_group_id' => $statusGroup->id,
                    ]);
                    break;
                }
            }
        }
        return $status;
    }

    /**
     * Возвращает группу статусов заказов из БД по символьному коду из CRM или создаёт её.
     *
     * @param string $orderStatusGroupCode
     * @return App\Modeles\V1\OrderStatusGroup
     */
    private function getOrderStatusGroup($orderStatusGroupCode)
    {
        $statusGroup = OrderStatusGroup::where('symbolic_code', $orderStatusGroupCode)->first();
        if (!$statusGroup) {
            $statusGroups = $this->client->references->statusGroups()->statusGroups;
            foreach ($statusGroups as $statusGroup) {
                if ($statusGroup->code == $orderStatusGroupCode) {
                    $statusGroup = OrderStatusGroup::create([
                        'symbolic_code' => $statusGroup->code,
                        'name' => $statusGroup->name,
                    ]);
                    break;
                }
            }
        }
        return $statusGroup;
    }

    /**
     * Возвращает соответствующий App\Modeles\V1\Product в БД склада переданного
     * \RetailCrm\Api\Model\Entity\Orders\Items\OrderProduct производя поиск по артикули или создаёт новый App\Modeles\V1\Product.
     *
     * @param \RetailCrm\Api\Model\Entity\Orders\Items\OrderProduct $item
     * @return App\Modeles\V1\Product
     */
    private function getItemProduct($item)
    {
        $sku = $item->offer->article;
        $product = Product::whereRaw("JSON_SEARCH(LOWER(CAST(products.sku_list AS CHAR)), 'one', LOWER(?)) IS NOT NULL", ["{$sku}"])->first();
        if (!$product) {
            $product = new Product;
            $product = $product->fill([
                'name' => $item->offer->name,
                'main_sku' => $sku,
                // 'sku_list' => '["' . $sku . '"]',
            ]);
            $product->sku_list = '["' . $sku . '"]';
            $product->save();
        }
        return $product;
    }

    /**
     * Возвращает App\Modeles\V1\Contractor для переданного \RetailCrm\Api\Model\Entity\Orders\Items\OrderProduct.
     *
     * @param \RetailCrm\Api\Model\Entity\Orders\Order $order
     * @param \RetailCrm\Api\Model\Entity\Orders\Items\OrderProduct $item
     * @return App\Modeles\V1\Contractor
     */
    private function getItemContractor($order, $item)
    {
        if ($item->status != 'new') {
            $contractor = Contractor::whereJsonContains('symbolic_code_list', $item->status)->first();
        } else {
            $contractor = Contractor::whereJsonContains('symbolic_code_list', $order->customFields['postavshik'])->first();
        }

        if (!$contractor) {
            return new Contractor;
        }

        return $contractor;
    }

    public function removeKeyFromObject($object, $keyToClear)
    {
        if (is_object($object)) {
            foreach ($object as $key => $value) {
                if ($key === $keyToClear) {
                    // Очищаем значение ключа
                    $object->{$key} = null;
                } else {
                    $this->removeKeyFromObject($value, $keyToClear);
                }
            }
        } elseif (is_array($object)) {
            foreach ($object as $item) {
                $this->removeKeyFromObject($item, $keyToClear);
            }
        }
    }
}
