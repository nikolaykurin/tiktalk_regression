 $(document).ready(function() {

    $("body").on("click", ".call-link", function(event) {
        event.preventDefault();
        event.stopPropagation();
        var data = $(event.currentTarget).data();
        var patientId = data.id;
        $.request("onGetChatTokens", {
            data: {
                patientId: patientId
            },
            success: function(data) {
                this.success(data);

                if (!data.jitsiToken) {
                    alert('Wrong Jitsi Token!');
                    document.location = "/clinician/patient/";
                    return;
                }

                console.log(data);
                var toSend = {
                    registration_ids: [data.device],
                    data: {
                        room_path: 'https://jitsi.tiktalk-av.com/'+patientId
                    }
                };
                $.ajax({
                    type: "POST",
                    url: 'https://fcm.googleapis.com/fcm/send',
                    data: JSON.stringify(toSend),
                    headers: {
                        'Authorization': 'key=' + data.pushToken,
                        'Content-Type': 'application/json'
                    },
                    success: function (response) {
                        console.log(1, response);
                        if (response.success) {
                            var newHref = 'https://jitsi.tiktalk-av.com/' + patientId + "?jwt="+data.jitsiToken;
                            window.open(newHref,'_blank');
                        } else {
                            alert('Error: '+response.results[0].error);
                        }
                    },
                    error: function (err) {
                        console.log('-------------------');
                        console.log(err);
                        alert('Error: '+err.responseText);
                    },
                    done: function () {
                        console.log('done');
                    },
                    dataType: 'json'
                });

            }
        });
    });

    $("body").on("click", "#tblPatients .tbl-body .tbl-td", function () {
        if (!$(this).hasClass("td-edit")) {
            document.location = "/clinician/patient/" + $(this).parent().data("id");
        }
    });
});
