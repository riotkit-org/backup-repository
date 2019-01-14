
class BaseUpload
{
    validationFailure (byField, overallStatus) {
        var statusByField = '';

        for (var field in byField) {
            statusByField += field + ": \n";

            for (var messageNum in byField[field]) {
                statusByField += ' - ' + byField[field][messageNum] + "\n"
            }

            statusByField += "\n\n";
        }

        window.alert(overallStatus + "\n\n" + statusByField);
    }

    showFileUrl (url) {
        $('#linkConfirmationModal').modal();
        $('#resultUrl').val(url);
    }
}
