$(function(){
    //ここから実行するコードを書いていく。
    //読み込むなどのマークを表示

    //getWeatherオブジェクトを取得
    getWeather()
    //エラーの場合の処理
    .fail(function(data){
        console.log("エラーログ: "+data);
    })
    //処理が成功した場合
    .done(function(data){
        //天気予報の情報を取得したあとに行う処理。晴れなどの画像を表示
        console.log(data);
        $("#weather_forecast_pattern").html(data);
        //晴れの場合
        if(data == "sunny")
        {






        }
    });


});






