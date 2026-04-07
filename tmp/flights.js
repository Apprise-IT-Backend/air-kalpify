ï»¿var isBannerFirstLoad = false;
var clicked = false;
var hasUmrahParam = /[?&]umrah=(1|true)/i.test(location.search);
var _routeInitDone = false;
$(document).ready(function () {
    showclient_data();
    //$("#tripList").on("click", "li", function () { selectTrip(this); });
    $("input[name='tripType']").on("change", function () {
        let selectedTrip = $(this).val();
        selectTrip(selectedTrip);
    });
    $("#classList").on("click", "li", function () { selectClass(this); });

    $("#hotelRoomCount").on("click", "li", function () { selectHotelRoom(this); });

    $("#jfrom.form-control").mouseover(function () {
        $(".arrow-btn").css("box-shadow", "1px 0px 0px #ccc, -1px 0 0 #aaa");
    });
    $("#jfrom.form-control").mouseout(function () {
        $(".arrow-btn").css("box-shadow", "1px 0px 0px #ccc, -1px 0 0 #ccc");
    });
    $("#jdest.form-control").mouseover(function () {
        $(".arrow-btn").css("box-shadow", "1px 0px 0px #aaa, -1px 0 0 #ccc");
    });
    $("#jdest.form-control").mouseout(function () {
        $(".arrow-btn").css("box-shadow", "1px 0px 0px #ccc, -1px 0 0 #ccc");
    });
    $("#modAirport").on("shown.bs.modal", function () { google.maps.event.trigger(mapap, "resize"); });

    resetAll();
    setDatePickers();
    //getrtFrom(3, 0);
    if (!hasUmrahParam) {
        getrtFrom(3, 0);
        _routeInitDone = true;
    }



    $('.popup-visa-image').magnificPopup({ type: 'image' });

    //Chat Code
    if ($(window).width() <= 800) {
        var tx = '<button onclick="$(\'#frmChat\').hide();" class="btn btn-danger btn-sm" style="z-index: 999; float: right; position: absolute; right: 0px; border-radius: 1px; padding: 2px 10px;">X</button><iframe  allow="camera; microphone" src = "https://www.amybd.com/laser/chat.html?cmd=' + FLTkn() + '&cc=' + chtkn() + '" style="width:100%" height="550px"></iframe>'
        $('#frmChat').css('width', '97%');
    }
    else {
        var tx = '<button onclick="$(\'#frmChat\').hide();" class="btn btn-danger btn-sm" style="z-index: 999; float: right; position: absolute; right: 0px; border-radius: 1px; padding: 2px 10px;">X</button><iframe allow="camera; microphone" src = "https://www.amybd.com/laser/chat.html?cmd=' + FLTkn() + '&cc=' + chtkn() + '" height="550px" width="590px"></iframe>'
    }
    $('#frmChat').html(tx);
    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const page = urlParams.get('page')
    $(document).click(function (event) {
        var clickover = $(event.target);
        var _opened = false;
        if (parseInt($('#mySidenav').css('width')) > 0) {
            _opened = true;
        }
        if (_opened === true && !clickover.hasClass("sidenav") && !clickover.hasClass("ui-accordion-header") && !clickover.hasClass("ui-accordion-header-icon")) {
            document.getElementById("mySidenav").style.width = "0";
        }
    });

    //var whatsearched = getLS('whichpage', '');
    //if (!isEmpty(whatsearched)) {
    //    if (whatsearched == 'Flights' || whatsearched == 'UmrahTicket' || whatsearched == 'UmrahPackage') { checkSavedFlightSearch(); }
    //}
    changeAnotherFinalDiv(page);
    getTotalDays();
    chonload();
    getRefreshedFlightSearch();
    //loadFlightFromUrl();
    setTimeout(function () {
        loadFlightFromUrl();
    }, 0);
    $('#btnSearchFlight').on('click', function () { GoToFlightSearch(); });
});
function GoToFlightSearch() {
    // Normalize trip type (UI uses "One Way"/"Round Trip"/"Multi City")
    var tripTypeRaw = $('input[name="tripType"]:checked').val() || 'One Way';
    var t = tripTypeRaw.toString().toLowerCase();
    var tripType = 'OW';
    if (t.indexOf('one') >= 0 || t.indexOf('ow') >= 0) tripType = 'OW';
    else if (t.indexOf('round') >= 0 || t.indexOf('rt') >= 0) tripType = 'RT';
    else if (t.indexOf('multi') >= 0 || t.indexOf('mc') >= 0) tripType = 'MC';

    // Passenger counts
    var ad = parseInt($('#qtA').val(), 10) || 1;
    var ch = parseInt($('#qtC').val(), 10) || 0;
    var inf = parseInt($('#qtI').val(), 10) || 0;

    // Class
    var cls = $('#valClass').text().trim() || 'Economy';

    // Build base URL
    var url = '/flights.html?trip=' + encodeURIComponent(tripType);
    var isUmrah = ($('#chkUmrah').is(":checked")) ? 1 : 0;
    var isCombo = ($('#chkCombo').is(":checked")) ? 1 : 0;
    url += '&umrah=' + encodeURIComponent(isUmrah) + '&combo=' + encodeURIComponent(isCombo);

    if (tripType === 'OW') {
        var from = ($('#jfrom').val() || '').trim();
        var to = ($('#jdest').val() || '').trim();
        var dep = ($('#journey-date').val() || '').trim();
        if (isEmpty(from) || isEmpty(to) || isEmpty(dep)) {
            ShowError('Please enter all required flight search data.');
            return;
        }
        url += '&from=' + encodeURIComponent(from)
            + '&to=' + encodeURIComponent(to)
            + '&dep=' + encodeURIComponent(dep);

    } else if (tripType === 'RT') {
        var fromR = ($('#jfrom').val() || '').trim();
        var toR = ($('#jdest').val() || '').trim();
        var depR = ($('#journey-date').val() || '').trim();
        var retR = ($('#return-date').val() || '').trim();
        if (isEmpty(fromR) || isEmpty(toR) || isEmpty(depR) || isEmpty(retR)) {
            ShowError('Please enter all required flight search data.');
            return;
        }
        url += '&from=' + encodeURIComponent(fromR)
            + '&to=' + encodeURIComponent(toR)
            + '&dep=' + encodeURIComponent(depR)
            + '&ret=' + encodeURIComponent(retR);

    } else if (tripType === 'MC') {
        var segmentCount = 0;
        var lastFrom = '';
        var lastTo = '';
        var lastDep = '';
        var nodes = $('[id^="mcrut"]').toArray();
        if (nodes.length > 0) {
            nodes = nodes.map(function (el) {
                var id = el.id || '';
                var idx = parseInt(id.replace(/^mcrut/i, ''), 10);
                if (isNaN(idx)) idx = 999999;
                return { el: el, idx: idx };
            }).sort(function (a, b) { return a.idx - b.idx; });

            nodes.forEach(function (item) {
                var $el = $(item.el);
                var fromAttr = ($el.attr('data-frt') || '').trim();
                var toAttr = ($el.attr('data-drt') || '').trim();
                var dateAttr = ($el.attr('data-mdt') || '').trim();
                if (!fromAttr || !toAttr) {
                    var txt = $el.text().split('\n')[0] || '';
                    var parts = txt.split('-').map(function (p) { return p.trim(); });
                    if (parts.length >= 2) {
                        if (!fromAttr) fromAttr = parts[0];
                        if (!toAttr) toAttr = parts[1];
                    }
                }
                if (fromAttr && toAttr && dateAttr) {
                    segmentCount++;
                    var n = segmentCount;
                    url += '&from' + n + '=' + encodeURIComponent(fromAttr)
                        + '&to' + n + '=' + encodeURIComponent(toAttr)
                        + '&dep' + n + '=' + encodeURIComponent(dateAttr);
                    lastFrom = ($('#jfrom').val() || '').trim();
                    lastTo = ($('#jdest').val() || '').trim();
                    lastDep = ($('#journey-date').val() || '').trim();
                }
            });
        }
        if (segmentCount === 0 && window.flightSearchState && Array.isArray(window.flightSearchState.multiCitySegments)) {
            window.flightSearchState.multiCitySegments.forEach(function (seg) {
                var fromVal = (seg.from || '').trim();
                var toVal = (seg.to || '').trim();
                var depVal = (seg.date || '').trim();
                if (fromVal && toVal && depVal) {
                    segmentCount++;
                    var n2 = segmentCount;
                    url += '&from' + n2 + '=' + encodeURIComponent(fromVal)
                        + '&to' + n2 + '=' + encodeURIComponent(toVal)
                        + '&dep' + n2 + '=' + encodeURIComponent(depVal);
                    lastFrom = ($('#jfrom').val() || '').trim();
                    lastTo = ($('#jdest').val() || '').trim();
                    lastDep = ($('#journey-date').val() || '').trim();
                }
            });
        }
        if (segmentCount === 0) {
            $('.multi-city-row').each(function () {
                var fromVal2 = $(this).find('.multi-city-from').text().trim();
                var toVal2 = $(this).find('.multi-city-to').text().trim();
                var depVal2 = $(this).find('.multi-city-date').text().trim();
                if (fromVal2 && toVal2 && depVal2) {
                    segmentCount++;
                    var n3 = segmentCount;
                    url += '&from' + n3 + '=' + encodeURIComponent(fromVal2)
                        + '&to' + n3 + '=' + encodeURIComponent(toVal2)
                        + '&dep' + n3 + '=' + encodeURIComponent(depVal2);
                    lastFrom = ($('#jfrom').val() || '').trim();
                    lastTo = ($('#jdest').val() || '').trim();
                    lastDep = ($('#journey-date').val() || '').trim();
                }
            });
        }
        if (segmentCount === 0) {
            ShowError('Please enter at least one multi-city segment.');
            return;
        }
        if (lastFrom && lastTo && lastDep) {
            url += '&from=' + encodeURIComponent(lastFrom)
                + '&to=' + encodeURIComponent(lastTo)
                + '&dep=' + encodeURIComponent(lastDep);
        }
    }

    url += '&ad=' + encodeURIComponent(ad)
        + '&ch=' + encodeURIComponent(ch)
        + '&inf=' + encodeURIComponent(inf)
        + '&cls=' + encodeURIComponent(cls);

    // --- NEW URL UPDATE LOGIC ---
    var onFlightsPage = window.location.pathname.toLowerCase().indexOf('flights.html') >= 0;

    if (onFlightsPage) {
        try {
            // choose pushState if you want each search to be a separate back-button step
            if (window.history && history.replaceState) {
                history.replaceState(null, '', url); // or history.pushState(null,'',url);
            } else {
                // Fallback causes reload
                location.href = url;
                return;
            }
        } catch (e) {
            // As a last resort navigate (will reload)
            location.href = url;
            return;
        }
        // Run search logic using current in-memory form values
        FSearchChoose();
        // If you specifically want to re-read from URL each time, uncomment:
        // loadFlightFromUrl();
    } else {
        // Not on flights page yet: navigate to it
        location.href = url;
    }
}
$(function () {

    $('#cmbAir').on('change', function (e) { fillsearch(); });
    $('input:radio[name=group1]').change(function () { fillsearch(); });
    $('#chkd:checkbox').change(function () { fillsearch(); });
    $('#cmbStops').change(function () { fillsearch(); });
    $('input:radio[name=btnOnwSegments]').change(function () { fltdaysegmentchanged('onw'); });
    $('input:radio[name=btnRetSegments]').change(function () { fltdaysegmentchanged('ret'); });
    $('input:radio[name=btnOnwLayover]').change(function () { fltlaytimechanged('onw'); });
    $('input:radio[name=btnRetLayover]').change(function () { fltlaytimechanged('ret'); });

    $('#umrahFullCheck:checkbox').change(function () { updateUmrahFare(); });
    $('#chkUmrah:checkbox').change(function () {
        if ($('#chkUmrah').is(":checked")) {
            $('#hdUM').val(1);
            getrtFrom(3, 0);
        }
        else {
            $('#hdUM').val(0);
            getrtFrom(3, 0);
        }
    });
    $('#cmbhttypF').on('change', function (e) { fillHotelResults2(); });
    $('#cmbAreaF').on('change', function (e) { fillHotelResults2(true); });
    $('#cmbhtSortPriceF').on('change', function (e) { fillHotelResults2(); });
    $('#cmbPref').on('change', function (e) { fillHotelResults2(); });
    var lgd = retlogin();
    if (!isEmpty(lgd.usr)) {
        getPendingHotelBooking(lgd.usr, 'Payment', '', '');
    }
    var isLogged = isUserLogged();
    //If logged in
    if (!isEmpty(isLogged) && isLogged == true) {
        $('#btnVisaShareAll').show();
        $('#divShareBasic').show();
        $('#divShareDoc').show();
        $('#divShareCovid').show();
    }
    else {
        $('#btnVisaShareAll').hide();
        $('#divShareBasic').hide();
        $('#divShareDoc').hide();
        $('#divShareCovid').hide();
    }
    var options = [];
    $('.dropdown-menu a').on('click', function (event) {
        setLS('StarF', '');
        var $target = $(event.currentTarget),
            val = $target.attr('data-value'),
            $inp = $target.find('input'),
            idx;

        if ((idx = options.indexOf(val)) > -1) {
            options.splice(idx, 1);
            setTimeout(function () { $inp.prop('checked', false) }, 0);
        } else {
            options.push(val);
            setTimeout(function () { $inp.prop('checked', true) }, 0);
        }
        $(event.target).blur();
        setLS('StarF', options);
        fillHotelResults2();
        return false;
    });

    var x = window.matchMedia("(max-width: 1000px)")
    mediaf(x) // Call listener function at run time
    x.addListener(mediaf)
    //Journey From Modal Pop Position
    $('#jfrom').click(function () {
        $('#collapseExample').collapse('hide');
        setTimeout(function () { $('#cityfindF').val('').trigger('change').focus(); }, 0);
        $(".cityTop").removeClass("cityTop");
        $("#cityF").addClass("cityTop");

    });

    $('#country').click(function () { setTimeout(function () { $('#countryFind').focus(); }, 0); });
    $('#category').click(function () { setTimeout(function () { $('#categoryFind').focus(); }, 0); });
    $('.dropdown input[type=text]').click(function (e) { e.stopPropagation(); });

    $('#jdest').click(function () {
        $('#collapseExample').collapse('hide');
        setTimeout(function () { $('#cityfindT').val('').trigger('change').focus(); }, 0);
        $(".cityTop").removeClass("cityTop");
        $("#cityT").addClass("cityTop");
    });
    $('.dropdown input[type=text]').click(function (e) { e.stopPropagation(); });

    //$('#hdest').click(function () {
    //    setTimeout(function () { $('#cityfindH').focus(); }, 0);
    //    $(".cityTop").removeClass("cityTop");
    //    $("#cityH").addClass("cityTop");
    //});
    $('.dropdown input[type=text]').click(function (e) { e.stopPropagation(); });

    $('#country').click(function () {
        setTimeout(function () { $('#countrylistF').focus(); }, 0);
        $(".cityTop").removeClass("cityTop");
        $("#countryH").addClass("cityTop");
    });


    var inpt = document.getElementById("lpswrd");
    if (!isEmpty(inpt)) {
        inpt.addEventListener("keyup", function (event) {
            if (event.getModifierState("CapsLock")) {
                $("#caps").show();
            } else {
                $("#caps").hide();
            }
        });
    }
    $(document).mouseup(function (e) {
        var container = $(".pophide");
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.hide();
        }
    });
    $('#chkRem:checkbox').change(function () { passman(); });
    getTotalDays();
    //setInterval(function () { chkSsn(1); }, 1200000);
    if (typeof isUserLogged === 'function' && isUserLogged()) {
        setInterval(function () { chkSsn(1); }, 1200000); // 20 min
    }
    setLS('grid', $('#grid1').html());
    setLS('gridUmrah', $('#gridUmrah').html());
    setLS('gridtest', $('#gridTest').html());
    setLS('hotelgrid', $('#hotelgrid2').html());
    getHBanner();
    loadSearchHistory();

});

$('body').on('show.bs.dropdown', function (event) { owlBannerClass(); });

$('body').on('hide.bs.dropdown', function (event) { owlBannerClass(); });

$(document).on('change', 'input[name="btnFType"]', fillsearch);

$(document).on('change', 'input[name="fltChkProviders"]', fillsearch);

$(document).on('change', 'input[name="fltChkLayAirports"]', fillsearch);
$(document).on('change.syncDay', '#journey-date,#return-date', function () {
    syncDayLabels();
});
$('#passengerBlock').on('click', function (event) {
    var events = $._data(document, 'events') || {};
    events = events.click || [];
    if ($(event.target)[0].id == "btnDone") {
        event.preventDefault();
        $("#dropdownMenuButtonpassenger").trigger("click");
    }
    for (var i = 0; i < events.length; i++) {
        if (events[i].selector) {
            if ($(event.target).is(events[i].selector)) {
                events[i].handler.call(event.target, event);
            }
            $(event.target).parents(events[i].selector).each(function () {
                events[i].handler.call(this, event);
            });
        }
    }
    event.stopPropagation();
});





function showpopupGiftVoucherTerms() { $('#withGiftVoucherRules').modal('show'); }

function sideNavClicked(ctrl) {
    if (ctrl == 'Offer') {
        location.href = '/promotions.html?v=987654321'
        return;
    }
    if (ctrl == 'Tours') {
        location.href = '/promotions.html?v=987654321&cat=Holiday'
        return;
    }
    //setLS('whichpage', ctrl);
    //loadSearhDiv();
}

//function loadSearhDiv() {
//    var ctrl = getLS('whichpage', 'Flights');
//    var surl = `?page=` + ctrl;
//    rewriteUrlString(surl);
//    changeAnotherFinalDiv(ctrl);
//}

function changeAnotherFinalDiv(ctrl) {
    $("#divSPBanner").hide();

    $("#vsc").html('');
    $("#vsc").hide();

    if (!isEmpty(ctrl)) {
        //reset
        setLS('isnewapply', 0);
        //document.getElementById("imgVisaMenu").src = "./images/icon_visa.svg";
        ////document.getElementById("imgOfficeMenu").src = "./images/icon_office.png";
        $("#divFlights").hide();
        //$("#dResult").hide();
    }

    //document.getElementById("header-wrap").style.backgroundImage = "url('images/bg_flight.svg')";
    setLS('ptype', '');
    $('.flightumrah').html('Flight');
    $('#hdUM').val(0);
    //getrtFrom(3, 0);
    if (!hasUmrahParam && !_routeInitDone) {          // only load if not already done and no umrah deep link
        getrtFrom(3, 0);
        _routeInitDone = true;
    }
    $("#divFlights").show();
    var ws = getLS('whatsearched', '');
    $("#search_status_hotel").html('<br/>');
    $('.btnFlights').addClass("active");
    $('.btnFlights span').css('color', '#1967d2');
    clearFlightSearchInputsNew();
    setDatePickers();

}

function showLoginModal() { clickToLogin(); }

function fillSearchFromHist(ddH) {
    if (ddH != "") {
        FindReq = getLS('FindReqH' + ddH, '');
        rsp = getLS('FindRespH' + ddH, '');

        setLS('FindReq', FindReq);
        setLS('FindResp', rsp);
    }
    filterAirline();
    fillsearch();
}

function owlBannerClass() {
    isBannerFirstLoad = !isBannerFirstLoad;
    if (isBannerFirstLoad) { $(".owl-banner").addClass("owl-zbanner"); }
    else { $(".owl-banner").removeClass("owl-zbanner"); }
}

function mediaf(x) {
    if (x.matches) { //Small Screen
        $("#pref-date").datepicker("option", { numberOfMonths: 1 });
        $("#journey-date").datepicker("option", { numberOfMonths: 1 });
        $("#return-date").datepicker("option", { numberOfMonths: 1 });

    } else {
        $("#pref-date").datepicker("option", { numberOfMonths: 2 });
        $("#journey-date").datepicker("option", { numberOfMonths: 2 });
        $("#return-date").datepicker("option", { numberOfMonths: 2 });
    }
}

function descDet(cntr) {
    var tx = $(cntr).parent().next('.dvdet').html();
    if (tx != '') {
        $(cntr).parent().next('.dvdet').slideDown(300);
    }
}



function psngrcount(par, vl, qt, ud) {
    var value = Number($('#qt' + qt).val());
    if (ud == '-') {
        value--;
        if (value == vl) {
            $(par).removeClass('bgactivenumberinput');
            $(par).addClass('bginactivenumberinput');
        }
        if (value < vl) {
            value = vl;
        }
    } else {
        value++;
        if (value == vl) {
            $(par).removeClass('bgactivenumberinput');
            $(par).addClass('bginactivenumberinput');
        }
        if (value > vl) {

            value = vl;
        }
    }
    $('#qt' + qt).val(value);
    $('#valPerson').html(Number($('#qtA').val()) + Number($('#qtC').val()) + Number($('#qtI').val()));
}

/*function searchNow() { $("#dResult").toggle(); }*/

function poppos(trgt, cntr) { }

function fillfrom(cntrl) {

    var tblid = $(cntrl).parent().attr('id');
    if (tblid == 'citylistF') {
        $('#jfrom').val($(cntrl).text());
        $('#jdest').val('').trigger("change");
        getrtTo(3);
    } else {
        $('#jdest').val($(cntrl).text());
        let rt1 = $('#jfrom').val();
        let rt2 = $('#jdest').val();
        let hdOr = $('#hdOR').val();
        if (hdOr == 'OW') {
            if (rt1.includes('BANGLADESH') && rt2.includes('BANGLADESH') && !rt1.includes('DAC') && !rt2.includes('DAC')) {
                $('#dvComboSearch').show();
            } else {
                $('#dvComboSearch').hide();
            }
        }

    }
}





function popPassenger() {
    this.clicked = !this.clicked;
    if (this.clicked) {
        $("#passengerBlock").css({
            display: 'block',
            position: 'absolute',
            right: '0'
        });
    } else {
        $("#passengerBlock").css({
            display: 'none',
            position: 'relative',
            right: '0'
        });
    }
}

function resetAll() {

    //reset Trip
    //$('#tripList li').removeClass("dropdown-item-selected");
    //$('#tripList li:first').addClass("dropdown-item-selected");
    $("#valWay").html('<i class="fas fa-long-arrow-alt-right px-2"></i>One Way');
    $('#retDate').hide();
    $('#dvComboSearch').hide();
    //reset Class
    $('#classList li').removeClass("dropdown-item-selected");
    $('#classList li:first').addClass("dropdown-item-selected");
    $("#valClass").html('Economy');
    //reset Passenger
    $('#valPerson').html(1);
    $('#qtA').val(1);
    $('#qtC').val(0);
    $('#qtI').val(0);
}

function setDatePickers() {
    if (typeof $.datepicker !== 'undefined') {

        $.datepicker.setDefaults({
            appendTo: 'body',
            showAnim: '',
            beforeShow: function (input, inst) {
                var $dp = $(inst.dpDiv);
                $dp.css({ opacity: 0, display: 'block' });
                requestAnimationFrame(function () {
                    try {
                        $dp.css('z-index', 200000);
                        $dp.position({
                            my: "right top",
                            at: "right bottom",
                            of: $(input),
                            collision: "fit"
                        });
                    } finally {
                        $dp.css({ opacity: 1 });
                    }
                });
            },
            onChangeMonthYear: function (year, month, inst) {
                var $dp = $(inst.dpDiv);
                $dp.css({ opacity: 0 });
                requestAnimationFrame(function () {
                    try {
                        $dp.position({
                            my: "right top",
                            at: "right bottom",
                            of: $(inst.input),
                            collision: "fit"
                        });
                    } finally {
                        $dp.css({ opacity: 1 });
                    }
                });
            }
        });
    }

    $('#pref-date').val(moment(new Date()).format("DD-MMM-YYYY"));
    $('#journey-date').val(moment(new Date()).format("DD-MMM-YYYY"));
    $('#return-date').val(moment(new Date()).format("DD-MMM-YYYY"));
    $('#journey-day').html(moment(new Date()).format('dddd'));
    $('#return-day').html(moment(new Date()).format('dddd'));

    $("#journey-date").datepicker({
        numberOfMonths: 1,
        autoclose: true,
        dateFormat: "dd-M-yy",
        minDate: 0,
        onSelect: function () {
            var minDt = $('#journey-date').datepicker('getDate');
            var minDtE = $('#return-date').datepicker('getDate');
            $("#return-date").datepicker("change", { minDate: minDt });
            if ($('#hdOwRt').val() == 'RT') {
                var date2 = $('#journey-date').datepicker('getDate', '+1D');
                date2.setDate(date2.getDate() + 1);
                if (minDtE < minDt) {
                    $('#return-date').datepicker('setDate', date2)
                }
            }
            var minDtin = $('#journey-date').datepicker('getDate');
            $('#journey-day').html(moment(minDtin).format('dddd'));
            var minDtout = $('#return-date').datepicker('getDate');
            $('#return-day').html(moment(minDtout).format('dddd'));
            getTotalDays();
        }
    });

    $("#return-date").datepicker({
        numberOfMonths: 1,
        dateFormat: "dd-M-yy",
        minDate: $('#journey-date').datepicker('getDate'),
        onSelect: function () {
            var minDtout = $('#return-date').datepicker('getDate');
            $('#return-day').html(moment(minDtout).format('dddd'));
            getTotalDays();
        }
    });

    $("#pref-date").datepicker({
        numberOfMonths: 1,
        autoclose: true,
        dateFormat: "dd-M-yy",
        minDate: 0,
        maxDate: "+12M"
    });

    var tomorrow = new Date();
    $('#checkin-date').val(moment(tomorrow).format("DD-MMM-YYYY")).trigger('change');

    var tomorrow2 = new Date();
    tomorrow2.setDate(tomorrow2.getDate() + 2);
    $('#checkout-date').val(moment(tomorrow2).format("DD-MMM-YYYY")).trigger('change');

    var minDtin = $('#checkin-date').datepicker('getDate');
    $('#checkin-day').html(moment(minDtin).format('dddd'));
    var minDtout = $('#checkout-date').datepicker('getDate');
    $('#checkout-day').html(moment(minDtout).format('dddd'));
    getTotalDays();

    $("#checkin-date").datepicker({
        numberOfMonths: 1,
        autoclose: true,
        dateFormat: "dd-M-yy",
        minDate: 0,
        maxDate: "+6M",
        onSelect: function () {
            var minDt = $('#checkin-date').datepicker('getDate');
            minDt.setDate(minDt.getDate() + 1);

            var minDtE = $('#checkout-date').datepicker('getDate');
            $("#checkout-date").datepicker("change", { minDate: minDt });

            var date2 = $('#checkin-date').datepicker('getDate', '+1D');
            //var spage = getLS('whichpage', '');
            //if (!isEmpty(spage)) {
            //    if (spage == 'Office') date2.setDate(date2.getDate());
            //    else date2.setDate(date2.getDate() + 1);
            //} else {
            //    date2.setDate(date2.getDate() + 1);
            //}
            $("#checkout-date").datepicker("change", { minDate: date2 });

            if (minDtE < minDt) $('#checkout-date').datepicker('setDate', date2);
            else $('#checkout-date').datepicker('setDate', date2);

            var minDtin = $('#checkin-date').datepicker('getDate');
            $('#checkin-day').html(moment(minDtin).format('dddd'));
            var minDtout = $('#checkout-date').datepicker('getDate');
            $('#checkout-day').html(moment(minDtout).format('dddd'));
            getTotalDays();
        }
    });

    //var spage = getLS('whichpage', '');
    //if (!isEmpty(spage) && spage == 'Office') {
    //    var t2 = new Date();
    //    t2.setDate(t2.getDate() + 1);
    //    $('#checkout-date').val(moment(t2).format("DD-MMM-YYYY")).trigger('change');
    //    var minDtout2 = $('#checkout-date').datepicker('getDate');
    //    $('#checkout-day').html(moment(minDtout2).format('dddd'));
    //}

    $("#checkout-date").datepicker({
        numberOfMonths: 1,
        dateFormat: "dd-M-yy",
        minDate: $('#checkin-date').datepicker('getDate', '+1D'),
        maxDate: "+6M",
        onSelect: function () {
            var minDtout = $('#checkout-date').datepicker('getDate');
            $('#checkout-day').html(moment(minDtout).format('dddd'));
            getTotalDays();
        }
    });

    $(".childdate").datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: "dd-M-yy",
        maxDate: "-23M",
        minDate: "-11Y",
        showOtherMonths: true
    });

    $('#ui-datepicker-div').click(function (event) { event.stopPropagation(); });
    $('.childdate').val(moment(moment().subtract(9, 'years')).format("DD-MMM-YYYY"));
}


function openNav() {
    document.getElementById("mySidenav").style.width = "300px";
}

function closeNav() {
    document.getElementById("mySidenav").style.width = "0";
}

function loginrequest(cntr) {
    $("#loginrequestdiv").show();
    $('.rselected').removeClass('rselected');
    $(cntr).addClass("rselected");
}

function closeloginrequestdiv() {
    $("#loginrequestdiv").hide();
}





function setbig(url, rm, sl) {
    $('#bigroomimg' + rm).attr('src', url);
}


function syncDayLabels() {
    var depVal = $('#journey-date').val();
    if (depVal) {
        var d1 = moment(depVal, ["DD-MMM-YYYY", "DD-MMM-YY", "DD-M-YYYY", "DD-M-YY"], true);
        if (d1.isValid()) {
            $('#journey-day').html(d1.format('dddd'));
        }
    }
    var retVal = $('#return-date').val();
    if (retVal) {
        var d2 = moment(retVal, ["DD-MMM-YYYY", "DD-MMM-YY", "DD-M-YYYY", "DD-M-YY"], true);
        if (d2.isValid()) {
            $('#return-day').html(d2.format('dddd'));
        }
    }
}
// Insert this function somewhere after your other helper functions (e.g. near HSearchDetailsPage)
// and call loadFlightFromUrl() from inside $(document).ready() after getRefreshedFlightSearch();
function loadFlightFromUrl() {
    try {
        var params = new URLSearchParams(window.location.search);
        if (!params || params.toString() === '') return;

        var trip = (params.get('trip') || '').toUpperCase();
        if (isEmpty(trip)) {
            return;
        }
        // map trip codes to UI values
        if (trip === 'OW') {
            $('input[name="tripType"][value="One Way"]').prop('checked', true);
            $('#hdOR').val('OW');
            if (typeof selectTrip === 'function') selectTrip('One Way');
        } else if (trip === 'RT') {
            $('input[name="tripType"][value="Round Trip"]').prop('checked', true);
            $('#hdOR').val('RT');
            if (typeof selectTrip === 'function') selectTrip('Round Trip');
        } else if (trip === 'MC') {
            $('input[name="tripType"][value="Multi City"]').prop('checked', true);
            $('#hdOR').val('MC');
            if (typeof selectTrip === 'function') selectTrip('Multi City');
        }

        // passengers & class
        var ad = params.get('ad');
        var ch = params.get('ch');
        var inf = params.get('inf');
        var cls = params.get('cls');

        if (ad !== null) { $('#qtA').val(ad); }
        if (ch !== null) { $('#qtC').val(ch); }
        if (inf !== null) { $('#qtI').val(inf); }
        $('#valPerson').html(Number($('#qtA').val()) + Number($('#qtC').val()) + Number($('#qtI').val()));

        if (cls !== null) {
            $('#valClass').html(decodeURIComponent(cls));
        }
        // get umrah and combo checkboxes if present

        // One-way / Return
        if (trip === 'OW' || trip === 'RT') {
            var from = params.get('from') ? decodeURIComponent(params.get('from')) : '';
            var to = params.get('to') ? decodeURIComponent(params.get('to')) : '';
            var dep = params.get('dep') ? decodeURIComponent(params.get('dep')) : '';
            var ret = params.get('ret') ? decodeURIComponent(params.get('ret')) : '';

            if (from) { $('#jfrom').val(from).trigger('change'); }
            if (to) { $('#jdest').val(to).trigger('change'); }
            if (dep) { $('#journey-date').val(dep); }
            if (trip === 'RT' && ret) {
                $('#return-date').val(ret);
                $('#retDate').show();
            }
            syncDayLabels();
        }

        // Multi-city: expect params like from1/to1/dep1, from2/to2/dep2 ...
        if (trip === 'MC') {
            // clear existing mc container
            $('#dvmcrut').html('');
            var segmentCount = 0;
            for (var i = 1; i <= 6; i++) { // allow up to 6 segments; adjust if needed
                var fKey = 'from' + i;
                var tKey = 'to' + i;
                var dKey = 'dep' + i;
                if (params.has(fKey) && params.has(tKey) && params.has(dKey)) {
                    var fVal = decodeURIComponent(params.get(fKey) || '').trim();
                    var tVal = decodeURIComponent(params.get(tKey) || '').trim();
                    var dVal = decodeURIComponent(params.get(dKey) || '').trim();
                    if (fVal && tVal && dVal) {
                        segmentCount++;
                        // create element id mcrut{n} to match index.html conventions
                        var n = segmentCount;
                        var dispFrom = fVal;
                        var dispTo = tVal;
                        var dispDate = dVal;
                        // Build the div similar to index's structure; keep data attributes for later use
                        var div = document.createElement('div');
                        div.id = 'mcrut' + n;
                        div.className = 'mcdiv m-2';
                        div.style.cssText = 'background-color: #5f5ff9; position: relative; color: #fff; padding:8px; border-radius:4px;';
                        div.setAttribute('data-frt', fVal);
                        div.setAttribute('data-drt', tVal);
                        div.setAttribute('data-mdt', dVal);
                        // close button calls removeItem if available (index uses zero-based)
                        var closeBtn = document.createElement('button');
                        closeBtn.className = 'close-button';
                        closeBtn.style.cssText = 'position:absolute; top:4px; right:6px; background:transparent; border: none; color:white; font-weight:bold;';
                        // attempt to call removeItem if defined; pass (n-1) to match earlier code expectations
                        closeBtn.setAttribute('onclick', 'if(typeof removeItem === \"function\"){ removeItem(' + (n - 1) + ');} else { this.parentNode.remove(); }');
                        closeBtn.textContent = 'X';
                        div.appendChild(closeBtn);
                        // text: show from - to and date on next line
                        var txt = document.createElement('div');
                        txt.innerHTML = '<span style="font-weight:700;">' + escapeHtml(dispFrom) + ' - ' + escapeHtml(dispTo) + '</span><br/><span style="font-size:0.95rem;">' + escapeHtml(dispDate) + '</span>';
                        div.appendChild(txt);
                        document.getElementById('dvmcrut').appendChild(div);
                    }
                }
            }
            // set hdMCCount so other code knows segments exist
            if (segmentCount > 0) {
                $('#hdMCCount').val(segmentCount);
                // set main inputs to first segment for UI coherence
                var from = params.get('from') ? decodeURIComponent(params.get('from')) : '';
                var to = params.get('to') ? decodeURIComponent(params.get('to')) : '';
                var dep = params.get('dep') ? decodeURIComponent(params.get('dep')) : '';
                if (from) { $('#jfrom').val(from).trigger('change'); }
                if (to) { $('#jdest').val(to).trigger('change'); }
                if (dep) { $('#journey-date').val(dep); }
                syncDayLabels();
            }
        }
        //var umrah = params.get('umrah');
        //if (umrah === '1' || umrah === 'true') {
        //    $('#chkUmrah').prop('checked', true);
        //    $('#hdUM').val(1);
        //    getrtFrom(3, 0);
        //} else {
        //    $('#chkUmrah').prop('checked', false);
        //    $('#hdUM').val(0);
        //    getrtFrom(3, 0);
        //}
        var umrah = params.get('umrah');
        var wantUmrah = (umrah === '1' || umrah === 'true');

        if (wantUmrah) {
            // Ensure checkbox reflects URL
            if (!$('#chkUmrah').is(':checked')) {
                // Trigger native change (which sets #hdUM and calls getrtFrom)
                $('#chkUmrah').prop('checked', true).trigger('change');
            } else {
                // Checkbox already checked but maybe initial route load skipped
                if ($('#hdUM').val() !== '1') {
                    $('#hdUM').val(1);
                }
                if (!_routeInitDone) {
                    getrtFrom(3, 0);
                    _routeInitDone = true;
                }
            }
        } else {
            if ($('#chkUmrah').is(':checked')) {
                $('#chkUmrah').prop('checked', false).trigger('change');
            } else if (!_routeInitDone) {
                // Non-umrah deep link (routes not yet loaded)
                getrtFrom(3, 0);
                _routeInitDone = true;
            }
        }

        var combo = params.get('combo');
        if (combo === '1' || combo === 'true') {
            $('#chkCombo').prop('checked', true);
        } else {
            $('#chkCombo').prop('checked', false);
        }
        // finally trigger the search (delay slightly so UI updates take effect)
        setTimeout(function () {
            if (typeof FSearchChoose === 'function') {
                // Ensure result panel is hidden/shown correctly before searching
                /* try { $('#dResult').hide(); } catch (e) { }*/
                FSearchChoose();
            }
        }, 300);

    } catch (err) {
        console.error('Error parsing flight URL params:', err);
    }

    // simple helper to avoid XSS when injecting text nodes
    function escapeHtml(s) {
        if (!s && s !== 0) return '';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
}
