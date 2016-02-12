(function( overview, $, undefined ) {

  overview.clearModal = function() {
    $('input[name=questionId]').removeAttr('value');
    $('#question-content').val('');

    $('input[name=answer1]').removeAttr('value');
    $('input[name=answer2]').removeAttr('value');
    $('input[name=answer3]').removeAttr('value');
    $('input[name=answer4]').removeAttr('value');

    $('input[type=radio][value=1]').parent().removeClass('active');
    $('input[type=radio][value=2]').parent().removeClass('active');
    $('input[type=radio][value=3]').parent().removeClass('active');
    $('input[type=radio][value=4]').parent().removeClass('active');
  };

  overview.updateTable = function(jsonString) {
    var data = JSON.parse(jsonString);

    $('#question-table').bootstrapTable({
      data: data
    }).on('click-row.bs.table', function (e, row, $element) {
      overview.clearModal();

      $('input[name=questionId]').attr('value', row.id);
      $('#question-content').val(row.question);

      $('input[name=answer1]').attr('value', row.answers[0]);
      $('input[name=answer2]').attr('value', row.answers[1]);
      $('input[name=answer3]').attr('value', row.answers[2]);
      $('input[name=answer4]').attr('value', row.answers[3]);

      var correctAnswer = row.correctAnswer+1;

      $('input[type=radio][value=' + correctAnswer +"]").parent().addClass('active');
      $('input[type=radio][value=' + correctAnswer +"]").attr('checked', '');
    });
  };

  overview.showModal = function() {
    $('#question-modal').modal('show');
  };
}( window.overview = window.overview || {}, jQuery ));
