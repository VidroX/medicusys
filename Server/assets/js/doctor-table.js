let searchInput = $("#searchInput");
let spinner = $("#loadingSpinner");
let error = $("#error");
let table = $("#patientsTable");
let prevButton = $("#prevPage");
let nextButton = $("#nextPage");
let fromInput = $("#filterFrom");
let toInput = $("#filterTo");
let applyFilterButton = $("#applyFilter");
let globalPage = app_page_param;
let latestPage = false;
let allowSearch = true;
let firstSearch = true;
let useFilters = false;
let searchParam = "";
let fromVal = "";
let toVal = "";

let header = {
    'csrf_name': app_csrf_name,
    'csrf_value': app_csrf_value,
};

function insertRows(rowObject) {
    let size = rowObject.length;
    let maxLength = 10;
    $.each(rowObject, function( key, value ) {
        let user = value.user;
        let lastVisit = value.latestVisit == null ? "..." : value.latestVisit;
        let upcomingVisit = value.upcomingVisit == null ? "..." : value.upcomingVisit;

        let row =
            "<tr id=\"" + user.id + "\">" +
            "<td>" + user.lastName + " " + user.firstName + " " + user.patronymic + "</td>" +
            "<td>" + lastVisit + "</td>" +
            "<td>" + upcomingVisit + "</td>" +
            "<td class=\"text-center\">" +
            "<a class=\"medicus-color margin-right-default\" title=\"" + app_generate_report + "\" href=\"" + app_url_prefix + "/doctor/report/" + user.id + "/\"><i class=\"fas fa-id-card\"></i></a>" +
            "</td>" +
            "</tr>";

        if(user.fcmRegToken != null && user.fcmRegToken.length > 0) {
            row =
                "<tr id=\"" + user.id + "\">" +
                "<td>" + user.lastName + " " + user.firstName + " " + user.patronymic + "</td>" +
                "<td>" + lastVisit + "</td>" +
                "<td>" + upcomingVisit + "</td>" +
                "<td class=\"text-center\">" +
                "<a class=\"medicus-color margin-right-default\" title=\"" + app_generate_report + "\" href=\"" + app_url_prefix + "/doctor/report/" + user.id + "/\"><i class=\"fas fa-id-card\"></i></a>" +
                "<a class=\"medicus-color\" title=\"" + app_send_message + "\" href=\"" + app_url_prefix + "/doctor/message/" + user.id + "/\"><i class=\"fas fa-paper-plane\"></i></a>" +
                "</td>" +
                "</tr>";
        }
        table.find("tbody").append(row);
    });

    if(size < maxLength) {
        let computedLength = maxLength - size;
        for (let i = 1; i <= computedLength; i++) {
            let row =
                "<tr>" +
                "<td></td>" +
                "<td></td>" +
                "<td></td>" +
                "<td></td>" +
                "</tr>";
            table.find("tbody").append(row);
        }
    }
}

function loadData(page, searchVal, from, to, showSpinner) {
    if(page === undefined) {
        page = 1;
    }
    if(searchVal === undefined) {
        searchVal = null;
    }
    if(from === undefined) {
        from = null;
    }
    if(to === undefined) {
        to = null;
    }
    if(showSpinner === undefined) {
        showSpinner = true;
    }

    clearTable();

    let fd = new FormData();
    fd.append('csrf_name', header.csrf_name);
    fd.append('csrf_value', header.csrf_value);
    if(searchVal != null && searchVal.length > 0 && firstSearch){
        firstSearch = false;
        globalPage = 1;
        page = globalPage;
    }
    fd.append('page', page);
    if(searchVal != null) {
        fd.append('searchVal', searchVal);
    }
    if(from != null && to != null){
        fd.append('from', from);
        fd.append('to', to);
    }

    let rowsNum = 0;

    nextButton.attr("disabled", true);
    prevButton.attr("disabled", true);

    $.ajax({
        url: '/'+app_language_code+'/doctor/table/get',
        data: fd,
        processData: false,
        contentType: false,
        type: 'POST',
        beforeSend: function(data) {
            error.hide(150);
            if(showSpinner) {
                spinner.show(150);
            }
        },
        success: function(data){
            spinner.hide();
            let curPage = data.data.page;
            let rows = data.data.rows;

            latestPage = !data.data.hasNext;

            rowsNum = rows.length;

            let params = window.location.search;
            let newParams = params;
            if(params.length > 0 && params.indexOf("page") < 0){
                newParams = params+"&page="+curPage;
            }else if(params.length <= 0){
                newParams = "?page="+curPage;
            }
            if(params.indexOf("page") >= 0){
                newParams = replaceUrlParam(newParams, 'page', curPage);
            }

            window.history.replaceState(null, null, newParams);

            insertRows(rows);
        },
        error: function(data){
            clearTable();
            error.show(150);
            spinner.hide();
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

            if(latestPage) {
                nextButton.attr("disabled", true);
            }else{
                nextButton.attr("disabled", false);
            }
            if(page === 1){
                prevButton.attr("disabled", true);
            }else{
                prevButton.attr("disabled", false);
            }

            if(globalPage === 1 && rowsNum === 0) {
                clearTable();
                error.show(150);
            }else if(rowsNum === 0){
                globalPage = 1;
                if(searchVal != null && searchVal.length > 0) {
                    loadData(globalPage, searchVal, false);
                }else{
                    loadData(globalPage, null, false);
                }
            }
        }
    });
}

function clearTable() {
    table.find("tbody").html("");
}

function replaceUrlParam(url, paramName, paramValue) {
    if (paramValue == null) {
        paramValue = '';
    }
    let pattern = new RegExp('\\b('+paramName+'=).*?(&|#|$)');
    if (url.search(pattern)>=0) {
        return url.replace(pattern,'$1' + paramValue + '$2');
    }
    url = url.replace(/[?#]$/,'');
    return url + (url.indexOf('?')>0 ? '&' : '?') + paramName + '=' + paramValue;
}

function parseQueryString(query) {
    if(query.length > 0) {
        let vars = query.split("&");
        let query_string = {};
        for (let i = 0; i < vars.length; i++) {
            let pair = vars[i].split("=");
            let key = decodeURIComponent(pair[0]);
            let value = decodeURIComponent(pair[1]);
            if (typeof query_string[key] === "undefined") {
                query_string[key] = decodeURIComponent(value);
            } else if (typeof query_string[key] === "string") {
                query_string[key] = [query_string[key], decodeURIComponent(value)];
            } else {
                query_string[key].push(decodeURIComponent(value));
            }
        }
        return query_string;
    }else{
        return null;
    }
}

function sortTable(column, type) {
    let block = $('.table thead tr>th:eq(' + column + ')');
    let order = block.data('order');
    order = order === 'ASC' ? 'DESC' : 'ASC';
    block.data('order', order);

    $('.table tbody tr').sort(function(a, b) {

        a = $(a).find('td:eq(' + column + ')').text();
        b = $(b).find('td:eq(' + column + ')').text();

        switch (type) {
            case 'text':
                if(a === '' || b === '') {
                    return 0;
                }
                if(a === '...') {
                    return 0;
                }else if(b === '...') {
                    return -1;
                }
                return order === 'ASC' ? a.localeCompare(b) : b.localeCompare(a);
            case 'number':
                return order === 'ASC' ? a - b : b - a;
            case 'date':
                let dateFormat = function(dt) {
                    [d, m, y] = dt.split('.');
                    return [y, m - 1, d];
                };

                a = new Date(dateFormat(a));
                b = new Date(dateFormat(b));

                return order === 'ASC' ? a.getTime() - b.getTime() : b.getTime() - a.getTime();
        }

    }).appendTo('.table tbody');
}

function doSearch(obj) {
    let value = $(obj).val();

    let params = window.location.search;
    let newParams = params;
    if (params.length > 0 && params.indexOf("search") < 0) {
        newParams = params + "&search=" + encodeURIComponent(value);
    } else if (params.length <= 0) {
        newParams = "?search=" + encodeURIComponent(value);
    }
    if (params.indexOf("search") >= 0) {
        newParams = replaceUrlParam(newParams, 'search', encodeURIComponent(value));
    }

    window.history.replaceState(null, null, newParams);

    if (value == null || (value != null && value.length <= 0)) {
        searchParam = null;
        firstSearch = true;
    } else {
        searchParam = value;
    }

    if (allowSearch) {
        allowSearch = false;
        setTimeout(function () {
            allowSearch = true;
            if (searchParam != null && searchParam.length > 0) {
                loadData(globalPage, searchParam)
            } else {
                globalPage = 1;
                loadData(globalPage)
            }
        }, 1500)
    }
}

$(document).ready(function () {
    error.hide(150);

    let query = parseQueryString(window.location.search);
    if(query != null && query.search != null && query.search.length > 0){
        searchParam = query.search;
        searchInput.val(searchParam);
    }

    searchInput.on('paste keyup', function (e) {
        if (e.which <= 90 && e.which >= 48 || e.which >= 96 && e.which <= 105 || e.which === 8) {
            doSearch(this);
        }
    });

    if(globalPage === 1) {
        prevButton.attr("disabled", true);
    }else{
        prevButton.attr("disabled", false);
    }

    prevButton.click(function() {
        globalPage--;
        if(searchParam != null && searchParam.length > 0){
            if(fromVal != null && fromVal.length > 0 && toVal != null && toVal.length > 0 && useFilters){
                loadData(globalPage, searchParam, fromVal, toVal);
            }else{
                loadData(globalPage, searchParam);
            }
        }else{
            if(fromVal != null && fromVal.length > 0 && toVal != null && toVal.length > 0 && useFilters){
                loadData(globalPage, null, fromVal, toVal);
            }else{
                loadData(globalPage);
            }
        }
        if(globalPage === 1) {
            prevButton.attr("disabled", true);
        }else{
            prevButton.attr("disabled", false);
        }
    });
    nextButton.click(function() {
        globalPage++;
        if(searchParam != null && searchParam.length > 0){
            if(fromVal != null && fromVal.length > 0 && toVal != null && toVal.length > 0 && useFilters){
                loadData(globalPage, searchParam, fromVal, toVal);
            }else{
                loadData(globalPage, searchParam);
            }
        }else{
            if(fromVal != null && fromVal.length > 0 && toVal != null && toVal.length > 0 && useFilters){
                loadData(globalPage, null, fromVal, toVal);
            }else{
                loadData(globalPage);
            }
        }
        if(globalPage === 1) {
            prevButton.attr("disabled", true);
        }else{
            prevButton.attr("disabled", false);
        }
    });

    fromInput.on('paste keyup', function () {
        fromVal = $(this).val();
        if(fromVal != null && fromVal.length > 0 && toVal != null && toVal.length > 0){
            useFilters = true;
        }else{
            useFilters = false;
        }
    });
    toInput.on('paste keyup', function () {
        toVal = $(this).val();
        if(fromVal != null && fromVal.length > 0 && toVal != null && toVal.length > 0){
            useFilters = true;
        }else{
            useFilters = false;
        }
    });

    $('#colLFP').click(function() {
        sortTable(0, 'text');
    });
    $('#colLastVisit').click(function() {
        sortTable(1, 'text');
    });
    $('#colUpcomingVisit').click(function() {
        sortTable(2, 'text');
    });

    applyFilterButton.click(function() {
        if(searchParam != null && searchParam.length > 0){
            if(fromVal != null && fromVal.length > 0 && toVal != null && toVal.length > 0 && useFilters){
                loadData(globalPage, searchParam, fromVal, toVal);
            }
        }else{
            if(fromVal != null && fromVal.length > 0 && toVal != null && toVal.length > 0 && useFilters){
                loadData(globalPage, null, fromVal, toVal);
            }
        }
    });

    if(searchParam != null && searchParam.length > 0){
        if(fromVal != null && fromVal.length > 0 && toVal != null && toVal.length > 0 && useFilters){
            loadData(globalPage, searchParam, fromVal, toVal);
        }else{
            loadData(globalPage, searchParam);
        }
    }else{
        if(fromVal != null && fromVal.length > 0 && toVal != null && toVal.length > 0 && useFilters){
            loadData(globalPage, null, fromVal, toVal);
        }else{
            loadData(globalPage);
        }
    }
});