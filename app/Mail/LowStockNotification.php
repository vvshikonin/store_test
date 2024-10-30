<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use App\Models\V1\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LowStockNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $product;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($productId)
    {
        $this->product = Product::find($productId);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $productName = $this->product->name;
        $skus = implode(', ', json_decode($this->product->sku_list, true));

        $content = "
            Уведомление о низком остатке на складе

            Наименование товара: {$productName}
            Артикулы товара: {$skus}

            Для данного товара в интернет-магазинах нужно убрать метку Акция и установить корректную цену РРЦ.

            Эта задача сформирована автоматически.

            Доступы:
            1) taskstoit@yandex.ru:
            https://lakme-shop.ru/
            https://deebot-russia.ru/
            http://bioline-russia.ru/
            http://bamix-russia.ru
            http://silga.ru
            https://harizma-professional.ru/
            http://tescoma.su/

            2) remoteuser@buy-domain.su:
            https://mizutani-scissors.ru/
            http://olivia-shop.ru
            https://swissdiamondrus.ru/
            https://swiss-diamond-russia.ru/
            https://niv-rus.ru/

            3) remoteuser2@buy-domain.su:
            http://jaguar-scissors.ru/
            https://valerarussia.ru/
            http://wahl-russia.ru/
            https://babylissrus.ru/
            https://moserrussia.ru/
            http://jura-russia.ru
            http://jbl-russia.ru/
        ";

        $subject = 'Изменение товаров и акций';

        return $this->subject($subject)
            ->text('emails.default', ['content' => $content])
            ->withSwiftMessage(function ($message) {
                $message->setCharset('UTF-8');
            });
    }
}
