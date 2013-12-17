setTimeout(function() {
    (ScriptRunner=function(v){var i,n,l,p,s,c={},w=window,t=setTimeout;for(i=0,n=v.length;i<n;++i){l=v[i];if(typeof l=='string'||l instanceof String){l={'':l}}for(p in l){if(p){if(w[p]){continue}c[p]=1}s=document.createElement('script');s.type='text/javascript';s.charset='UTF-8';s.src=l[p];document.documentElement.appendChild(s)}}return function(f){if(f){t(function(){for(p in c){if(!w[p]){return t(arguments.callee,300)}}f()},0)}return arguments.callee}})
    ([
        '//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js',
        'getweather.js',
        'action.js'
    ])
}, 1500);



