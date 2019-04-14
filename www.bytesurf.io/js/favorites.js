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
    
    // build query and url
    let query = jQuery.param(params);
    let url = 'https://bytesurf.io/inc/updater.php?action=toggle_favorite&' + query;
    
    // send request
    get_request(url, function(r) {
        console.log(r);
    });
    
}