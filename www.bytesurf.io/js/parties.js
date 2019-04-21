const party_update_interval = 5;
const max_time_delta = 0.1;

// note 'var party' must be set somewhere earlier in javascript
if (typeof party !== 'undefined') {
    
    // get season/episode/title/type into array, set party property
    var params = get_important_params();
    params['party'] = party;
    
    // start interval for updates
    window.setInterval(update_party_send, party_update_interval * 1000);
    
}

function ensure_party_link(data) {
    
    let correct = true;
    
    // make sure type, title, season, and episode all match
    if (params['type'] != data.type)
        correct = false;
    else if (params['t'] != data.title)
        correct = false;
    else if (params['s'] != data.season)
        correct = false;
    else if (params['e'] != data.episode)
        correct = false;
    
    if (!correct) {
        
        // store title in new url params
        let params_new = { 't': data.title };
        
        // if its an anime, store the episode.
        // if it's a show, store both episode and season.
        if (data.type == 'anime' || data.type == 'show')
            params_new['e'] = data.episode;
        if (data.type == 'show')
            params_new['s'] = data.season;
        
        // build new url from params
        let query = jQuery.param(params_new);
        let url = 'https://bytesurf.io/' + data.type + '.php?' + query;
        
        // redirect (simulate clicked link) to follow party host
        window.location.href = url;
        
    }
    
    return true;
    
}

function update_party_send() {
    
    // make sure player object exists
    if (typeof player === 'undefined')
        return;
    
    // add player time (seconds, as float) and current unix timestamp (ms, for improved accuracy)
    params['time'] = player.currentTime;
    params['timestamp'] = Date.now(); // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date/now
    
    // determine whether or not the client is playing
    params['playing'] = (!player.paused).toString();
    
    // build url with given parameters
    let query = jQuery.param(params);
    let url = 'https://bytesurf.io/inc/updater.php?action=party_update&' + query;
    
    // send web request (save progress) and store update time
    get_request(url, update_party_receive);
    
}

function update_party_receive(data_raw) {
    
    let data = JSON.parse(data_raw);
    
    // if local user owns party, don't worry about syncing
    if (data.owner == 'true')
        return;
    
    // make sure party link is correct
    if (!ensure_party_link(data))
        return;
    
    // get the users in the party
    let users = JSON.parse(data.users);
    
    // determine timestamp delta (will always be positive) & extra
    let timestamp_delta = (Date.now() - data.timestamp) / 1000; // in seconds
    let time_extrapolated = parseFloat(data.time) + timestamp_delta;
    
    // set player time again if we're over 1 second out of sync
    let time_delta = Math.abs(time_extrapolated - player.currentTime);
    if (time_delta > max_time_delta)
        player.currentTime = time_extrapolated;
    
    // make sure we're playing or paused accordingly
    let playing = data.playing == '1';
    if (playing && player.paused)
        player.play();
    if (!playing && !player.paused)
        player.pause();
    
}