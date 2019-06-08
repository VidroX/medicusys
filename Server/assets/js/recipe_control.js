let btnYes = $("#btnYes");
let btnNo = $("#btnNo");
let recipeDiv = $(".recipe-div");
let recipeBlock = $(".recipe-block");

let selected = -1;
let item = null;

$(document).ready(function () {
    recipeDiv.on('click', '.delete-button', function () {
        selected = $(this).attr('id');
        item = $(this).closest('.recipe');
    });

    btnYes.on('click', function () {
        if(selected !== -1 && item != null) {
            let fd = new FormData();
            fd.append('csrf_name', header.csrf_name);
            fd.append('csrf_value', header.csrf_value);
            fd.append('patient_id', patient_id);
            fd.append('recipe_id', selected);

            $.ajax({
                url: app_delete_url,
                data: fd,
                processData: false,
                contentType: false,
                type: 'POST',
                success: function (data) {
                    if (data.status === 27) {
                        item.hide(300);
                        setTimeout(function () {
                            item.remove();
                            if(recipeBlock.children().length <= 0) {
                                let appendix = '<div class="row align-items-start margin-top-big">' +
                                    '                    <div class="col-md-12 shaded-container">' +
                                    '                        <p>'+app_recipes_empty+'</p>' +
                                    '                    </div>' +
                                    '                </div>';
                                recipeBlock.append(appendix);
                            }
                            selected = -1;
                            item = null;
                        }, 300);
                    } else {
                        alert(data.message);
                    }
                },
                error: function (data) {
                    console.log(data);
                    selected = -1;
                    item = null;
                },
                complete: function (jqXHR) {
                    selected = -1;
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
        }
    });

    btnNo.on('click', function () {
        selected = -1;
        item = null;
    });
});