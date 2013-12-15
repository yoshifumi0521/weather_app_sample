<!DOCTYPE html>
<html lang="ja">
<head>
    <title>天気予報APIのサンプル</title>
    <meta charset="UTF-8" />
    <!-- jQueryを、Google Libraries APIで読み取る。最新版の1.8.3を読み取る。 -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script type="text/javascript" src="library/jsdeferred.js"></script>

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

<script>
    $(function(){

        //パラメーターで郵便番号を受け取る。
        var params = getUrlVars();
        var postcode = params["zip1"]+params["zip2"];
        $("#postcode").html(postcode);
        //API通信を直列で実行
        getLocation(postcode).pipe(getLocalCode).pipe(getWeatherData).done(getWeatherPattern);


    });

    //urlのパラメーターを取得するメソッド
    function getUrlVars()
    {
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }

    //yahooAPIを使って、郵便番号から位置情報を取得する
    function getLocation(postcode){
        console.log("郵便番号から位置情報を取得スタート");
        var df = $.Deferred();
        var yahool_app_id = "dj0zaiZpPTBWWDNjMVpIM0xUUiZzPWNvbnN1bWVyc2VjcmV0Jng9MTI-";
        var yahoo_api_url = 'http://search.olp.yahooapis.jp/OpenLocalPlatform/V1/zipCodeSearch?appid='+yahool_app_id+'&query='+postcode+'&output=json&callback=?';
        //Ajax通信をする。
        $.ajax({
            type: "GET",
            url: yahoo_api_url,
            dataType: 'json'
        })
        //YahooAPIで位置情報を取得した後の処理。
        .next(function(data){
            var location = data["Feature"][0]["Geometry"]["Coordinates"];
            $("#location").html(location);
            df.resolve(location);
        })
        //取得できなかったとき。例えば、郵便番号が有効でなかった場合など。
        .error(function(status) {
            alert("エラーが発生しました");
        });

        return df.promise();
    }

    //位置情報を使って、地域コードを取得する。
    function getLocalCode(location){
        console.log("地域コードを取得スタート");
        var df = $.Deferred();
        var lat = location.split(",")[1];
        var lng = location.split(",")[0];
        //localcode.jsonを読み込む
        $.ajax({
            url: 'localcode.json',
            type: "GET",
            dataType: 'json'
        }).next(function(data){
            var local_code;
            var prefecture;
            var city;
            var min_distance = 1000000000000000000000000;
            //ループさせて、一番距離が近い地域コードを取得
            jQuery.each(data,function(i,val)
            {
                var distance = cal_distance(lat,lng,val["latLng"]["lat"],val["latLng"]["lng"]);
                //現在の最小値よりも小さい値が出たら
                if ( min_distance > distance) {
                    min_distance　= distance;
                    local_code = val["code"];
                    prefecture = val["prefecture"];
                    city = val["city"];
                }
            });
            $("#local_code").html(local_code);
            $("#prefecture").html(prefecture);
            $("#city").html(city);
            df.resolve(local_code);
        })
        .error(function(status) {
            alert("エラーが発生しました");
        });
        return df.promise();
    }

    //天気予報の情報を取得
    function getWeatherData(local_code){
        console.log("天気予報APIで、天気予報情報を取得");
        var df = $.Deferred();
        var weather_api_domain = "w001.tenkiapi.jp";
        var weather_api_userid = "ddd01898684d17699c30acba0bf1386702646231";
        $.ajax({
            url: 'http://'+weather_api_domain+'/'+weather_api_userid+'/weekly/?p1='+local_code+'&type=jsonp&callback=?',
            type: "GET",
            dataType: 'json'
        })
        //天気予報APIを取得した場合
        .next(function(data){
            console.log();
            //今日の時刻と曜日を調べる。
            var hiduke = new Date();
            var year = hiduke.getFullYear();
            var month = hiduke.getMonth()+1;
            var hour = hiduke.getHours();
            var date = hiduke.getDate();
            var day_array = new Array("日","月","火","水","木","金","土");
            var day_number = hiduke.getDay();
            var day = day_array[day_number];
            $("#date").html(month+"-"+date+"-"+hour+":00"+"("+day+")");
            // 土曜日の天気の情報を取得する。
            var weathers_arr =  data["weekly"]["weather"];
            var weather;
            var n;
            //土曜日か、それ以外で判定する。
            if(day == "土")
            {
                console.log("土曜日");
                //ここがちょっと不明



            }
            else
            {
                console.log("土曜日以外");
                //今週の土曜日の天気を調べる。
                var saturday_data = year+"-"+month+"-"+(date+6-day_number);
                jQuery.each(weathers_arr,function(i,val)
                {
                    if(val["date"] == saturday_data)
                    {
                        weather = val;
                    }
                });
            }
            $("#weather_forecast_date").html(weather["date"]+"(土)");
            $("#weather_forecast_telop").html(weather["telop"]);
            $("#weather_forecast_wDescription").html(weather["wDescription"]);
            df.resolve(weather["telop"]);
        })
        .error(function(status) {
            alert("エラーが発生しました");
        });
        return df.promise();
    }

    //天気のパターンを取得するメソッド
    function getWeatherPattern(weather_telop)
    {
        console.log("天気のパターンを取得-");
        $.ajax({
            url: 'weatherpattern.json',
            type: "GET",
            dataType: 'json'
        }).next(function(data){
            jQuery.each(data, function(i,val)
            {
                if(weather_telop == val["code"])
                {
                    $("#weather_forecast_pattern").html(val["weather"]);
                }
            });
        }).error(function(status) {
            alert("エラーが発生しました");
        });
    }

    //緯度経度の2点間の距離の計算をする。
    function cal_distance(lat1, lon1, lat2, lon2){
        //ラジアンに変換
        var a_lat = lat1 * Math.PI / 180;
        var a_lon = lon1 * Math.PI / 180;
        var b_lat = lat2 * Math.PI / 180;
        var b_lon = lon2 * Math.PI / 180;

        // 緯度の平均、緯度間の差、経度間の差
        var latave = (a_lat + b_lat) / 2;
        var latidiff = a_lat - b_lat;
        var longdiff = a_lon - b_lon;

        //子午線曲率半径
        //半径を6335439m、離心率を0.006694で設定してます
        var meridian = 6335439 / Math.sqrt(Math.pow(1 - 0.006694 * Math.sin(latave) * Math.sin(latave), 3));

        //卯酉線曲率半径
        //半径を6378137m、離心率を0.006694で設定してます
        var primevertical = 6378137 / Math.sqrt(1 - 0.006694 * Math.sin(latave) * Math.sin(latave));

        //Hubenyの簡易式
        var x = meridian * latidiff;
        var y = primevertical * Math.cos(latave) * longdiff;

        return Math.sqrt(Math.pow(x,2) + Math.pow(y,2));
    }








</script>




