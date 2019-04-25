/*
=========== WARNING ===========
Only include this script via HTML if $_SESSION['party'] is set.
*/

// ===== SETTINGS =====
const party_update_interval = 5;
var max_time_delta = 0.1;

// ===== DON'T TOUCH THESE =====
var last_message_id = -1;

// get season/episode/title/type into array
var params = get_important_params();
    
// output/check type
console.log('type: ' + params['type']);
if (is_media_type_valid(params['type'])) {
    
    // start interval for updates
    var party_update_handler = new IntervalHandler(update_party_send, party_update_interval * 1000);
    party_update_handler.start(true);

    // execute without waiting, first time
    update_party_send();
    
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
    
    /*if (!correct) {
        
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
        return false;
        
    }*/
    
    // NOTE: refreshing the page will force php to calculate new page url
    if (!correct) {
        location.reload();
        return false;
    }
    
    return true;
    
}

function generate_message_node(username, message_content) {
    
    // create row element (whole message)
    let message_node = document.createElement('tr');
    
    // create data elements
    let username_node = document.createElement('td');
    let message_content_node = document.createElement('td');
    
    // store username/message in data e
    username_node.appendChild(document.createTextNode(username));
    message_content_node.appendChild(document.createTextNode(message_content));
    
    // insert data elements into row
    message_node.appendChild(username_node);
    message_node.appendChild(username_node);
    
    return message_node;
    
}

function interpret_party_message_data(data_raw) {
    
    // parse message data
    let data = JSON.parse(data_raw);
    
    // loop through messages and handle them 
    data.forEach(function(message) {
        console.log('[' + message.timestamp + '] message from ' + message.username + ': ' + message.message);
    });
    
    // update last_message_id
    // note: sorted by 'id' descending, so first item = highest id
    if (data.length > 0)
        last_message_id = data[0].id;
    
}

function send_party_chat_message(message) {
    let params = { 'message' : message };
    send_update('send_party_chat_message', params, function(raw) {
        let data = JSON.parse(raw);
        if (data.sent)
            party_update_handler.restart(true); // restart interval so we can download message instantly
        else
            console.log('send_party_chat_message failed: ' + data.reason);
    });
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
    
    // tell server the id of the last message we've received
    params['last_message_id'] = last_message_id;
    
    // send update with action, params, and callback
    send_update('party_update', params, update_party_receive);
    
}

function update_party_receive(data_raw) {
    
    let data = JSON.parse(data_raw);
    
    // handle party messages
    interpret_party_message_data(data.messages);
    
    // get the users in the party, display them
    let users = JSON.parse(data.users);
    let users_txt = '';
    for(user in users) {
        let user_time = users[user];
        if (Date.now() - user_time > 10000)
            continue;
        users_txt += user + ', ';
    }
    if (users_txt.length > 0)
        users_txt = users_txt.substring(0, users_txt.length - 2);
    
    //determine whether or not host is playing
    let playing = data.playing == 1;
    
    // update elements displayed in party modal dialog
    if (document.getElementById('party-modal')) {
        document.getElementById('party-users').innerHTML = users_txt;
        document.getElementById('party-status').innerHTML = (playing) ? 'Playing' : 'Paused';
    }
    
    // if local user owns party, don't worry about syncing
    if (data.owner.startsWith('true'))
        return;
    
    // make sure party link is correct
    if (!ensure_party_link(data))
        return;
    
    // determine timestamp delta (will always be positive) & extra
    let timestamp_delta = (Date.now() - data.timestamp) / 1000; // in seconds
    let time_extrapolated = parseFloat(data.time) + timestamp_delta;
    
    // set player time again if we're too out of sync
    let time_delta = Math.abs(time_extrapolated - player.currentTime);
    if (time_delta > max_time_delta) {
        
        // make adjustment to player time
        player.currentTime = time_extrapolated;
        
        // if owner is playing & they didn't skip (huge delta) increase maximum
        // this is to prevent really long periods of the browser trying to load & catch up
        if (playing && time_delta < 10)
            max_time_delta += 0.05;
        
    }
    
    // update desync text
    document.getElementById('party-desync').innerHTML = time_delta.toFixed(4) + "s (now) / " + max_time_delta.toFixed(2) + "s (max)";
    
    // make sure we're playing or paused accordingly
    if (playing && player.paused)
        player.play();
    if (!playing && !player.paused)
        player.pause();
    
}