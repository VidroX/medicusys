let btnSave = $("#btnSave");
let form = $("#recipeForm");
let btnSubmit = $("#btnSubmit");

let iRecipe = $("#iRecipe");
let iDtdn = $("#iDtdn");
let iSigna = $("#iSigna");

$(document).ready(function () {
    form.on('submit', function (e) {
        e.preventDefault();

        let fd = new FormData();
        fd.append('csrf_name', header.csrf_name);
        fd.append('csrf_value', header.csrf_value);
        fd.append('patient_id', patient_id);
        fd.append('recipe_id', recipe_id);
        fd.append('rp', iRecipe.val());
        fd.append('dtdn', iDtdn.val());
        fd.append('signa', iSigna.val());

        $.ajax({
            url: app_edit_url,
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
    btnSave.on('click', function () {
        btnSubmit.click();
    });
});