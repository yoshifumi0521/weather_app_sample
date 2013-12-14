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


</body>

<script>
    $(function(){

        //パラメーターで郵便番号を受け取る。
        var params = getUrlVars();
        var postcode = params["zip1"]+params["zip2"];
        $("#postcode").html(postcode);

        //YahooAPIで、郵便番号を使って、位置情報を取得
        var yahool_app_id = "dj0zaiZpPTBWWDNjMVpIM0xUUiZzPWNvbnN1bWVyc2VjcmV0Jng9MTI-";
        var location;
        var yahoo_api_url = 'http://search.olp.yahooapis.jp/OpenLocalPlatform/V1/zipCodeSearch?appid='+yahool_app_id+'&query='+postcode+'&output=json&callback=?';
        $.ajax({
            type: "GET",
            url: yahoo_api_url,
            dataType: 'json'
        })
        //YahooAPIで位置情報を取得した後の処理。
        .next(function(data){
            location = data["Feature"][0]["Geometry"]["Coordinates"];
            $("#location").html(location);
            var lat = location.split(",")[1];
            var lng = location.split(",")[0];

            //次に地域コードを特定する地域コードAPIを取得
            return $.ajax({
                url: 'localcode.json',
                type: "GET",
                dataType: 'json'
            }).next(function(data){
                // console.log(data);
                var local_code;
                var prefecture;
                var city;
                var min_distance = 1000000000000000000000000;
                // console.log(data[0]["latLng"]["lat"]);
                //ループさせて、一番距離が近い地域コードを取得
                jQuery.each(data, function(i,val)
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

                //天気予報APIで情報を取得
                var weather_api_domain = "w001.tenkiapi.jp";
                var weather_api_userid = "ddd01898684d17699c30acba0bf1386702646231";
                return $.ajax({
                    url: 'http://'+weather_api_domain+'/'+weather_api_userid+'/weekly/?p1='+local_code+'&type=jsonp&callback=?',
                    type: "GET",
                    dataType: 'json'
                }).next(function(data){
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

                    //土曜日の天気の情報を取得する。
                    var weather;
                    var n;
                    //土曜日か、それ以外で判定する。
                    if(day = "土")
                    {
                        console.log("土曜日");
                        //11時前かどうかで判定する。
                        if(hour < 11)
                        {
                            console.log("11時以前");
                            n = 0;
                            weather = data["weekly"]["weather"][n];
                        }
                        else
                        {
                            console.log("11時以降");
                            n = 6;
                            weather = data["weekly"]["weather"][n];
                        }
                    }
                    else
                    {
                        console.log("土曜日以外");
                        //11時前かどうかで判定する。
                        if(hour < 11)
                        {
                            console.log("11時以前");
                            n = 6 - day_number;
                            weather = data["weekly"]["weather"][n];
                        }
                        else
                        {
                            console.log("11時以降");
                            n = 5 - day_number;
                            weather = data["weekly"]["weather"][n];
                        }
                    }
                    console.log(weather);
                    $("#weather_forecast_date").html(weather["date"]+"(土)");
                    $("#weather_forecast_telop").html(weather["telop"]);
                    $("#weather_forecast_wDescription").html(weather["wDescription"]);






                });


            });

        });




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




