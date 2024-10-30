<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Imports\CsvCompareMoneyImport;
use App\Models\V1\VovaDengi;
use App\Models\VovaDenginUpload;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CSVCompareController extends Controller
{
    public function uploadFile(Request $request)
    {
        $file = $request->file('file');
        $import = new CsvCompareMoneyImport();
        Excel::import($import, $file, null);
        return response(status: 200);
    }

    public function getData(Request $request)
    {
        $newUploadIndex = $request->get('upload_index', 1);
        $oldUploadIndex = $newUploadIndex - 1;

        $newData = VovaDengi::select('vova_dengi.*', 'vova_dengin_uploads.chacked as chacked')
            ->where('upload_index', $newUploadIndex)
            ->leftJoin('vova_dengin_uploads', 'vova_dengi.upload_index', '=', 'vova_dengin_uploads.id')
            ->get();

        $oldData = VovaDengi::select('vova_dengi.*', 'vova_dengin_uploads.chacked as chacked')
            ->where('upload_index', $oldUploadIndex)
            ->leftJoin('vova_dengin_uploads', 'vova_dengi.upload_index', '=', 'vova_dengin_uploads.id')
            ->get();

        $newDataAnomalies = [];
        $oldDataAnomalies = [];

        $excludeAttributes = ['id', 'created_at', 'updated_at', 'upload_index', 'comment'];

        $compareModels = function ($model1, $model2, $excludeAttributes) {
            foreach ($model1->getAttributes() as $key => $value) {
                if (in_array($key, $excludeAttributes)) {
                    continue;
                }
                if ($model1[$key] !== $model2[$key]) {
                    return false;
                }
            }
            return true;
        };

        foreach ($newData as $newModel) {
            $found = false;
            foreach ($oldData as $oldModel) {
                if ($compareModels($newModel, $oldModel, $excludeAttributes)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $newDataAnomalies[] = $newModel;
            }
        }

        foreach ($oldData as $oldModel) {
            $found = false;
            foreach ($newData as $newModel) {
                if ($compareModels($oldModel, $newModel, $excludeAttributes)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $oldDataAnomalies[] = $oldModel;
            }
        }

        return [
            'new_data_anomalies' => $newDataAnomalies,
            'old_data_anomalies' => $oldDataAnomalies,
        ];
    }

    public function getUploads()
    {
        return VovaDenginUpload::select(['id as upload_index', 'created_at', 'chacked'])->orderBy('id', 'desc')->get();
    }

    public function updateComment(Request $request)
    {
        $id = $request->get('id');
        $comment = $request->get('comment');
        VovaDengi::where('id', $id)->update(['comment' => $comment]);
        return response(status: 200);
    }

    public function removeAll()
    {
        VovaDengi::truncate();
        VovaDenginUpload::truncate();
        return response(status: 200);
    }

    public function getActual()
    {
        $lastUpliadIndex = VovaDengi::max('upload_index');
        $actualData = VovaDengi::where('upload_index', $lastUpliadIndex)->get();
        return $actualData;
    }

    public function checkUpload(Request $request)
    {
        $upload = VovaDenginUpload::find($request->get('id'));
        $upload->chacked = !$upload->chacked;
        $upload->save();
        return response(status: 200);
    }
}
