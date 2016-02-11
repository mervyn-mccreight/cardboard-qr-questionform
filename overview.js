(function( overview, $, undefined ) {
    overview.updateTable = function(jsonString) {
        var data = JSON.parse(jsonString);

        $('#question-table').bootstrapTable({
          data: data
        });
    };
}( window.overview = window.overview || {}, jQuery ));
