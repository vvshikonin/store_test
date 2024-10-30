<?php

namespace App\Jobs;

use App\Services\XMLGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use Exception;

class GenerateXMLJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1200;

    /**
     * Execute the job.
     *
     * @param App\Services\XMLGeneratorService $xmlGeneratorService
     *
     * @return void
     */
    public function handle(XMLGeneratorService $xmlGeneratorService)
    {
        $xmlGeneratorService->generateXml();
        Log::info('ICML-каталоги успешно сгенерированы в '.date('H:i:s d.m.Y'));
    }
}
