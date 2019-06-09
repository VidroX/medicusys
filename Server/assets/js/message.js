let btnSend = $("#btnSend");
let form = $("#messageForm");
let btnSubmit = $("#btnSubmit");

let title = $("#title");
let message = $("#message");

$(document).ready(function () {
    form.on('submit', function (e) {
        e.preventDefault();

        let fd = new FormData();
        fd.append('csrf_name', header.csrf_name);
        fd.append('csrf_value', header.csrf_value);
        fd.append('patient_id', patient_id);
        fd.append('fcm_reg_token', fcm_token);
        fd.append('title', title.val());
        fd.append('message', message.val());

        $.ajax({
            url: app_send_url,
            data: fd,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function(data){
                if(data.status === 27) {
                    alert(app_message_sent);
                    window.location.href = window.location.origin + app_prev_url;
                }else{
                    alert(data.message);
                }
            },
            error: function(data){
                console.log(data);
            },
            complete: function (jqXHR) {
                let csrf = jqXHR.getResponseHeader('X-CSRF-Token');

                if (csrf) {
                    try {
                        header = $.parseJSON(csrf);
                    } catch (e) {
                        console.log(e);
                    }
                }
            }
        });
    });
    btnSend.on('click', function () {
        btnSubmit.click();
    });
});