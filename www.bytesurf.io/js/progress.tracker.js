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
    
    // get season/episode/title/type into array
    let params = get_important_params();
    
    // add player time (seconds, as float) and total duration to params
    params['time'] = player.currentTime;
    params['time_total'] = player.duration;
    
    // determine whether or not it's completed (last 5% of video)
    let completion_time = player.duration - (player.duration * 0.05);
    params['completed'] = (player.currentTime >= completion_time).toString();
    
    // send update to save progress to server
    send_update('save_progress', params, function(r) { });
    last_progress_update = player.currentTime;
        
}

$(document).ready(function() {
    
    // get season/episode/title/type into array
    let params = get_important_params();
    
    // make sure we have title
    if (!array_key_exists('t', params))
        return;
    
    // send request and parse response
    send_update('get_progress', params, function(response) { 
        
        console.log('get_progress: ' + response);
        
        let data = response.split(',');
        if (data.length != 2)
            return;
        
        progress_time = data[0];
        progress_completed = data[1];
        
        if (progress_time == 0)
            return;
        
        let applyTime = window.setInterval(function() {
            if (player.readyState == 4) {
                player.currentTime = progress_time;
                window.clearInterval(applyTime);
            }
        }, 500);
        
    });
    
});