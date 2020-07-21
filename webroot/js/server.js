// Toggle the way we load VOMS into the provisioner
function toggle_server_load_mode(selected_val) {
    switch (selected_val) {
        case 'B':
            $('.bulk-mode').show();
            $('.single-mode').hide();
            break;
        case 'S':
            $('.bulk-mode').hide();
            $('.single-mode').show();
            break;
        default:
            break;
    }
}

function requestMode() {
    if ($('.bulk-mode').is(':visible')
        && $('.single-mode').is(':hidden')) {
        bulkVomsGet($('#bulkURL').val());
    }
}

function bulkVomsGet(url) {
    // Use this approach as a failover. First do the straight forward way
    fetch('https://jsonp.afeld.me/?url=' + url)
        .then(response => {
            return response.json();
        })
        .then((data) => {
            // Now i have my data
            console.log(data);
            parseJsonVoms(data);
        })
        .catch();
}

function parseJsonVoms(data) {
    $.each(data, (index, value) => {
       console.log(value);
       console.log(value.VOName);
    });
}