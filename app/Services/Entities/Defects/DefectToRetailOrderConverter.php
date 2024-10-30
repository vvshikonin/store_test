<?php

namespace App\Services\Entities\Defects;

use App\Models\V1\Defect;
use RetailCrm\Api\Model\Entity\Orders\Order;
use App\Services\RetailCRMService;
use Exception;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Enum\ByIdentifier;
use RetailCrm\Api\Model\Request\BySiteRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersEditRequest;
use Illuminate\Support\Facades\Log;
use App\Services\Entities\Defects\DefectService;

/**
 * Class DefectToRetailOrderConverter
 * Класс для конвертации объектов Defect в объекты Order для RetailCRM и обратно.
 */
class DefectToRetailOrderConverter
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
     * DefectToRetailOrderConverter конструктор.
     */
    public function __construct()
    {
        $this->retailCRMService = new RetailCRMService(config('retailcrm_integration.retailcrm.url'), config('retailcrm_integration.retailcrm.token'));
        $this->retailClient = SimpleClientFactory::createClient(config('retailcrm_integration.retailcrm.url'), config('retailcrm_integration.retailcrm.token'));
    }

    /**
     * Преобразует Defect в объект Order для RetailCRM.
     *
     * @param Defect $defect
     * @return Order
     */
    public function updateOrderFromDefect(Defect $defect): Order
    {
        $order = new Order();

        $order->id = $defect->order->external_id;

        $order->customFields['tovar_zabran'] = $this->getSymbolicCode(config('retailcrm_integration.mappings.tovar_zabran'), $defect->product_location);
        $order->customFields['tip_zameny'] = $this->getSymbolicCode(config('retailcrm_integration.mappings.tip_zameny'), $defect->replacement_type);
        $order->customFields['vozvrat_tovara'] = $this->getSymbolicCode(config('retailcrm_integration.mappings.vozvrat_tovara'), $defect->refund_type);
        $order->customFields['vozvrat_tovaraa'] = $this->getSymbolicCode(config('retailcrm_integration.mappings.vozvrat_tovaraa'), $defect->is_completed);

        return $order;
    }

    /**
     * Обновляет объект Defect на основе объекта Order из RetailCRM.
     *
     * @param array $order
     * @return Defect|null
     * @throws Exception
     */
    public static function updateDefectFromOrder($order): ?Defect
    {
        // Пытаемся найти существующий Defect, связанный с данным заказом
        $defect = Defect::whereHas('order', function ($query) use ($order) {
            $query->where('external_id', $order['external_id']);
        })->first();

        // Если Defect не найден, возвращаем null
        if (!$defect) {
            throw new Exception('Брак по заказу под номеру ' . $order['number'] . ' не найден', 400);
        }

        // Обновление полей в Defect на основе данных из заказа
        if (isset($order['customFields']['tovar_zabran'])) {
            $defect->product_location = self::getId(config('retailcrm_integration.mappings.tovar_zabran'), $order['customFields']['tovar_zabran']) ?? $defect->product_location;
        }
        if (isset($order['customFields']['tip_zameny'])) {
            $defect->replacement_type = self::getId(config('retailcrm_integration.mappings.tip_zameny'), $order['customFields']['tip_zameny']) ?? $defect->replacement_type;
        }
        if (isset($order['customFields']['vozvrat_tovara'])) {
            $defect->refund_type = self::getId(config('retailcrm_integration.mappings.vozvrat_tovara'), $order['customFields']['vozvrat_tovara']) ?? $defect->refund_type;
        }
        if (isset($order['customFields']['vozvrat_tovaraa'])) {
            $defect->is_completed = self::getId(config('retailcrm_integration.mappings.vozvrat_tovaraa'), $order['customFields']['vozvrat_tovaraa']) ?? $defect->is_completed;
        }

        // Сохранение изменений в объекте Defect
        $defectService = app(DefectService::class);
        $defectService->update($defect, $defect->toArray());

        return $defect;
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
     * Получает символьный код на основе переданного массива сопоставления и идентификатора.
     *
     * @param array $mappingArray
     * @param int|null $id
     * @return string|null
     */
    private function getSymbolicCode(array $mappingArray, ?int $id): ?string
    {
        foreach ($mappingArray as $item) {
            if ($item['id'] === $id) {
                return $item['symbolic_code'];
            }
        }

        // Если не нашли соответствующий symbolic_code
        return null;
    }

    /**
     * Получает идентификатор на основе переданного массива сопоставления и символьного кода.
     *
     * @param array $mappingArray
     * @param string $symbolicCode
     * @return int|null
     */
    private static function getId(array $mappingArray, $symbolicCode): ?int
    {
        foreach ($mappingArray as $item) {
            if ($item['symbolic_code'] === $symbolicCode) {
                return $item['id'];
            }
        }

        return null;
    }
}
