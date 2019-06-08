let btnYes = $("#btnYes");
let btnNo = $("#btnNo");

$(document).ready(function () {
    btnYes.on('click', function () {
        let fd = new FormData();
        fd.append('csrf_name', header.csrf_name);
        fd.append('csrf_value', header.csrf_value);
        fd.append('diagnosis_id', diagnosis_id);

        $.ajax({
            url: app_delete_url,
            data: fd,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function(data){
                if(data.status === 27) {
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
});