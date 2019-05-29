/*
=========== WARNING ===========
Only include this script via HTML if $_SESSION['party'] is set.
*/

// ===== SETTINGS =====
const party_update_interval = 2;
var max_time_delta = 0.1;

// ===== DON'T TOUCH THESE =====
var last_message_id = -1;
var username = '';
var params = get_important_params();
var time_sync = { };
var users = { };

// make sure type is valid
console.log('type: ' + params['type']);
if (is_media_type_valid(params['type'])) {

    // get local users username
    send_update('get_username', null, function (username_raw) {
        let username_data = JSON.parse(username_raw);
        username = username_data.username;
    });

    // determine server time desync
    send_update('time_sync', { client_time: Date.now() }, function (time_sync_raw) {

        // store time_sync info from server before starting party stuff
        time_sync = JSON.parse(time_sync_raw);

        // start interval for updates
        var party_update_handler = new IntervalHandler(update_party_send, party_update_interval * 1000);
        party_update_handler.start(true);

    });

}

function time_acc() {
    return Date.now() + time_sync.server_time_delta;
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

    // NOTE: refreshing the page will force php to calculate new page url
    if (!correct) {
        location.reload();
        return false;
    }

    return true;

}

function insert_message_node(username, message_content) {

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
    message_node.appendChild(message_content_node);

    // add node to message container element (at the end)
    document.getElementById('message_container').appendChild(message_node);

}

function interpret_party_message_data(data_raw) {

    // parse message data
    let data = JSON.parse(data_raw);
    let new_messages = 0;

    // loop through messages and handle them
    data.forEach(function(message) {

        if (message.username == username && interpret_party_message_data.did_run)
            return;

        insert_message_node(message.username, message.message);
        new_messages++;

    });

    if (new_messages > 0 && interpret_party_message_data.did_run)
        document.getElementById('party_notification_sound').play();

    // update last_message_id
    // note: sorted by 'id' descending, so first item = highest id
    if (data.length > 0)
        last_message_id = data[data.length - 1].id;

    // make sure we know this function has already executed at least once
    interpret_party_message_data.did_run = true;

}

function send_party_chat_message(message) {
    let params = { 'message' : message };
    send_update('send_party_chat_message', params, function(raw) {
        let data = JSON.parse(raw);
        if (data.sent)
            insert_message_node(username, message);
        else
            console.log('send_party_chat_message failed: ' + data.reason);
    });
}

function get_users_txt(data_raw) {

    // store old user list
    let users_old = users;
    let users_txt = '';

    // parse json data into new user list
    users = JSON.parse(data_raw);

    // loop through users
    for (user in users) {

        // skip the user if their last update was > 10 seconds ago
        let user_time = users[user];
        if (time_acc() - user_time > 10000)
            continue;

        // add users name to the text version (to show in UI)
        users_txt += user + ', ';

    }

    // remove the last 2 characters (', ')
    if (users_txt.length > 0)
        users_txt = users_txt.substring(0, users_txt.length - 2);

    return users_txt;

}

function update_party_send() {

    // make sure player object exists
    if (typeof player === 'undefined')
        return;

    // add player time (seconds, as float) and current unix timestamp (ms, for improved accuracy)
    params['time'] = player.currentTime;
    params['timestamp'] = time_acc();

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
    let users_txt = get_users_txt(data.users);

    //determine whether or not host is playing
    let playing = data.playing == 1;

    // update elements displayed in party modal dialog
    if (document.getElementById('party-modal')) {
        document.getElementById('party-users').innerHTML = users_txt;
        document.getElementById('party-status').innerHTML = (playing) ? 'Playing' : 'Paused';
    }

    // if local user owns party, don't worry about syncing
    if (data.owner)
        return;

    // make sure party link is correct
    if (!ensure_party_link(data))
        return;

    // determine timestamp delta (will always be positive) & extra
    let timestamp_delta = (time_acc() - data.timestamp) / 1000; // in seconds
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

        // set time_delta to 0 because we just made an adjustment to playercurrentTime
        time_delta = 0;

    }

    // update desync text
    document.getElementById('party-desync').innerHTML = time_delta.toFixed(4) + "s (now) / " + max_time_delta.toFixed(2) + "s (max)";

    // make sure we're playing or paused accordingly
    if (playing && player.paused)
        player.play();
    if (!playing && !player.paused)
        player.pause();

}
