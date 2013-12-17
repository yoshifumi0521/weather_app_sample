<!DOCTYPE html>
<html lang="ja">
<head>
    <title>天気予報APIのサンプル</title>
    <meta charset="UTF-8" />
    <!-- jQueryを、Google Libraries APIで読み取る。最新版の1.8.3を読み取る。 -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <!--IEでJSONが使えない場合があるので、JSONライブラリを読み込む(http://bestiejs.github.com/json3/)-->
    <script>
    if (!("JSON" in window)) {
        document.write('<script src="//cdnjs.cloudflare.com/ajax/libs/json3/3.2.4/json3.min.js"></scr'+'ipt>');
    }
    </script>
    <script src="getweather.js"></script>
    <script src="action.js"></script>

</head>
<body>

<h1>天気予報APIのサンプル</h1>

<strong>郵便番号</strong>は、<span id="postcode"></span><br>

<strong>位置情報(経度,緯度)</strong>は、<span id="location"></span><br>

<strong>地域コード</strong>は、<span id="local_code"></span><br>

<strong>prefecture</strong>は、<span id="prefecture"></span><br>

<strong>city</strong>は、<span id="city"></span><br>

<strong>今日</strong>は、<span id="date"></span><br>

<strong>表示する天気予報の日付は、</strong><span id="weather_forecast_date"></span><br>

<strong>表示する天気予報の天気テロップ番号は、</strong><span id="weather_forecast_telop"></span><br>

<strong>表示する天気予報の説明は、</strong><span id="weather_forecast_wDescription"></span><br>

<strong>表示する天気予報パターンは、</strong><span id="weather_forecast_pattern"></span><br>


</body>





