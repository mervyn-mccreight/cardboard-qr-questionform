(function( overview, $, undefined ) {

  overview.clearQuestionModal = function() {
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

  overview.clearParticleModal = function() {
    $('input[name=particleSystemId]').removeAttr('value');

    $('#particle-qr-preview').addClass('hidden');
    $('#particle-delete-button').addClass('hidden');
    $('#particle-print-button').addClass('hidden');

    $('#start-color-picker').colorpicker('setValue', '#000000');
    $('#end-color-picker').colorpicker('setValue', '#000000');

    $('#particle-delete-button').addClass('hidden');
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

  overview.sendDeleteQuestionRequest = function() {
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

  overview.sendDeleteParticleRequest = function() {
    var id = $('input[name=particleSystemId]').val();
    console.log('api.php/particlesystems/' + id);

    $.ajax({
      url: 'api.php/particlesystems/' + id,
      type: 'DELETE',
      success: function(result) {
          console.log(result);
          location.reload(true);
      }
    });
  };

  overview.updateParticleTable = function(jsonString) {
    var tableData = JSON.parse(jsonString);

    $('#particle-table').bootstrapTable({
      data: tableData.particleSystems
    }).on('click-row.bs.table', function (e, row, $element) {

      overview.clearParticleModal();

      $('#particle-qr-preview').removeClass('hidden');
      $('input[name=particleSystemId]').attr('value', row.id);

      $('#start-color-picker').colorpicker('setValue', row.startColor);
      $('#end-color-picker').colorpicker('setValue', row.endColor);

      $.get(
          "api.php/particleqrcode/" + row.id,
          {},
          function(data) {
              var json = JSON.parse(data);
              $('#particle-qr').attr('src', json.url);
          }
      );

      // TODO: fill print-page img-src

      $('#particle-delete-button').removeClass('hidden');
      // TODO: print page
    });

    $('#particle-table > tbody > tr').attr('data-toggle', 'modal');
    // TODO: add correct modal link
    $('#particle-table > tbody > tr').attr('href', '#particle-modal');
    $('#particle-table > tbody > tr').attr('style', 'cursor: pointer');
    // data-toggle="modal" href="#question-modal"
  };

  overview.updateQuestionTable = function(jsonString) {
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
    // TODO: only do this if there is a matching row in the json (table is filled.)
    $('#question-table > tbody > tr').attr('href', '#question-modal');
    $('#question-table > tbody > tr').attr('style', 'cursor: pointer');
    // data-toggle="modal" href="#question-modal"
  };

  overview.showModal = function() {
    $('#question-modal').modal('show');
  };
}( window.overview = window.overview || {}, jQuery ));
