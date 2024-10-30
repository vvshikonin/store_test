<?php

namespace App\Imports;



use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\Log;
use App\Models\V1\VovaDengi;
use App\Models\VovaDenginUpload;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class CsvCompareMoneyImport implements ToCollection, WithCustomCsvSettings
{
    use Importable;


    public function getCsvSettings(): array
    {
        return [
            'input_encoding' => 'Windows-1251',
        ];
    }

    public function collection(Collection $rows)
    {
        $upload = new VovaDenginUpload();
        $upload->save();
        $uploadIndex = $upload->id;
        foreach ($rows as $kay => $row) {
            if ($kay == 0 || $kay == 1 || $kay == 2) {
                continue;
            }

            $vovaDengi = new VovaDengi();
            $vovaDengi->upload_index =  $uploadIndex;
            $vovaDengi->date =  $row[0];
            $vovaDengi->kontragent =  $row[1];
            $vovaDengi->rs1 =  $row[2];
            $vovaDengi->naimenovanie_banka1 =  $row[3];
            $vovaDengi->tip_deneg =  $row[4];
            $vovaDengi->rs2 =  $row[5];
            $vovaDengi->naimenovanie_banka2 =  $row[6];
            $vovaDengi->tip_documenta =  $row[7];
            $vovaDengi->nomer_documenta =  $row[8];
            $vovaDengi->tip_operacii =  $row[9];
            $vovaDengi->opisanie =  $row[10];
            $vovaDengi->postuplenie =  $row[11];
            $vovaDengi->spisano =  $row[12];
            $vovaDengi->komissiya =  $row[13];
            $vovaDengi->usn_dohod =  $row[14];
            $vovaDengi->usn_rashod =  $row[15];
            $vovaDengi->patent =  $row[16];
            $vovaDengi->save();
        }
    }
}
