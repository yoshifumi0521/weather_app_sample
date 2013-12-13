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




</body>

<script>
    $(function(){

        //パラメーターで郵便番号を受け取る。
        var params = getUrlVars();
        var postcode = params["postcode"];
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




