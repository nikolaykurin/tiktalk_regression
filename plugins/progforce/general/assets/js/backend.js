function onShowWordModal(self) {
    var dlg = $('#wordModal');
    dlg.find('#wordTitle').text($(self).data("word"));
    var path = $(self).data("img");
    if (path) {
        dlg.find("#wordImg").show();
        dlg.find("#wordImgEmpty").hide();
        dlg.find('#wordImg').attr("src", path);
    } else {
        dlg.find("#wordImg").hide();
        dlg.find("#wordImgEmpty").show();
    }
    dlg.find('#wordAudio').attr("src", $(self).data("audio"));
    dlg.modal('show');
}

function onShowPlanPhaseModal(self) {
    var dlg = $("#planPhaseModal");
    var phaseId = $(self).data("phase-id");
    var statusId = $(self).data("status-id");
    var planPhaseId = $(self).data("plan-phase-id");
    dlg.attr("data-plan-phase-id", planPhaseId);
    dlg.find("#frmPhase").val(phaseId);
    dlg.find("#frmStatus").val(statusId);

    dlg.modal("show");
}

$(document).on('click', '#btnOKPhaseModal', function() {
    var dlg = $("#planPhaseModal");
    var planPhaseId = dlg.attr("data-plan-phase-id");
    var data = {
        "plan_phase_id": planPhaseId,
        "phase_id": dlg.find("#frmPhase").val(),
        "phase_status_id": dlg.find("#frmStatus").val()
    };
    if (planPhaseId == 0) {
        var table = $("#tablePhases tbody");
        var rows = table.find("tr");
        var rowNum =rows.length === 0 ? 0 : table.find('tr:last').attr('data-row-num');
        data.row_num = Number(rowNum)+1;
    }
    $.request("onPlanPhaseModify", {
        data: data,
        complete: function(data) {
            location.reload();
        },
        update: { 
            //relation_phases: '#tablePhases' 
        },
        error: function() {
            console.log('Error!');
        }
    });
});