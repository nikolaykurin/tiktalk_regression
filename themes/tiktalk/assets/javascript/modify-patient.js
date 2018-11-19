function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
            var img = $("#frmAvatarPreview");
            img.attr("src", e.target.result);

            var label = $("#frmAvatarPreviewLabel");
            label.text("");
        };
        reader.readAsDataURL(input.files[0]);
    }
}

$(document).ready( function () {

    $("body").on("click", "#btnAddPatient, .btn-edit-patient", function() {
        $.request('onModifyPatient', {
            data: {
                patientId : $(this).attr("data-id")
            },
            update: {
            },
            complete: function() {
                $('.selectpicker').selectpicker({
                });

                $("#modifyPatientModal").modal();
            },
            error: function(data) {
                console.log(data);
            }
        });
    });

    $(document).on("hidden.bs.modal", "#modifyPatientModal", function () {
        this.remove();
    });

    $("body").on("change", "input[name=avatar]", function() {
        readURL(this);
    });
});
