let url_obj = new URL(window.location.href);
let searchParams = new URLSearchParams(url_obj.search);
if (searchParams.has('party')) {
    var party = searchParams.get('party');
    window.setInterval(update_party_send, 5000);
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
    
    console.log(JSON.stringify(data));
    
}