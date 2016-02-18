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

    $('#qr-preview').addClass('hidden');
    $('#delete-button').addClass('hidden');
    $('#print-button').addClass('hidden');
  };

  overview.print = function() {
    var prtContent = document.getElementById("print-page");
    var WinPrint = window.open('', '', 'left=0,top=0,toolbar=0,scrollbars=0,status=0');
    WinPrint.document.write(prtContent.innerHTML);
    WinPrint.document.close();
    WinPrint.focus();
    WinPrint.print();
    WinPrint.close();
  };

  overview.sendDeleteRequest = function() {
    var id = $('input[name=questionId]').val();

    $.ajax({
      url: 'api.php/questions/' + id,
      type: 'DELETE',
      success: function(result) {
          console.log(result);
          location.reload(true);
      }
    });
  };

  overview.updateTable = function(jsonString) {
    var tableData = JSON.parse(jsonString);

    $('#question-table').bootstrapTable({
      data: tableData.questions
    }).on('click-row.bs.table', function (e, row, $element) {
      overview.clearModal();

      $('#qr-preview').removeClass('hidden');

      $('input[name=questionId]').attr('value', row.id);
      $('#question-content').val(row.question);

      $('input[name=answer1]').attr('value', row.answers[0]);
      $('input[name=answer2]').attr('value', row.answers[1]);
      $('input[name=answer3]').attr('value', row.answers[2]);
      $('input[name=answer4]').attr('value', row.answers[3]);

      var correctAnswer = row.correctAnswer+1;

      $('input[type=radio][value=' + correctAnswer +"]").parent().addClass('active');
      $('input[type=radio][value=' + correctAnswer +"]").attr('checked', '');

      $.get(
          "api.php/qrcodes/" + row.id,
          {},
          function(data) {
              var qrCodes = JSON.parse(data);
              console.log(data);
              $('#question-qr').attr('src', qrCodes.question);
              $('#coin-qr').attr('src', qrCodes.coin);
          }
      );

      $.get(
          "api.php/qrcodesprint/" + row.id,
          {},
          function(data) {
              var qrCodes = JSON.parse(data);
              console.log(data);
              $('#print-question-qr').attr('src', qrCodes.question);
              $('#print-coin-qr').attr('src', qrCodes.coin);
          }
      );

      $('#print-title').html("Frage: " + row.question);

      $('#delete-button').removeClass('hidden');
      $('#print-button').removeClass('hidden');

    });

    $('#question-table > tbody > tr').attr('data-toggle', 'modal');
    $('#question-table > tbody > tr').attr('href', '#question-modal');
    $('#question-table > tbody > tr').attr('style', 'cursor: pointer');
    // data-toggle="modal" href="#question-modal"
  };

  overview.showModal = function() {
    $('#question-modal').modal('show');
  };
}( window.overview = window.overview || {}, jQuery ));
