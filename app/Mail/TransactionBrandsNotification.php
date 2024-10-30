<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TransactionBrandsNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $xmlCatalogs;

    public function __construct($xmlCatalogs)
    {
        $this->xmlCatalogs = $xmlCatalogs;
    }

    public function build()
    {
        // Log::debug(print_r($this->brands, true));
        if ($this->xmlCatalogs->count() > 0) {
            $catalogText = $this->xmlCatalogs->map(function ($xmlCatalog) {
                return $xmlCatalog->name . ": " . asset($xmlCatalog->xml_link);
            })->implode("\n");

            $content = "Список брендов и шаблонов брендов для обновления цен сгенерированными каталогами:\n\n" . $catalogText . "\n\nДополнительные условия:\n
            Kuppersbusch (хомаер не забирает, метка: уценка - мятая коробка, закуп + 15%):
            KE 9340.0 SR (sale price - 150580 / regular price - 179990) - если остаток на складе 0, то убрать упоминания уценки с товара и обновить по прайсу.
            
            JBL - минимальная стоимость акционного товара 5к (т.е. у всех товаров ниже 5к делаем sale price - 5000, regular price 5880). 
            По складу в автоматическом режиме пока нет функционала это делать, поэтому так же вместе с сайтом insales обновить эти товары jbl-russia.com!
            
            Бьюти технологии:
            Выполнять после обычного обновления акционных товаров!
            1) Выгрузить со склада список товарных позиций с фильтром по поставщику Бьюти технологии.
            2) Удалить в выгрузке всех поставщиков кроме Бьюти технологии.
            3) Сравнить прайс Бьюти технологии со складской выгрузкой и исключить в прайсе товары с нулевым остатком.
            4) Обновить сайты moserrussia.ru и wahl-russia.ru (сайты с Юр лицом ИП Грибанов) по прайсу Бьюти технологии.";

            // dd($content);
            // Пример прикрепления файла
            return $this->subject('Список брендов для обновления')
                ->text('emails.default', ['content' => $content]);
                // ->attach('storage/app/public/Бьюти технологии.xlsx');
        } else {
            $content = "За прошедшее время не обнаружено брендов для обновления";

            return $this->subject('Список брендов для обновления')
                ->text('emails.default', ['content' => $content]);
        }
    }
}
