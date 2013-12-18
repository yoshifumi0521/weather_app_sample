function getWeather(){
    var df = $.Deferred();
    //IEでconsoleを使うとエラーが起こることがある。
    if(!("console" in window))
    {
        window.console = {};
        window.console.log = function(){};
        window.console.debug = function(){};
    }

    //IEでのindexOf対応
    if(!Array.indexOf) {
      Array.prototype.indexOf = function(o) {
        for(var i in this) {
          if(this[i] == o) {
            return i;
          }
        }
        return -1;
      }
    }

    //パラメーターで郵便番号を受け取る。
    var params = getUrlVars();
    var postcode = params["zip1"]+params["zip2"];

    //APIの処理を直列処理する。
    getLocation(postcode).pipe(getLocalCode).pipe(getWeatherData).pipe(getWeatherPattern).done(function(data){
        //処理が終わったあとにする処理
        df.resolve(data);
    }).fail(function(data){
        //すべたの処理がうまくいなかったとき
        df.reject(data);
    });
    return df.promise();
}



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
    var df = $.Deferred();
    //YahooアプリケーションのID
    var yahool_app_id = "dj0zaiZpPTBWWDNjMVpIM0xUUiZzPWNvbnN1bWVyc2VjcmV0Jng9MTI-";
    var yahoo_api_url = 'http://search.olp.yahooapis.jp/OpenLocalPlatform/V1/zipCodeSearch?appid='+yahool_app_id+'&query='+postcode+'&output=json&callback=?';
    //Ajax通信をする。
    $.ajax({
        type: "get",
        url: yahoo_api_url,
        dataType: 'json'
    })
    //YahooAPIで位置情報を取得した後の処理。
    .done(function(data){
        if(data["ResultInfo"]["Count"] == 0)
        {
            df.reject("YahooAPIに接続できているが、一つも天気の情報が取得できない");
        }
        else
        {
            var location = data["Feature"][0]["Geometry"]["Coordinates"];
            df.resolve(location);
        }
    })
    .fail(function(){
        df.reject("yahooAPIのエラー");
    });
    return df.promise();
}

//位置情報を使って、地域コードを取得する。
function getLocalCode(location){
    var df = $.Deferred();
    var lat = location.split(",")[1];
    var lng = location.split(",")[0];
    //localcode.jsonを読み込む
    $.ajax({
        url: 'api/localcode.json',
        type: "GET",
        dataType: 'json'
    }).done(function(data){
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
        df.resolve(local_code);
    }).fail(function(){
        df.reject("api/localcode.jsonのエラー");
    });
    return df.promise();
}

//天気予報の情報を取得
function getWeatherData(local_code){
    var df = $.Deferred();
    var weather_api_domain = "w001.tenkiapi.jp";
    var weather_api_userid = "ddd01898684d17699c30acba0bf1386702646231";

    //今日の時刻と曜日を調べる。
    var now = new Date();
    var nYear = now.getFullYear();
    var nMonth = now.getMonth();
    var hour = now.getHours();
    var nDate = now.getDate();
    var day_array = new Array("日","月","火","水","木","金","土");
    var day_number = now.getDay();
    var day = day_array[day_number];
    var weather;
    var n;
    //土曜日の17時で分ける
    if(day == "土")
    {
        //17時以前
        if(hour < 17 )
        {
            // 天気予報のデータを取得する。
            $.ajax({
                url: 'http://'+weather_api_domain+'/'+weather_api_userid+'/daily/?p1='+local_code+'&p2=today&type=jsonp&callback=?',
                type: "GET",
                dataType: 'json'
            })
            //天気予報APIを取得した場合
            .done(function(data){
                weather = data['daily'];
                df.resolve(weather["telop"]);
            }).fail(function(){
                df.reject("天気予報APIのエラー");
            });
        }
        //17時以降
        else
        {
            var saturday = new Date(nYear,nMonth,nDate+7);
            var saturday_date = saturday.getFullYear()+"-"+(saturday.getMonth()+1)+"-"+saturday.getDate();
            $.ajax({
                url: 'http://'+weather_api_domain+'/'+weather_api_userid+'/weekly/?p1='+local_code+'&type=jsonp&callback=?',
                type: "GET",
                dataType: 'json'
            })
            //天気予報APIを取得した場合
            .done(function(data){
                var weathers_arr =  data["weekly"]["weather"];
                jQuery.each(weathers_arr,function(i,val)
                {
                    if(val["date"] == saturday_date)
                    {
                        weather = val;
                        df.resolve(weather["telop"]);
                    }
                });
            }).fail(function(){
                df.reject("天気予報APIのエラー");
            });
        }

    }
    else
    {
        //今週の土曜日の天気を調べる。
        var saturday = new Date(nYear,nMonth,nDate+6-day_number);
        var saturday_date = saturday.getFullYear()+"-"+(saturday.getMonth()+1)+"-"+saturday.getDate();
        $.ajax({
            url: 'http://'+weather_api_domain+'/'+weather_api_userid+'/weekly/?p1='+local_code+'&type=jsonp&callback=?',
            type: "GET",
            dataType: 'json'
        })
        //天気予報APIを取得した場合
        .done(function(data){
            var weathers_arr =  data["weekly"]["weather"];
            jQuery.each(weathers_arr,function(i,val)
            {
                if(val["date"] == saturday_date)
                {
                    weather = val;
                    df.resolve(weather["telop"]);
                }
            });
        })
        //エラーの場合
        .fail(function(){
            df.reject("天気予報APIのエラー");
        });
    }
    return df.promise();
}

//天気のパターンを取得するメソッド
function getWeatherPattern(weather_telop)
{
    var df = $.Deferred();
    $.ajax({
        url: 'api/weatherpattern.json',
        type: "GET",
        dataType: 'json'
    }).done(function(data){
        jQuery.each(data, function(i,val)
        {
            if(weather_telop == val["code"])
            {
                df.resolve(val["weather"]);
            }
        });
    })
    //エラーの場合
    .fail(function(){
        df.reject("api/weatherpattern.jsonのエラー");
    });
    return df.promise();
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