<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

</head>
<body>
<h1>Добро пожаловать</h1>
<p>
    {!! $message !!}
</p>
@if($result === true)
    <div>
        <form action="{{ route('cbr.send') }}" method="post">
            @csrf
            <label for="date">Курс на
                <input type="date" id="date" name="date" value="{{ $date }}" placeholder="" required>
            </label>

            <label for="charCode">валюты
                <select id="charCode" name="charCode">
                    @foreach($data as $key => $value)
                        <option value="{{ $key }}" @if($key == $charCode) selected @endif>{{ $value }}</option>
                    @endforeach
                </select>
            </label>

            <label for="defaultCharCode">к валюте
                <select id="defaultCharCode" name="defaultCharCode">
                    @foreach($data as $key => $value)
                        <option value="{{ $key }}" @if($key == $defaultCharCode) selected @endif>{{ $value }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit">
                Получить
            </button>
        </form>
        {{ session('message') }}
        @if(isset($tableData))
            <table>
                @foreach($tableData as $element)
                <tr>
                    <td>
                        <strong>{!! $element !!}</strong>
                    </td>
                </tr>
                @endforeach
            </table>
        @endif
    </div>
@endif
    </body>
</html>
