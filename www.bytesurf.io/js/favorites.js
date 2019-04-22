// toggles whether or not the current video is favorited
function toggle_favorited() {
    
    let params = get_important_params();
    
    // make sure we have title
    if (!array_key_exists('t', params))
        return 'failed';
    
    // get type, make sure it's valid
    params['type'] = get_media_type();
    if (!is_media_type_valid(params['type']))
        return 'failed';
    
    // send request
    send_update('toggle_favorite', params, function(r) {
        console.log(r);
    });
    
}