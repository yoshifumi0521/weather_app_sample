$(function(){
    //ここから実行するコードを書いていく。
    //読み込むなどのマークを表示

    //getWeatherオブジェクトを取得
    var df = $.Deferred();
    getWeather().done(function(data){
        //天気予報の情報を取得したあとに行う処理
        console.log("処理終わったー");



    });


});






