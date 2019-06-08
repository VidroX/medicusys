let symptomsDiv = $("#symptomsDiv");
let inputAdd = $("#inputAdd");
let error = $(".invalid-tooltip");
let spinner = $("#loadingSpinner");
let pDiagnosisError = $("#pDiagnosisError");
let pSymptomsError = $("#pSymptomsError");
let possibleIssues = $("#possibleIssues");
let diagnosis = $("#diagnosis");

let firstStart = true;

let prevOccurrences = [];
let occurrences = [];
let symptoms = [];

function getDiagnoses() {
    pDiagnosisError.html(app_no_symptoms);
    pDiagnosisError.hide();
    pSymptomsError.hide();
    spinner.hide();
    possibleIssues.hide();
    possibleIssues.html("");

    $('#possibleDiagnosesModal').modal();

    if (occurrences.length <= 0) {
        pDiagnosisError.show();
    } else {
        spinner.show();

        let fd2 = new FormData();
        fd2.append('csrf_name', header.csrf_name);
        fd2.append('csrf_value', header.csrf_value);

        let symptomIds = [];
        $.each(occurrences, function (key, value) {
            let occurrence = value;
            let symptom = symptoms.find(function (element) {
                return element.name === occurrence;
            });
            if (symptom != null && symptom.api_id != null) {
                symptomIds.push(symptom.api_id);
            }
        });

        if (symptomIds.length > 0) {
            fd2.append("symptom_ids", JSON.stringify(symptomIds));
        }
        if (user_gender <= 2 && user_gender >= 1) {
            let gender = 'male';
            if (user_gender === 2) {
                gender = 'female';
            }
            fd2.append("patient_gender", gender);
        }
        if (user_birth_date != null && user_birth_date.length > 0) {
            fd2.append("patient_birthdate", user_birth_date);
        }
        fd2.append("language", language_code_api);

        $.ajax({
            url: app_diagnosis_url,
            data: fd2,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (data) {
                spinner.hide(150);

                let issues = data.data;
                if (issues == null || (issues != null && issues.length < 1)) {
                    pDiagnosisError.html(app_no_diagnoses);
                    pDiagnosisError.show();
                } else {
                    $.each(issues, function (key, value) {
                        let issue = value.Issue;

                        let appendix = '<div class="flex-container flex-card shaded admin-rounded margin-top-default">' +
                            '<div class="flex-card-left">' +
                            '<span><label for="diagnosisName" class="bold no-margin">' + app_name + '</label>: <span id="diagnosisName">' + issue.Name + '</span></span>' +
                            '<span><label for="diagnosisICDCode" class="bold no-margin">' + app_icd + '</label>: <span id="diagnosisICDCode">' + issue.Icd + '</span></span>' +
                            '<span><label for="diagnosisProfName" class="bold no-margin">' + app_prof_name + '</label>: <span id="diagnosisProfName">' + issue.ProfName + '</span></span>' +
                            '<span><label for="diagnosisAccuracy" class="bold no-margin">' + app_accuracy + '</label>: <span id="diagnosisAccuracy">' + issue.Accuracy + '%</span></span>' +
                            '</div>' +
                            '<div class="flex-card-right"><button class="btn btn-block btn-medicus btn-medicus-shaded select" data-dismiss="modal">' + app_select + '</button></div>' +
                            '</div>';

                        possibleIssues.append(appendix);
                    });
                    possibleIssues.show();
                }
            },
            error: function (data) {
                spinner.hide(150);
            },
            complete: function (jqXHR) {
                prevOccurrences = _.clone(occurrences);
                firstStart = false;
                spinner.hide(150);

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
}

function addSymptom(name) {
    occurrences.push(name);
    let symptom = symptoms.find(function (element) {
        return element.name === name;
    });
    let field = $('<div style="display: none" class="input-group mb-3">' +
        '<input disabled aria-label="' + app_symptom + '" type="text" name="symptoms[]" class="form-control symptom ' + (symptom != null && symptom.api_id > 0 ? 'symptom-api' : 'symptom-user') + '" placeholder="' + app_symptom_name + '" value="' + name + '" required>' +
        '<div class="input-group-append">' +
        '<button class="btn btn-outline-medicus medicus-no-min-width btn-remove" title="'+app_remove_symptom+'" type="button"><i class="fas fa-minus"></i></button>' +
        '</div>' +
        '</div>');
    symptomsDiv.append(field);
    field.show(150);
    inputAdd.val("");
}

$(document).ready(function () {
    let fd = new FormData();
    fd.append('csrf_name', header.csrf_name);
    fd.append('csrf_value', header.csrf_value);

    $.ajax({
        url: app_symptoms_url,
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        beforeSend: function(data) {
        },
        success: function(data){
            $.each(data, function (key, value) {
                let element = {
                    id: value.id,
                    api_id: value.api_id,
                    name: value.name
                };
                symptoms.push(element);
            });
            inputAdd.typeahead({
                source: data,
                autoSelect: false,
                changeInputOnSelect: false,
                selectOnBlur: false,
                minLength: 1,
                sorter: function(e) {
                    let pattern = /[a-zA-Z]/;

                    if(app_language === 'ru' || app_language === 'uk') {
                        pattern = /[\u0400-\u04FF]/;
                    }

                    return $.grep(e, function(value) {
                        return pattern.test(value.name);
                    });
                },
                afterSelect: function(args){
                    inputAdd.blur();
                    if(args != null && args.name != null && args.name.length > 0 && occurrences.indexOf(args.name) < 0) {
                        addSymptom(args.name);
                    }else{
                        pSymptomsError.html(app_already_added);
                        pSymptomsError.show();
                        inputAdd.addClass("is-invalid");
                    }
                }
            });
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

    symptomsDiv.on('click', '.btn-remove', function () {
        let item = $(this).closest(".mb-3");
        let input = item.find('.symptom');
        item.remove();

        if(occurrences.indexOf(input.val()) >= 0) {
            occurrences = $.grep(occurrences, function(value) {
                return value !== input.val();
            });
        }
    });

    symptomsDiv.on('click', '.btn-add', function () {
        inputAdd.removeClass('is-invalid');
        error.hide(150);
        if(inputAdd.val().length <= 0) {
            inputAdd.addClass('is-invalid');
            error.show(150);
        }
        if(inputAdd.val().length > 0 && occurrences.indexOf(inputAdd.val()) < 0) {
            addSymptom(inputAdd.val());
        }
    });

    inputAdd.keydown(function(e){
        if($(this).hasClass("is-invalid")) {
            pSymptomsError.hide();
            $(this).removeClass("is-invalid");
        }
        if(e.keyCode === 13) {
            e.preventDefault();
            let symptom = symptoms.find(function (element) {
                return element.name === inputAdd.val();
            });
            if(symptom == null) {
                $(".btn-add").click();
            }
        }
    });

    $("#possibleDiagnoses").on('click', function () {
        if(firstStart){
            getDiagnoses();
        }else if(!_.isEqual(prevOccurrences, occurrences) && !firstStart) {
            getDiagnoses();
        } else {
            $('#possibleDiagnosesModal').modal();
        }
    });

    possibleIssues.on('click', '.select', function () {
        let value = $(this).closest(".flex-container").find("#diagnosisProfName").text();
        diagnosis.val(value);
    });
});