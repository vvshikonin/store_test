<?php

namespace App\Logging;

use Monolog\Logger;
use Illuminate\Http\Request;

use Monolog\Handler\AbstractProcessingHandler;
use phpDocumentor\Reflection\PseudoTypes\True_;

class DiscordLogger
{
    protected $request;
    protected $sequence;

    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    public function __invoke(array $config)
    {
        $logger = new Logger('Discord');
        $handler = new DiscordLoggerHandler($config['level'], $config);


        $logger->pushHandler($handler);

        collect($logger->getHandlers())->each(function ($handler) {
            $handler->pushProcessor(function ($record) {
                $record['extra'] = array_merge($record['extra'] ?? [], [
                    'user_id' => auth()->user() ? auth()->user()->id : null,
                    'user_agent' => $this->request->server('HTTP_USER_AGENT'),
                    'context' => $record['context'] ?? null,
                    'level_name' => $record['level_name'],
                ]);
                return $record;
            });
        });

        return $logger;
    }
}

class DiscordLoggerHandler extends AbstractProcessingHandler
{
    protected $config = [];

    public function __construct($level = Logger::DEBUG, array $config = [])
    {
        parent::__construct($level);
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */


    protected function write(array $record): void
    {
        $timestamp = date("c", strtotime("now"));
        $webhookurl = $this->config['webhook'];

        $message = $record['message'];

        $isFromXMLGenerator = isset($record['context']['exception']) && strpos($record['context']['exception'], 'GenerateXMLJob.php') !== false;

        if ($isFromXMLGenerator) {
            $message = "Ошибка при генерации ICML-каталога:\n" . $message;
        }

        $title = null;
        $hexcode = null;

        switch ($record['extra']['level_name']) {
            case 'WARNING':
                $title = 'ПРЕДУПРЕЖДЕНИЕ';
                $hexcode = "ffff00";
                break;
            case 'ERROR':
                $title = 'ОШИБКА';
                $hexcode = "ff0000";
                break;
            case 'EMERGENCY':
                $title = 'КРИТИЧЕСКИЙ СБОЙ';
                $hexcode = "ff5733";
                break;
        }

        $json_data = json_encode([
            // Сообщение

            // Ник бота который отправляет сообщение
            "username" => "Уведомление об ошибке на складе",
            "content" => "Работоспособность склада",

            // URL Аватара.
            // Можно использовать аватар загруженный при создании бота, или указанный ниже
            //"avatar_url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=512",

            // Преобразование текста в речь
            "tts" => false,

            // Загрузка файла
            //"file" => $file,

            // Массив Embeds
            "embeds" => [
                [
                    // Заголовок
                    "title" => $title,

                    // Тип Embed Type, не меняем ничего.
                    "type" => "rich",

                    "description" => request()->root(),

                    // Таймштамп, обязательно в формате ISO8601
                    "timestamp" => $timestamp,

                    // Цвет границы слева, в HEX
                    "color" => hexdec($hexcode),

                    // Дополнительные поля
                    "fields" => [
                        [
                            "name" => "Содержание",
                            "value" => $message,
                            "inline" => false
                        ]

                    ]
                ]
            ]

        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);


        $ch = curl_init($webhookurl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4); // Время ожидания подключения в секундах
        curl_setopt($ch, CURLOPT_TIMEOUT, 8); // Максимальное время выполнения запроса в секундах

        // Отключить проверку SSL сертификата (для отладки, см. ниже)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            \Log::debug('Ошибка при отправке сообщения в Discord: ' . $error);
        } else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode >= 400) {
                \Log::debug('Discord вернул ошибку: HTTP ' . $httpCode . ' - ' . $response);
            }
        }
        curl_close($ch);
    }
}
