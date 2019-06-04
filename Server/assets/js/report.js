let form = $("#diagnosisForm");

let csrf_name = $("input[name=csrf_name]").val();
let csrf_value = $("input[name=csrf_value]").val();

let header = {
    'csrf_name': csrf_name,
    'csrf_value': csrf_value,
};

$(document).ready(function () {
    //spinner.hide(150);
    //error.hide(150);

    $("#submitBtn").on('click', function () {
        $("#formSubmitBtn").click();
    });

    form.on('submit', function (e) {
        e.preventDefault();

        let diagnosis = $("#diagnosis");
        let symptoms = $(".symptom");

        let fd = new FormData();
        fd.append("csrf_name", header.csrf_name);
        fd.append("csrf_value", header.csrf_value);
        fd.append("patient_user_id", app_patient_id);
        fd.append("diagnosis", diagnosis.val());

        let symptomsArr = [];
        $.each(symptoms, function (key, value) {
           let obj = $(value);
            symptomsArr.push(obj.val());
        });

        if(symptomsArr.length > 0) {
            fd.append("symptoms", JSON.stringify(symptomsArr));
        }

        $.ajax({
            url: app_post_url,
            data: fd,
            processData: false,
            contentType: false,
            type: 'POST',
            beforeSend: function(data) {
                //spinner.show(150);
            },
            success: function(data){
                //spinner.hide();
                console.log(data);
            },
            error: function(data){
                //error.show(150);
                //spinner.hide();
            },
            complete: function (jqXHR) {
                //spinner.hide();
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