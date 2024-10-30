<?php

namespace App\Services\Entities\ProductRefunds;

use App\Models\V1\ProductRefund;
use App\Services\Entities\ProductRefunds\ProductRefundService;
use RetailCrm\Api\Model\Entity\Orders\Order;
use App\Services\RetailCRMService;
use Exception;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Enum\ByIdentifier;
use RetailCrm\Api\Model\Request\BySiteRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersEditRequest;

/**
 * Class ProductRefundToRetailOrderConverter
 * Класс для конвертации объектов ProductRefund в объекты Order для RetailCRM и обратно.
 */
class ProductRefundToRetailOrderConverter
{
    /**
     * @var RetailCRMService
     */
    protected $retailCRMService;
    /**
     * @var \RetailCrm\Api\Interfaces\ClientInterface
     */
    protected $retailClient;

    /**
     * ProductRefundToRetailOrderConverter конструктор.
     */
    public function __construct()
    {
        $this->retailCRMService = new RetailCRMService(
            config('retailcrm_integration.retailcrm.url'),
            config('retailcrm_integration.retailcrm.token')
        );
        $this->retailClient = SimpleClientFactory::createClient(
            config('retailcrm_integration.retailcrm.url'),
            config('retailcrm_integration.retailcrm.token')
        );
    }

    /**
     * Преобразует ProductRefund в объект Order для RetailCRM.
     *
     * @param ProductRefund $productRefund
     * @return Order
     */
    public function updateOrderFromProductRefund(ProductRefund $productRefund): Order
    {
        $order = new Order();

        $order->id = $productRefund->order->external_id;

        $mappings = config('retailcrm_integration.mappings');

        $order->customFields['tovar_zabran'] = self::findInMappingArray($mappings['tovar_zabran'], 'name', $productRefund->product_location, 'symbolic_code');
        $order->customFields['vozvrat_tovaraa'] = self::findInMappingArray($mappings['vozvrat_tovaraa'], 'id', $productRefund->status, 'symbolic_code');

        return $order;
    }

    /**
     * Обновляет объект ProductRefund на основе объекта Order из RetailCRM.
     *
     * @param array $order
     * @return ProductRefund|null
     * @throws Exception
     */
    public static function updateProductRefundFromOrder($order): ?ProductRefund
    {
        // Пытаемся найти существующий ProductRefund, связанный с данным заказом
        $productRefund = ProductRefund::whereHas('order', function ($query) use ($order) {
            $query->where('external_id', $order['external_id']);
        })->first();

        // Если ProductRefund не найден, возвращаем null
        if (!$productRefund) {
            throw new Exception('Возврат по заказу под номеру ' . $order['number'] . ' не найден', 400);
        }

        $mappings = config('retailcrm_integration.mappings');
        // Обновление полей в ProductRefund на основе данных из заказа
        if (isset($order['customFields']['tovar_zabran'])) {
            $productRefund->product_location = self::findInMappingArray($mappings['tovar_zabran'], 'symbolic_code', $order['customFields']['tovar_zabran'], 'name') ?? $productRefund->product_location;
        }
        if (isset($order['customFields']['vozvrat_tovaraa'])) {
            $productRefund->status = self::findInMappingArray($mappings['vozvrat_tovaraa'], 'symbolic_code', $order['customFields']['vozvrat_tovaraa'], 'id') ?? $productRefund->status;
        }

        // Сохранение изменений в объекте ProductRefund
        $productRefundService = app(ProductRefundService::class);
        $productRefundService->update($productRefund, $productRefund->toArray());

        return $productRefund;
    }

    /**
     * Обновляет заказ в RetailCRM на основе переданного объекта Order.
     *
     * @param Order $order
     * @return \RetailCrm\Api\Model\Response\Orders\OrdersEditResponse
     */
    public function updateRetailOrder(Order $order)
    {
        $crmOrder = $this->retailClient->orders->get($order->id, new BySiteRequest(ByIdentifier::ID, 'site'))->order;

        $request = new OrdersEditRequest();
        $request->by    = ByIdentifier::ID;
        $request->site  = $crmOrder->site;
        $request->order = $order;

        $response = $this->retailClient->orders->edit($crmOrder->id, $request);

        return $response;
    }

    /**
     * Универсальная функция для поиска значения по ключу в массиве сопоставлений.
     *
     * @param array $mappingArray Массив сопоставлений.
     * @param string $searchField Поле для поиска.
     * @param mixed $searchValue Значение для поиска.
     * @param string $returnField Поле, значение которого необходимо вернуть.
     * @return mixed|null
     */
    private static function findInMappingArray(array $mappingArray, string $searchField, $searchValue, string $returnField)
    {
        $index = array_search($searchValue, array_column($mappingArray, $searchField));
        return $index !== false ? $mappingArray[$index][$returnField] : null;
    }
}
