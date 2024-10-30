<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class DeployController extends Controller
{
    public function deploy(Request $request)
    {
        try {
            $secret = 'JDn20uDFp03mFUVkRLcmFWWv9A3QhVr+fKleFREcSQI=';
            $signature = $request->header('X-Hub-Signature');

            if ($signature) {
                $hash = "sha1=" . hash_hmac('sha1', $request->getContent(), $secret);
                if (!hash_equals($signature, $hash)) {
                    abort(403, 'Unauthorized');
                }
            }

            $process = new Process(['git', 'pull']);
            $process = new Process(['ssh-agent bash -c \'ssh-add ~/.ssh/id_rsa; git pull\'']);
            $process->setWorkingDirectory(base_path());
            $process->run();
            if (!$process->isSuccessful()) {
                Log::error('Ошибка при выполнении команды: ' . $process->getErrorOutput());
                throw new \Exception('Ошибка при выполнении команды: ' . $process->getErrorOutput());
            }


            $process = new Process(['composer', 'i']);
            $process->setWorkingDirectory(base_path());
            $process->run();
            if (!$process->isSuccessful()) {
                Log::error('Ошибка при выполнении команды: ' . [$process->getErrorOutput()]);
                throw new \Exception('Ошибка при выполнении команды: ' . $process->getErrorOutput());
            }

            Artisan::call('migrate');

            $process = new Process(['npm', 'install']);
            $process->setWorkingDirectory(base_path());
            $process->run();
            if (!$process->isSuccessful()) {
                Log::error('Ошибка при выполнении команды: ' . [$process->getErrorOutput()]);
                throw new \Exception('Ошибка при выполнении команды: ' . $process->getErrorOutput());
            }

            $process = new Process(['npm', 'run', 'prod']);
            $process->setWorkingDirectory(base_path());
            $process->run();
            if (!$process->isSuccessful()) {
                Log::error('Ошибка при выполнении команды: ' . [$process->getErrorOutput()]);
                throw new \Exception('Ошибка при выполнении команды: ' . $process->getErrorOutput());
            }

            Log::info('Развёртование обновлений прошло успешно');
            return response()->json(['message' => 'Successfully deployed.']);
        } catch (\Exception $e) {
            Log::emergency('Во время развёртования обновления произошла ошибка');
        }
    }
}
