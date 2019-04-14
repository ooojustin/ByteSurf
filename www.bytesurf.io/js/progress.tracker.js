
window.setInterval(update_progress, 10000);
var last_progress_update = 0;
var progress_season, progress_episode, progress_time, progress_completed;

function update_progress() {
    
    // make sure player object exists
    if (typeof player === 'undefined')
        return;
    
    // make sure an update is actually needed
    if (player.currentTime == last_progress_update)
        return;
    
    // get season/episode/title into array
    let params = get_important_params();
    
    // determine type, make sure it's valid
    params['type'] = get_media_type();
    if (!is_media_type_valid(params['type']))
        return;
    
    // set some vars based on type
    populate_seasons_and_episodes(params, params['type']);
    
    // add player time (seconds, as float) to params
    params['time'] = player.currentTime;
    
    // determine whether or not it's completed (last 5% of video)
    let completion_time = player.duration - (player.duration * 0.05);
    params['completed'] = (player.currentTime >= completion_time).toString();
    
    // build url with given parameters
    let query = jQuery.param(params);
    let url = 'https://bytesurf.io/inc/progress_tracker.php?action=save&' + query;
    
    // send web request (save progress) and store update time
    get_request(url, function(r) { });
    last_progress_update = player.currentTime;
        
}

$(document).ready(function() {
    
    // get season/episode/title into array
    let params = get_important_params();
    
    // make sure we have title
    if (!('t' in params))
        return;

    // get type, make sure it's valid
    params['type'] = get_media_type();
    if (!is_media_type_valid(params['type']))
        return;
    
    // set some vars based on type
    populate_seasons_and_episodes(params, params['type']);
    
    // generate url to get information from
    let query = jQuery.param(params);
    let url = 'https://bytesurf.io/inc/progress_tracker.php?action=get&' + query;
    
    // send request and parse response
    get_request(url, function(response) { 
        
        let data = response.split(',');
        if (data.length != 2)
            return;
        
        progress_time = data[0];
        progress_completed = data[1];
        
        if (progress_time == 0)
            return;
        
        let applyTime = window.setInterval(function() {
            if (player.readyState == 4) {
                console.log('ready');
                player.currentTime = progress_time;
                window.clearInterval(applyTime);
            }
        }, 500);
        
    });
    
});

function populate_seasons_and_episodes(params, type) {
    switch (type) {
        case 'show':
            if (!array_key_exists('s', params))
                params['s'] = 1;
            if (!array_key_exists('e', params))
                params['e'] = 1;
            break;
        case 'anime':
            params['s'] = -1;
            if (!array_key_exists('e', params))
                params['e'] = 1;
            break;
        case 'movie':
            params['s'] = -1;
            params['e'] = -1;
            break;
    }
}

function get_important_params() {
    
    // find season, episode, and title.
    // put them into a key/value array that we can build a query from.
    let find = ['s', 'e', 't'];
    let params = { };
         
    // initialize URL and URLSearchParams objects so we can get params from URL
    let url_obj = new URL(window.location.href);
    let searchParams = new URLSearchParams(url_obj.search);
    
    // put items from url into our own array
    find.forEach(function(f) {
        if (searchParams.has(f)) {
            params[f] = searchParams.get(f);
        }
    });
    
    return params;
    
}