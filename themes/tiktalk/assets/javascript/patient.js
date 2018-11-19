function activatePlansDragger() {
    var el = document.getElementById("tblPlansBody");
    var sortable = new Sortable(el, {
        handle: ".btn-drag",
        draggable: ".tbl-row", // Specifies which items inside the element should be sortable
        // Element dragging ended
        onEnd: function (evt) {
            // reindex table rows
            var data = [];
            $("#tblPlansBody .tbl-row").each(function(index) {
                $(this).find(".td-sequence .seq").text(index+1);
                data[$(this).attr("data-id")] = {"protocol_sequence": index+1};
            });
            $.request("onUpdatePlansSequence", {
                data: data,
                success: function(data) {
                    this.success(data);
                }
            });
        }
    });
}

function activatePhasesDragger() {
    var el = document.getElementById("tblPhasesBody");
    var sortable = new Sortable(el, {
        handle: ".btn-drag",
        draggable: ".tbl-row", // Specifies which items inside the element should be sortable
        onEnd: function (evt) {
            if (evt.newIndex == 0) {
                var firstRow = $("#tblPhasesBody").find(".row-not-draggable");
                if (firstRow) {
                    var itemEl = $(evt.item).detach();
                    itemEl.insertAfter(firstRow);
                }
            }
            // reindex table rows
            $("#tblPhasesBody .tbl-row").each(function(index) {
                $(this).attr("data-row-num", index+1);
            });
            updatePhases(null);
	}
    });
}

function selectPlansTab(tabId) {
    var tabs = $(".tab-plan");
    tabs.each(function() {
        if ($(this).attr("id") == tabId) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function updatePhases(rows) {
    var phases = [];
    if (rows == null) {
        rows = $("#tblPhases .tbl-row");
    }
    rows.each(function() {
        var phase = {};
        phase.id = $(this).attr("data-id");
        var vals = {};
        vals.row_num = $(this).attr("data-row-num");
        vals.phase_id = $(this).find(".select-phase :selected").val();
        vals.phase_status_id = $(this).find(".select-status :selected").val();
        vals.phase_status_date = $(this).find(".phase-status-date").text();
        phase.vals = vals;
        phases.push(phase);
    });

    var data = {};
    data.phases = phases;
    $.request('onUpdatePhases', {
        data: data,
        success: function(data) {
            this.success(data);
        }
    });
}

function showPlansTab() {
    $(".tab-slp-comment").hide();

    selectPlansTab("tabPatientPlansList");
    activatePlansDragger();
}

function setSelected(select) {
    var val = select.val();
    select.find("option").each(function() {
        if (val == $(this).val()) {
            $(this).attr("selected", "selected");
        } else {
            $(this).removeAttr('selected');
        }
    });
}

function setDateRange() {
    var start = moment();
    var end = moment();

    function cb(start, end) {
        $('#filterPeriod span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }

    $('#filterPeriod').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    cb(start, end);

};

$(document).ready( function () {
    $(".nav-item-patient").click(function() {

        $(".nav-item-patient").each(function() {
            var item = $(this).find("a");
            if (item.hasClass("active")) {
                item.removeClass("active");
                $("#tabPatient" + $(this).attr("data-id")).hide();
            }
        });

        var tabId = "#tabPatient" + $(this).attr("data-id");
        $(tabId).show();
        if (tabId == "#tabPatientInfo") {
            $("#panelPatient .patient-header").hide();
        } else {
            $("#panelPatient .patient-header").show();
        }

        if (tabId == "#tabPatientPlans") {
            selectPlansTab("tabPatientPlansList");
        }

        $(this).find("a").addClass("active");
    });

    activatePlansDragger();

    // on change plan
    $("body").on("change", "#planSounds", function() {
        $.request("onChangePlan", {
            data: {
                planId: $(this).val()
            },
            success: function(data) {
                this.success(data);
            }
        });
    });

    // on change plan status
    $("body").on("change", "#planStatuses", function() {
        var data = {};
        var select = $("#planStatuses");
        var planId = $("#tabPatientPlansPhases").attr("data-plan-id");
        data.planStatusId = select.find(":selected").val();
        data.planId = planId;
        $.request('onUpdatePlan', {
            data: data,
            success: function(data) {
                // sets new status for row in plans list
                var td = $("#tblPlansBody #plan" + planId + " .td-protocol");
                td.text(select.find(":selected").text());
            }
        });
    });

    // on change status
    $("body").on("change", ".select-phase", function() {
        setSelected($(this));
        var row = $(this).closest(".tbl-row");
        updatePhases(row);
    });

    // on change status
    $("body").on("change", ".select-status", function() {
        setSelected($(this));

        var statusDate;
        if ($(this).val() == 3) {
            statusDate = moment().format("YYYY-MM-D");
        } else {
            statusDate = "";
        }
        var row = $(this).closest(".tbl-row");
        row.find(".phase-status-date").text(statusDate);
        updatePhases(row);
    });

    // add plan
    $("body").on("click", "#btnAddPlan", function() {
        $.request("onAddPlan", {
            success: function(data) {
                this.success(data);
                selectPlansTab("tabPatientPlansAdd");
            }
        });
    });

    // button edit plan click
    $("body").on("click", ".btn-edit-plan", function() {
        $.request("onEditPlan", {
            data: {
                planId: $(this).closest(".tbl-row").attr("data-id")
            },
            success: function(data) {
                this.success(data);
                selectPlansTab("tabPatientPlansPhases");
                activatePhasesDragger();
            }
        });
    });

    // button remove plan click
    $("body").on("click", ".btn-remove-plan", function() {
        var dlg = confirm("Are you sure?");
        if (dlg !== true) {
            return false;
        }

        $.request("onDeletePlan", {
            data: {
                planId: $(this).closest(".tbl-row").attr("data-id")
            },
            success: function(data) {
                this.success(data);
                activatePlansDragger();
            }
        });
    });

    // button insert plan click
    $("body").on("click", "#btnInsertPlan", function() {
        var soundId = $("#selectSounds :selected").val();
        if (soundId == 0) {
            alert("Sound is not selected!");
            return false;
        }
        $.request("onInsertPlan", {
            data: {
                patientId: $("#panelPatient").attr("data-patient-id"),
                planSoundId: soundId
            },
            success: function(data) {
                this.success(data);
                selectPlansTab("tabPatientPlansPhases");
            }
        });
    });

    // add phase
    $("body").on("click", "#btnAddPhase", function() {
        var table = $("#tblPhases");
        var row = table.find(".tbl-row");
        var phaseId = row.length === 0 ? 1 : 0;
        var rowNum = row.length === 0 ? 0 : table.find('.tbl-row:last').attr('data-row-num');
        rowNum = Number(rowNum)+1;

        // add container for partial rendering
        table.find("#tblPhasesBody").append('<div id="partialPhaseRow0"></div>');

        $.request('onAddPhase', {
            data: {
                rowNum: rowNum,
                planId: $("#planSounds :selected").val(),
                phaseId: phaseId,
            },
            success: function(data) {
                this.success(data);
                var element = $(table.find("#partialPhaseRow0"));
                var content = element.children().clone(true, true);
                element.remove();
                content.appendTo(table.find("#tblPhasesBody"));

                activatePhasesDragger();
            }
        });
    });

    // remove phase
    $("body").on("click", ".btn-remove-phase", function() {
        var dlg = confirm("Are you sure?");
        if (dlg !== true) {
            return false;
        }

        var row = $(this).closest(".tbl-row");
        $.request('onRemovePhase', {
            data: {id: row.attr("data-id")},
            success: function(data) {
                this.success(data);
                row.remove();;
            }
        });
    });

    $("body").on("click", "#btnSlpLogs", function() {
        $.request("onGetSlpLogs", {
            data: {
                planId: $("#planSounds :selected").val()
            },
            complete: function() {
                $("#slpLogsModal").modal();
            },
            error: function(data) {
                console.log(data);
            }
        });
    });

    // update phases
    $("body").on("click", "#btnUpdatePhases", function() {
        updatePhases();
    });

    // cancel plan
    $("body").on("click", "#btnBackPhases", function() {
        showPlansTab();
    });

    // cancel insert plan
    $("body").on("click", "#btnCancelInsertPlan", function() {
        showPlansTab();
    });

    $("body").on("click", "#btnUpdatePhases", function() {
        updatePhases();
    });

    $("body").on("click", ".btn-edit-slp-comment", function() {
        var pnl = $(this).closest(".pnl-slp-logs");
        pnl.find(".tab-slp-logs").hide();
        var tab = $(".tab-slp-comment");
        tab.show();
        tab.find(".edt-slp-comment").val("");
        tab.find(".btn-submit-slp-comment").attr("data-id", $(this).attr("data-id"));
    });

    $("body").on("click", ".btn-submit-slp-comment", function() {
        var pnl = $(this).closest(".pnl-slp-logs");
        var tabLogs = pnl.find(".tab-slp-logs");
        var tabComment = pnl.find(".tab-slp-comment");
        tabLogs.show();
        tabComment.hide();

        var edtComment = tabComment.find(".edt-slp-comment");
        var val = edtComment.val().trim();

        if (val) {
            if (val.length > 200) {
                val = val.substr(0, 200);
            }
            var id = $(this).attr("data-id");
            var tbl = tabLogs.find(".tbl .tbl-body");
            var log = tbl.find('[data-id="' + id + '"]').first();
            var td = log.find(".td-slp-comment");

            $.request("onSetSlpComment", {
                data: {
                    logId: id,
                    comment: val
                },
                complete: function() {
                    td.html(val);
                },
                error: function(data) {
                    console.log(data);
                }
            });
        }
    });

    function getReports() {
        var filterPeriod = [];
        var filter = $("#reportsFilter");
        var mode = filter.find("#filterMode").val();

        if (mode == 2) {
            var startDate = $('#filterPeriod').data('daterangepicker').startDate;
            var endDate = $('#filterPeriod').data('daterangepicker').endDate;
            filterPeriod = [
                startDate.format("YYYY-MM-DD HH:mm:ss"),
                endDate.format("YYYY-MM-DD HH:mm:ss")
            ];
        }

        $.request('onGetReports', {
            data: {
                pageNum: $("#reportsPagination .active").attr("data-page-num"),
                patientId: $("#patientId").attr("data-id"),
                navId: $("#navItemByDates").hasClass("active") ? 0 : 1,
                filterPeriod: filterPeriod
            },
            success: function(data) {
                this.success(data);
                $("#tabPatientReports").show();
            }
        });
    }

    $("body").on("click", "#navItemByDates", function() {
        $(this).addClass("active");
        $("#navItemByWords").removeClass("active");
        getReports();
    });

    $("body").on("click", "#navItemByWords", function() {
        $(this).addClass("active");
        $("#navItemByDates").removeClass("active");
        getReports();
    });

    $("body").on("click", ".page-item", function() {
        $("#reportsPagination .active").removeClass("active");
        $(this).addClass("active");
        getReports();
    });

    $("body").on("change", "#filterMode", function() {
        var mode = $(this).val();
        if (mode == 1) {
            $("#filterSectionPeriod").hide();
        } else {
            $("#filterSectionPeriod").show();
        }
        getReports();
    });

    $("body").on("change", "#filterMode, #filterPeriod", function() {
        getReports();
    });

    $("body").on("click", ".close-slp", function() {
        $(".slp-logs-static").hide();
    });

    setDateRange();
    $('#filterPeriod').on('apply.daterangepicker', function(ev, picker) {
        getReports();
    });

    $("body").on("change", "#selProgressSound", function() {
        var total = $(this).find(":selected").attr("data-total");
        var completed = $(this).find(":selected").attr("data-completed");

        var val = total == 0 ? 0 : Math.round(completed*100/total);
        var bar1 = $("#barProgress1");
        var bar2 = $("#barProgress2");
        bar1.text(val + "%");
        bar1.attr("style", "width: " + val + "%");
        bar1.attr("aria-valuenow", val);

        bar2.attr("style", "width: " + (100-val) + "%");
        bar2.attr("aria-valuenow", 100-val);
    });
});
