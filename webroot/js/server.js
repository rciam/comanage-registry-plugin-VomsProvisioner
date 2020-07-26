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

function bulkVomsGet(url) {
    // todo:Use this approach as a failover. First do the straight forward way
    fetch('https://jsonp.afeld.me/?url=' + url, {
        method: 'GET',
    })
        .then(response => {
            return response.json();
        })
        .then((data) => {
            // Now i have my data
            parseJsonVoms(data);
            // dismiss modal
            $('#vomsModal').modal('hide');
            if($('.voms-server-list').length > 0) {
                $('#voms-server-clr-btn').show();
            }
        })
        .catch(error => {
            generateLinkFlash(error, "error", 5000);
            console.log('bulk fetch error: ' + error);
            $('#vomsModal').modal('hide');
        });
}

// Load and parse the data from the JSON endpoint
// Then append li and input elements in the body of the edit view
function parseJsonVoms(data) {
    $.each(data, (index, value) => {
        if (value.VOName === vo_name) {
            // Now i have my vo data
            let servers = value.VOMSServers;
            let servers_list = $('#co_voms_provisioner_servers_list');
            action_tbl = $('#CoVomsProvisionerTargetEditForm').attr('action').split('/');
            let co_voms_provisioner_target_id = action_tbl[action_tbl.length - 1];
            if (servers.length !== 0) {
                // Empty the server list
                servers_list.find('.voms-server-list').remove();
                // Remove all the hidden fields
                $('.voms-server-list-input').remove();
            }

            $.each(servers, (index, server) => {
                protocol = '';
                if (server.Protocol == null || server.Protocol === '') {
                    // Assume that the protocol is https
                    protocol = 'https';
                }
                add_single_voms(co_voms_provisioner_target_id, protocol, server.HostName, server.Port, server.DN);
            });
        }
    });
}