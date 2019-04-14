let url_obj = new URL(window.location.href);
let searchParams = new URLSearchParams(url_obj.search);
if (searchParams.has('party')) {
    var party = searchParams.get('party');
    window.setInterval(update_party_send, 1000);
}

function update_party_send() {
    
    // make sure player object exists
    if (typeof player === 'undefined')
        return;
    
    // get season/episode/title/type into array
    let params = get_important_params();
    
    // make sure we know the party id
    params['party'] = party;
    
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
    
    // get the users in the party
    let users = JSON.parse(data.users);
    
    // determine timestamp delta (will always be positive) & extra
    let timestamp_delta = (Date.now() - data.timestamp) / 1000; // in seconds
    let time_extrapolated = parseFloat(data.time) + timestamp_delta;
    
    // set player time again if we're over 1 second out of sync
    let time_delta = Math.abs(time_extrapolated - player.currentTime);
    if (time_delta > 1)
        player.currentTime = time_extrapolated;
    
    // make sure we're playing or paused accordingly
    let playing = data.playing == '1';
    if (playing && player.paused)
        player.play();
    if (!playing && !player.paused)
        player.pause();
    
}