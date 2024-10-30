<?php

namespace App\Services;

use App\Models\V1\Brand;
use App\Models\V1\Product;
use App\Models\V1\XMLTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;
use App\Models\V1\Stock;
use App\Models\V1\OrderProduct;

class XMLGeneratorService
{
    /**
     * Основной метод генерации XML-файлов для всех брендов и шаблонов.
     *
     * @return void
     */
    public function generateXml()
    {
        $this->generateXmlForAllBrands();
        $this->generateXmlForAllTemplates();
    }

    /**
     * Генерация XML-файлов для всех брендов.
     *
     * @return void
     */
    public function generateXmlForAllBrands()
    {
        $brands = Brand::all();

        foreach ($brands as $brand) {
            $this->generateXmlForBrand($brand);
        }
    }

    /**
     * Генерация XML-файла для конкретного бренда.
     *
     * @param Brand $brand
     * @return void
     */
    private function generateXmlForBrand(Brand $brand)
    {
        $initialXmlString = '<?xml version="1.0" encoding="UTF-8"?><xml/>';
        $xml = new SimpleXMLElement($initialXmlString);

        // Обработка товаров в автоматическом режиме распродажи
        $autoProducts = Product::select('main_sku', 'brand_id', 'name', 'sku_list', 'maintained_balance', 'id', 'sale_type')
            ->selectRaw('COALESCE(stocks.sum_amount, 0) as total_stock')
            ->selectRaw('COALESCE(reserved.amount, 0) as reserved_stock')
            ->selectRaw('GREATEST(COALESCE(stocks.sum_amount, 0) - COALESCE(reserved.amount, 0), 0) as free_stock')
            ->leftJoin(DB::raw('(SELECT SUM(COALESCE(amount, 0)) AS sum_amount, product_id FROM stocks GROUP BY product_id) AS stocks'), 'stocks.product_id', '=', 'products.id')
            ->leftJoin(DB::raw('(SELECT SUM(COALESCE(order_products.amount, 0)) AS amount, order_products.product_id FROM order_products JOIN orders ON order_products.order_id = orders.id WHERE orders.state = "reserved" GROUP BY order_products.product_id) AS reserved'), 'reserved.product_id', '=', 'products.id')
            ->havingRaw('free_stock > (0.5 * total_stock)')
            ->where('brand_id', '=', $brand->id)
            ->where('sale_type', '!=', 'nonsale')
            ->get();

        // Обработка товаров в режиме с множителем
        $multiplierProducts = Product::where('brand_id', '=', $brand->id)
            ->where('sale_type', '=', 'multiplier')
            ->get();

        foreach ($autoProducts as $product) {
            $skus = json_decode($product->sku_list, true);
            foreach ($skus as $sku) {
                $this->createOfferElement($xml, $product, $sku);
            }
        }

        foreach ($multiplierProducts as $product) {
            $totalStock = Stock::where('product_id', $product->id)->sum('amount');

            if ($totalStock == 0) {
                $product->update(['is_sale' => false, 'sale_type' => 'auto']);
                continue;
            } else {
                $product->update(['is_sale' => true]);
            }
            $skus = json_decode($product->sku_list, true);
            foreach ($skus as $sku) {
                $this->createOfferElement($xml, $product, $sku);
            }
        }

        $trimmedBrandName = str_replace(' ', '', $brand->name);
        $filePath = "xml/{$trimmedBrandName}.xml";
        Storage::disk('public')->put($filePath, $xml->asXML());

        Brand::where('id', $brand->id)->update(['xml_link' => 'public/storage/xml/' . $trimmedBrandName . '.xml']);
    }

    /**
     * Генерация XML-файлов для всех шаблонов.
     *
     * @return void
     */
    public function generateXmlForAllTemplates()
    {
        $templates = XMLTemplate::all();

        foreach ($templates as $template) {
            $this->generateXmlForTemplate($template);
        }
    }

    /**
     * Генерация XML-файла для шаблона, включающего несколько брендов.
     *
     * @param XMLTemplate $template
     * @return void
     */
    public function generateXmlForTemplate(XMLTemplate $template)
    {
        $initialXmlString = '<?xml version="1.0" encoding="UTF-8"?><xml/>';
        $xml = new SimpleXMLElement($initialXmlString);

        // Получаем бренды, указанные в шаблоне
        $brands = Brand::whereIn('id', $template->brand_ids)->get();

        foreach ($brands as $brand) {
            // Обработка товаров в автоматическом режиме распродажи
            $autoProducts = Product::select('main_sku', 'brand_id', 'name', 'sku_list', 'maintained_balance', 'id', 'sale_type')
                ->selectRaw('COALESCE(stocks.sum_amount, 0) as total_stock')
                ->selectRaw('COALESCE(reserved.amount, 0) as reserved_stock')
                ->selectRaw('GREATEST(COALESCE(stocks.sum_amount, 0) - COALESCE(reserved.amount, 0), 0) as free_stock')
                ->leftJoin(DB::raw('(SELECT SUM(COALESCE(amount, 0)) AS sum_amount, product_id FROM stocks GROUP BY product_id) AS stocks'), 'stocks.product_id', '=', 'products.id')
                ->leftJoin(DB::raw('(SELECT SUM(COALESCE(order_products.amount, 0)) AS amount, order_products.product_id FROM order_products JOIN orders ON order_products.order_id = orders.id WHERE orders.state = "reserved" GROUP BY order_products.product_id) AS reserved'), 'reserved.product_id', '=', 'products.id')
                ->havingRaw('free_stock > (0.5 * total_stock)')
                ->where('brand_id', '=', $brand->id)
                ->where('sale_type', '!=', 'nonsale')
                ->get();

            // Обработка товаров в режиме с множителем
            $multiplierProducts = Product::where('brand_id', '=', $brand->id)
                ->where('sale_type', '=', 'multiplier')
                ->get();

            foreach ($autoProducts as $product) {
                $skus = json_decode($product->sku_list, true);
                foreach ($skus as $sku) {
                    $this->createOfferElement($xml, $product, $sku);
                }
            }

            foreach ($multiplierProducts as $product) {
                $totalStock = Stock::where('product_id', $product->id)->sum('amount');

                if ($totalStock == 0) {
                    $product->update(['is_sale' => false, 'sale_type' => 'auto']);
                    continue;
                } else {
                    $product->update(['is_sale' => true]);
                }
                $skus = json_decode($product->sku_list, true);
                foreach ($skus as $sku) {
                    $this->createOfferElement($xml, $product, $sku);
                }
            }
        }

        // Имя файла основано на названии шаблона
        $trimmedTemplateName = str_replace(' ', '', $template->name);
        $filePath = "xml/templates/{$trimmedTemplateName}.xml";
        Storage::disk('public')->put($filePath, $xml->asXML());

        // Обновляем ссылку на XML-файл в шаблоне
        $template->update(['xml_link' => 'public/storage/xml/templates/' . $trimmedTemplateName . '.xml']);
    }

    /**
     * Метод для формирования структуры $xml с учетом данных для каждого offer-товара.
     *
     * @param SimpleXMLElement $xml
     * @param App\Models\V1\Product $product
     * @param String $sku
     *
     * @return void
     */
    private function createOfferElement($xml, $product, $sku)
    {
        // Выбор логики ценообразования на основе типа распродажи
        switch ($product->sale_type) {
            case 'multiplier':
                // Расчёт цены продажи с учётом множителя
                $salePrice = $product->averagePrice * $product->sale_multiplier;
                break;
            case 'auto':
            default:
                // Автоматический режим (используется текущий механизм)
                $salePrice = $product->salePrice;
                break;
        }

        // Проверка на слишком низкую цену
        if ($salePrice < 10) {
            return;
        }

        $offer = $xml->addChild('offer');
        $offer->addAttribute('productId', $product->id);
        $offer->addAttribute('saleType', $product->sale_type);

        $offer->addChild('price_rrp', round($salePrice, -1)); // Продажная цена
        $offer->addChild('oldPrice', round($salePrice / 0.85, -1)); // Зачёркнутая цена
        $offer->addChild('name', htmlspecialchars($product->name));
        $offer->addChild('vendorCode', htmlspecialchars($sku));
        $offer->addChild('insalesStock', '');
        $offer->addChild('param', 'Акция');
        $offer->addChild('category', 'Акции');
        $unit = $offer->addChild('unit');
        $unit->addAttribute('sym', 'шт');
    }
}
