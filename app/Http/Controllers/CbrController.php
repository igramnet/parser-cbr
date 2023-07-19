<?php

namespace App\Http\Controllers;

use App\Jobs\ParseData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CbrController extends Controller
{
    public function GetData(): void
    {
        for ($i = 0; $i < env('CUSTOM_PARSE_DAYS', 180); $i++) {
            $startDate = date('d/m/Y', strtotime('-' . $i . ' days'));
            ParseData::dispatch($startDate)->onQueue('cbr');
        }
    }

    private function CurrencyFormula(array $dataCurrent, array $prevData, string $needCurrency, array $defaultCurrencyData, array $defaultCurrencyPrevData): array
    {
        $resultPrev = [];
        for ($i = 0; $i < count($prevData); $i++) {
            if ($prevData[$i]['NumCode'] === $needCurrency) {
                $resultPrev[$prevData[$i]['NumCode']] = round(str_replace(',', '.', $prevData[$i]['Value']) / str_replace(',', '.', $defaultCurrencyPrevData['Value']), 4);
            }
        }

        $result = [];
        for ($i = 0; $i < count($dataCurrent); $i++) {
            if ($dataCurrent[$i]['NumCode'] === $needCurrency) {
                $currReal = round(str_replace(',', '.', $dataCurrent[$i]['Value']) / str_replace(',', '.', $defaultCurrencyData['Value']), 4);
                $difference = round($currReal - $resultPrev[$prevData[$i]['NumCode']], 6);
                if($difference > 0) {
                    $differenceMessage = ' за сутки прирост на ' . $difference;
                } elseif($difference < 0) {
                    $differenceMessage = ' за сутки упал на ' . $difference;
                } else {
                    $differenceMessage = ' за сутки курс не изменился';
                }
                $result[$dataCurrent[$i]['NumCode']] = $dataCurrent[$i]['Nominal'] . ' ' . $dataCurrent[$i]['CharCode'] . ': ' . $currReal . ' ' . $defaultCurrencyData['CharCode'] . ', ' . $differenceMessage;
            }
        }
        return $result;
    }

    public function GetCurrencies(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'charCode' => 'required|string',
            'defaultCharCode' => 'required|string',
        ]);

        try {
            $curDate = strtotime(str_replace('/', '-', $request['date']));
            $checkData = Cache::tags([$curDate])->has($curDate);
            if ($checkData === true) {
                $resultCurrencies = [];
                $data = Cache::tags([$curDate])->get($curDate);

                for ($i = 0; $i < count($data); $i++) {
                    $resultCurrencies[$data[$i]['NumCode']] = $data[$i]['CharCode'] . ' / ' . $data[$i]['Name'];
                }
                $currencyData = Cache::tags([$request['defaultCharCode'], $curDate])->get($curDate . ' - ' . $request['defaultCharCode']);
                $prevDate = $curDate - 60*60*24;
                $currencyDataPrevDate = Cache::tags([$request['defaultCharCode'], $prevDate])->get($prevDate . ' - ' . $request['defaultCharCode']);
                $dataPrevDate = Cache::tags([$prevDate])->get($prevDate);

                $resultData = $this->CurrencyFormula($data, $dataPrevDate, $request['charCode'], $currencyData, $currencyDataPrevDate);

                $message = 'Результат запроса';

                return view('welcome', [
                    'message' => $message,
                    'data' => $resultCurrencies,
                    'result' => true,
                    'tableData' => $resultData,
                    'date' => $request['date'],
                    'charCode' => $request['charCode'],
                    'defaultCharCode' => $request['defaultCharCode'],
                ]);
            } else {
                return redirect()->route('cbr.show')->with('message', 'Нет данных для заданных параметров');
            }
        } catch (\Throwable $e) {
            error_log($e->getMessage(), E_USER_NOTICE);
            return redirect()->route('cbr.show')->with('message', 'Проблема с кеширование');
        }

    }

    public function ShowIndex()
    {
        $resultCurrencies = [];
        $resultData = false;
        $currentDate = date('d.m.Y', time());
        try {
            $curDate = strtotime($currentDate);
            $checkData = Cache::tags([$curDate])->has($curDate);
            if ($checkData === true) {
                $data = Cache::tags([$curDate])->get($curDate);
                for ($i = 0; $i < count($data); $i++) {
                    $resultCurrencies[$data[$i]['NumCode']] = $data[$i]['CharCode'] . ' / ' . $data[$i]['Name'];
                }

                $resultData = true;
                $message = 'Актуальные курсы валют на ' . $currentDate . ' в нашей базе.';
            } else {
                $this->GetData();
                $message = 'Нет текущих курсов. Пожалуйста дождитесь окончания сбора данных или запустите его принудительно';
            }
        } catch (\Throwable $e) {
            error_log($e->getMessage(), E_USER_NOTICE);
            $message = 'Проблема с кеширование';
        }
        return view('welcome', [
            'message' => $message,
            'data' => $resultCurrencies,
            'result' => $resultData,
            'date' => '',
            'charCode' => '',
            'defaultCharCode' => env('CUSTOM_DEFAULT_CHAR_CODE', 'RUB'),
        ]);
    }

}
