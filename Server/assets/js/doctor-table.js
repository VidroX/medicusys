let spinner = $("#loadingSpinner");
let error = $("#error");
let table = $("#patientsTable");
let prevButton = $("#prevPage");
let nextButton = $("#nextPage");
let page = 1;
let latestPage = false;

let header = {
    'csrf_name': app_csrf_name,
    'csrf_value': app_csrf_value,
};

function insertRows(rowObject) {
    $.each(rowObject, function( key, value ) {
        let user = value.user;
        let lastVisit = value.latestVisit == null ? "..." : value.latestVisit;
        let upcomingVisit = value.upcomingVisit == null ? "..." : value.upcomingVisit;

        let row =
            "<tr id=\"" + user.id + "\">" +
            "<th scope=\"row\">" + user.id + "</th>" +
            "<td>" + user.lastName + " " + user.firstName + " " + user.patronymic + "</td>" +
            "<td>" + lastVisit + "</td>" +
            "<td>" + upcomingVisit + "</td>" +
            "<td class=\"text-center\"><a class=\"medicus-color\" title=\""+ app_generate_report + "\" href=\"#\"><i class=\"fas fa-id-card\"></i></a></td>" +
            "</tr>";
        table.find("tbody").append(row);
    });
}

function loadData(page = 1) {
    clearTable();

    let fd = new FormData(this);
    fd.append('csrf_name', header.csrf_name);
    fd.append('csrf_value', header.csrf_value);
    fd.append('page', page);

    $.ajax({
        url: '/'+app_language_code+'/doctor/table/get',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        beforeSend: function(data) {
            error.hide(150);
            spinner.show(150);
        },
        success: function(data){
            spinner.hide();
            let curPage = data.data.page;
            let rows = data.data.rows;

            latestPage = !data.data.hasNext;

            if(latestPage) {
                nextButton.attr("disabled", true);
            }else{
                nextButton.attr("disabled", false);
            }

            insertRows(rows);
        },
        error: function(data){
            error.show(150);
        },
        complete: function (jqXHR) {
            spinner.hide();
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

function search(searchVal) {
    page = 1;
    let fd = new FormData(this);
    fd.append('csrf_name', header.csrf_name);
    fd.append('csrf_value', header.csrf_value);
    fd.append('searchVal', searchVal);
    fd.append('page', page);

    $.ajax({
        url: '/'+app_language_code+'/doctor/table/search',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        beforeSend: function(data) {
            error.hide(150);
            spinner.show(150);
        },
        success: function(data){
            spinner.hide();
            let curPage = data.data.page;
            let rows = data.data.rows;

            latestPage = !data.data.hasNext;

            insertRows(rows);
        },
        error: function(data){
            error.show(150);
        },
        complete: function (jqXHR) {
            spinner.hide();
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

function clearTable() {
    table.find("tbody").html("");
}

$(document).ready(function () {
    error.hide(150);

    if(page === 1) {
        prevButton.attr("disabled", true);
    }else{
        prevButton.attr("disabled", false);
    }

    prevButton.click(() => {
        page--;
        console.log(page);
        loadData(page);
        if(page === 1) {
            prevButton.attr("disabled", true);
        }else{
            prevButton.attr("disabled", false);
        }
    });
    nextButton.click(() => {
        page++;
        console.log(page);
        loadData(page);
        if(page === 1) {
            prevButton.attr("disabled", true);
        }else{
            prevButton.attr("disabled", false);
        }
    });

    loadData();
});