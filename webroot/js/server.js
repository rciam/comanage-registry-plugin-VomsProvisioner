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
        bulkVomsGet($('#bulkURL').val().trim());
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
            $('#vomsAddModal').modal('hide');
            $('#voms-server-clr-btn').show();
        })
        .catch(error => {
            generateLinkFlash(error, "error", 5000);
            console.log('bulk fetch error: ' + error);
            $('#vomsAddModal').modal('hide');
        });
}

function addSingle() {

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
            debugger;
            if (servers.length !== 0) {
                // Empty the server list
                servers_list.find('.voms-server-list').remove();
                // Remove all the hidden fields
                $('.voms-server-list-input').remove();

            }

            $.each(servers, (index, server) => {
                let host = server.HostName;
                let port = server.Port;
                let dn = server.DN;
                let base_uri = 'https://' + host + ':' + port + '/' + vo_name;
                servers_list.prepend('<li class="voms-server-list"><b>Server: </b>' + base_uri + '</li>');
                last_element = $('form > input[type=hidden]').last();
                if(co_voms_provisioner_target_id !== '') {
                    $('<input class="voms-server-list-input" type="hidden" name="data[CoVomsProvisionerServer][' + index + '][co_voms_provisioner_target_id]" value=' + co_voms_provisioner_target_id + ' id="CoVomsProvisionerServerCoVomsProvisionerTargetId">').insertAfter(last_element);
                }
                $('<input class="voms-server-list-input" type="hidden" name="data[CoVomsProvisionerServer][' + index + '][host]" value=' + host + ' id="CoVomsProvisionerServerHost">').insertAfter(last_element);
                $('<input class="voms-server-list-input" type="hidden" name="data[CoVomsProvisionerServer][' + index + '][port]" value=' + port + ' id="CoVomsProvisionerServerPort">').insertAfter(last_element);
                $('<input class="voms-server-list-input" type="hidden" name="data[CoVomsProvisionerServer][' + index + '][dn]" value=' + dn + ' id="CoVomsProvisionerServerDn">').insertAfter(last_element);
            });
            debugger;
        }
    });
}