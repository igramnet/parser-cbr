<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ParserService
{

    private function ServiceGetDataOnDate(string $date): array
    {
        $xmlFile = file_get_contents('https://www.cbr.ru/scripts/XML_daily.asp?date_req=' . $date);
        $xmlObject = simplexml_load_string($xmlFile);

        $jsonFormatData = json_encode($xmlObject);
        $result = json_decode($jsonFormatData, true);
        $rubArray = [
            '@attributes' => [
                'ID' => 'R01020'
            ],
            "NumCode" => "643",
            "CharCode" => "RUB",
            "Nominal" => "1",
            "Name" => "Российский рубль",
            "Value" => "1",
        ];
        array_unshift($result['Valute'], $rubArray);

        if (isset($result['Valute'])) {
            return $result['Valute'];
        } else {
            dd($result);
        }
    }

    public function GetDataOnDate(string $date): void
    {

        try {
            $curData = strtotime(str_replace('/', '-', $date));
            $data = Cache::tags([$curData])->get($curData);
            if (!$data) {
                $data = $this->ServiceGetDataOnDate($date);
                Cache::tags([$curData])->forever($curData, $data);
            }
        } catch (\Throwable $e) {
            error_log($e->getMessage(), E_USER_NOTICE);
            $data = $this->ServiceGetDataOnDate($date);
        }

        for ($i = 0; $i < count($data); $i++) {
            $numCode = $data[$i]['NumCode'];
            Cache::tags([$numCode, $curData])->forever($curData . ' - ' . $numCode, $data[$i]);
        }
    }

}
