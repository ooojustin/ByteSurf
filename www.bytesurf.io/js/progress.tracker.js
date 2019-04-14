window.setInterval(update_progress, 5000);
var last_progress_update = 0;
var progress_season, progress_episode, progress_time, progress_completed;

function update_progress() {
    
    // make sure player object exists
    if (typeof player === 'undefined')
        return;
    
    // make sure an update is actually needed
    if (player.currentTime == last_progress_update)
        return;
    
    // get season/episode/title/type into array
    let params = get_important_params();
    
    // add player time (seconds, as float) to params
    params['time'] = player.currentTime;
    
    // determine whether or not it's completed (last 5% of video)
    let completion_time = player.duration - (player.duration * 0.05);
    params['completed'] = (player.currentTime >= completion_time).toString();
    
    // build url with given parameters
    let query = jQuery.param(params);
    let url = 'https://bytesurf.io/inc/updater.php?action=save_progress&' + query;
    
    // send web request (save progress) and store update time
    get_request(url, function(r) { });
    last_progress_update = player.currentTime;
        
}

$(document).ready(function() {
    
    // get season/episode/title/type into array
    let params = get_important_params();
    
    // make sure we have title
    if (!array_key_exists('t', params))
        return;
    
    // generate url to get information from
    let query = jQuery.param(params);
    let url = 'https://bytesurf.io/inc/updater.php?action=get_progress&' + query;
    
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