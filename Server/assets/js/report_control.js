let symptomsDiv = $("#symptomsDiv");
let inputAdd = $("#inputAdd");

$(document).ready(function () {
    symptomsDiv.on('click', '.btn-remove', function () {
        $(this).closest(".mb-3").remove();
    });

    inputAdd.keydown(function(e){
        if(e.keyCode === 13) {
            e.preventDefault();
            $(".btn-add").click();
        }
    });

    symptomsDiv.on('click', '.btn-add', function () {
        let closestInput = $(this).closest(".mb-3").find("input");
        if(closestInput.val().length > 0) {
            let field = $('<div style="display: none" class="input-group mb-3">' +
                '<input aria-label="' + app_symptom + '" type="text" name="symptoms[]" class="form-control symptom" placeholder="' + app_symptom_name + '" value="' + closestInput.val() + '" required>' +
                '<div class="input-group-append">' +
                '<button class="btn btn-outline-medicus medicus-no-min-width btn-remove" title="'+app_remove_symptom+'" type="button"><i class="fas fa-minus"></i></button>' +
                '</div>' +
                '</div>');
            symptomsDiv.append(field);
            field.show(150);
            closestInput.val("");
        }
    });
});