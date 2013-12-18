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

<strong>表示する天気予報パターンは、</strong><span id="weather_forecast_pattern"></span><br>


</body>





