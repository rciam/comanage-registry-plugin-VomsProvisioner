// todo: Investigate more on filereader. It would be better if we made this a promise
// Load certificate and key file from inside config page
function read_file(event, validation_string, input_textbox, file_type) {
    const fileList = event.target.files;
    if (!fileList || fileList.length < 1) {
        alert("File reading not supported in this browser");
        return;
    }
    const file = fileList[0];

    let fileReader = new FileReader();
    fileReader.onload = (event) => {
        // Reset status icons
        $('#' + file_type + '-done').hide();
        $('#' + file_type + '-error').hide();

        let payload = event.target.result;

        if(payload.includes(validation_string)) {
            let li = input_textbox.closest('li');
            let description = li.find('.field-desc');
            let description_text = description.html().split('<span')[0].trim();
            description_text = description_text.trim() + ' <span class="info-box success"><i class="material-icons">info</i>File Loaded</span>';
            description.html(description_text);
        }else{
            let li = input_textbox.closest('li');
            let description = li.find('.field-desc');
            let description_text = description.html().split('<span')[0].trim();
            description_text = description_text.trim() + ' <span class="info-box failed">Wrong File Input</span>';
            description.html(description_text);
            $('#' + file_type + '-error').show();
            return;
        }
        //Encode to base64
        let result = $.base64.encode(payload);
        // Add base64 encoded value in the textbox
        input_textbox.val(result);
        $('#' + file_type + '-done').show();
    }
    fileReader.onprogress = (event) => {
        progress_element = $('#'+file_type + '-progress');
        let progress_bar = progress_element.find('.progress-bar');
        progress_element.show();
        if (event.loaded && event.total) {
            // Add a progressbar
            const percent = Math.round((event.loaded / event.total) * 100);
            progress_bar.width(percent+'%');
            console.log('Progress: ' + percent);
            if( percent === 100) {
                progress_element.hide();
            }
        }
    }
    fileReader.onerror = (event) => {
        let li = input_textbox.closest('li');
        let description = li.find('.field-desc');
        let description_text = description.html().split('<span')[0].trim();
        description_text = description_text.trim() + ' <span class="info-box failed">Load File failed</span>';
        description.html(description_text);
        $('#' + file_type + '-error').show();
    }
    fileReader.readAsText(file);
}

// Generate flash notifications for messages
function generateLinkFlash(text, type, timeout) {
    var n = noty({
        text: text,
        type: type,
        dismissQueue: true,
        layout: 'topCenter',
        theme: 'comanage',
        timeout: timeout
    });
}