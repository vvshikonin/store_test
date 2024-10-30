<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\V1\PriceMonitoring;
use App\Http\Resources\V1\PriceMonitoringResource;
use Illuminate\Support\Facades\Log;
use Symfony\Component\ErrorHandler\Debug;

class PriceMonitoringController extends Controller
{
    public function index(Request $request)
    {
        // Проверяем доступность метода пользователю
        $permissions = auth()->user()->permissions;
        if (!$permissions->where('name', 'price_monitoring_read')->count()) {
            return response('', 403);
        }

        // Получаем все объекты PriceMonitoring и их связанные продукты
        // $monitorings = PriceMonitoring::all();
        $monitorings = PriceMonitoring::productFilter($request->product_filter)->get();

        // Парсим каждый мониторинг и проверяем значение дельты, если установлен фильтр
        foreach ($monitorings as $key => $monitoring) {
            $this->parse($monitoring);

            // Если delta_filter равен true, проверяем условие
            if ($request->delta_filter) {
                if ($monitoring->parsed_price > 0) {
                    $delta = (($monitoring->product->RRP - $monitoring->parsed_price) / $monitoring->parsed_price) * 100;
                    if ($delta < 10) {
                        // Если условие не выполняется, удаляем мониторинг из коллекции
                        unset($monitorings[$key]);
                    }
                } else {
                    // Если parsed_price равен нулю, мы не можем выполнить расчет, поэтому удаляем мониторинг из коллекции
                    unset($monitorings[$key]);
                }
            }
        }

        // Возвращаем все объекты PriceMonitoring, отформатированные с помощью ресурса
        return PriceMonitoringResource::collection($monitorings->fresh());
    }

    public function show($id)
    {
        // Проверяем доступность метода пользователю
        $permissions = auth()->user()->permissions;
        if (!$permissions->where('name', 'price_monitoring_read')->count()) {
            return response('', 403);
        }

        // Находим объект PriceMonitoring по его ID
        $monitoring = PriceMonitoring::findOrFail($id);

        // Парсим мониторинг
        $this->parse($id);

        // Возвращаем объект PriceMonitoring, отформатированный с помощью ресурса
        return new PriceMonitoringResource($monitoring);
    }

    public function store(Request $request)
    {
        // Проверяем доступность метода пользователю
        $permissions = auth()->user()->permissions;
        if (!$permissions->where('name', 'price_monitoring_create')->count()) {
            return response('', 403);
        }

        // Создаем новый объект PriceMonitoring
        $monitoring = new PriceMonitoring;

        // Заполняем модель данными из запроса
        $monitoring->url = $request->url;
        $monitoring->xpath = $request->xpath;
        $monitoring->product_id = $request->product_id;

        // Сохраняем объект PriceMonitoring
        $monitoring->save();

        // Возвращаем созданный объект PriceMonitoring, отформатированный с помощью ресурса
        return new PriceMonitoringResource($monitoring);
    }

    public function update(Request $request, $id)
    {
        // Проверяем доступность метода пользователю
        $permissions = auth()->user()->permissions;
        if (!$permissions->where('name', 'price_monitoring_update')->count()) {
            return response('', 403);
        }

        // Находим объект PriceMonitoring по его ID
        $monitoring = PriceMonitoring::findOrFail($id);

        // Обновляем поля объекта PriceMonitoring данными из запроса
        $monitoring->url = $request->url;
        $monitoring->xpath = $request->xpath;
        $monitoring->product_id = $request->product_id;

        // Сохраняем изменения
        $monitoring->save();

        // Парсим обновленный мониторинг
        $this->parse($monitoring->fresh());

        // Возвращаем обновленный объект PriceMonitoring, отформатированный с помощью ресурса
        return new PriceMonitoringResource($monitoring->fresh());
    }

    public function destroy($id)
    {
        // Проверяем доступность метода пользователю
        $permissions = auth()->user()->permissions;
        if (!$permissions->where('name', 'price_monitoring_delete')->count()) {
            return response('', 403);
        }

        // Находим объект PriceMonitoring по его ID
        $monitoring = PriceMonitoring::findOrFail($id);

        // Удаляем объект PriceMonitoring
        $monitoring->delete();

        // Возвращаем успешный ответ
        return response()->json(null, 204);
    }

    protected function parse($monitoring)
    {
        // Если отсутствует url, xpath или ррц у товара, то возвращаем ошибку
        if (empty($monitoring->url) || empty($monitoring->xpath) || empty($monitoring->product->RRP)) {
            return response()->json(['error' => 'URL, XPath or Product RRP is missing'], 400);
        }

        // Инициализируем DOMDocument и DOMXPath
        $doc = new \DOMDocument;

        try {
            // Загружаем HTML из указанного URL
            @$doc->loadHTMLFile($monitoring->url);
            // Log::debug(print_r($doc, true));

            // Выполняем XPath запрос
            $xpath = new \DOMXPath($doc);
            $nodes = $xpath->query($monitoring->xpath);

            // Берем первый узел или возвращаем null, если узлов нет
            $result = $nodes->item(0) ? $nodes->item(0)->nodeValue : null;

            // Log::debug("Результат парсинга: " . $result);

            // Если результат не null, то удаляем все кроме чисел, точек и запятых
            if ($result !== null) {
                $result = preg_replace('/[^0-9\.,]/', '', $result);
            }

            // Заменяем запятую на точку для корректного преобразования в float
            $result = str_replace(',', '.', $result);

            // Сохраняем результат в базе данных
            $monitoring->parsed_price = floatval($result);
            $monitoring->save();
        } catch (\Exception $e) {
            // Если что-то пошло не так, возвращаем ошибку
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
