<?php

  function get_questions()
  {

    if (!file_exists("questions/")) {
      return "[]";
    }

    $questions = array();

    $iterator = new FilesystemIterator("questions/", FilesystemIterator::SKIP_DOTS);
    while($iterator->valid()) {

      $file = fopen($iterator->getPathname(), "r");
      $content = fread($file, filesize($iterator->getPathname()));
      fclose($file);
      array_push($questions, $content);

      $iterator->next();
    }

    return "[" . implode(",", $questions) . "]";
  }

  class QRCodeUrls implements JsonSerializable {
    private $question;
    private $coin;

    public function __construct($questionUrl, $coinUrl) {
      $this->question = $questionUrl;
      $this->coin = $coinUrl;
    }

    public function jsonSerialize() {
      return get_object_vars($this);
    }

    public function toJson() {
      return json_encode($this);
    }
  }

  function get_qrcodes_by_id($id, $dimension) {
    $questionFilename = "questions/".$id.".json";
    $file = fopen($questionFilename, "r");
    $questionContent = fread($file, filesize($questionFilename));
    fclose($file);

    $questionUrl = "https://chart.googleapis.com/chart?cht=qr&choe=UTF-8"
          ."&chs=".$dimension."x".$dimension
          ."&chl=".urlencode($questionContent);

    $coinFilename = "coins/".$id.".json";
    $file = fopen($coinFilename, "r");
    $coinContent = fread($file, filesize($coinFilename));
    fclose($file);

    $coinUrl = "https://chart.googleapis.com/chart?cht=qr&choe=UTF-8"
          ."&chs=".$dimension."x".$dimension
          ."&chl=".urlencode($coinContent);

    $qrCodes = new QRCodeUrls($questionUrl, $coinUrl);
    return $qrCodes->toJson();
  }

  function get_question_count() {
    $iterator = new FilesystemIterator("questions/", FilesystemIterator::SKIP_DOTS);
    return "".iterator_count($iterator);
  }

  function delete_question($id) {
    $questionFilename = "questions/".$id.".json";
    $coinFilename = "coins/".$id.".json";

    if (file_exists($questionFilename)) {
      unlink($questionFilename);
    }

    if (file_exists($coinFilename)) {
      unlink($coinFilename);
    }

    return "";
  }

  function get_question_by_id($id) {
    $filePath = "questions/".$id.".json";

    if (file_exists($filePath)) {
      $file = fopen($filePath, "r");
      $content = fread($file, filesize($filePath));
      fclose($file);
      return $content;
    }

    return "no question with id: " . $id;
  }

  function handle_error($message) {
    exit($message);
  }

  $method = $_SERVER['REQUEST_METHOD'];
  $request = explode("/", substr(@$_SERVER['PATH_INFO'], 1));
  $requestSize = sizeof($request);

  switch ($method) {
    case 'GET':
      switch ($request[0]) {
        case 'questions':
          if ($requestSize == 1) {
            exit(get_questions());
          }

          if ($requestSize == 2) {
            if ($request[1] == "") {
              // special case.
              // http://localhost/cardboard-qr-marker-frontend/api.php/questions/
              exit(get_questions());
            }
            exit(get_question_by_id($request[1]));
          }

          handle_error("too many arguments in questions call");
        case 'qrcodes':
          exit(get_qrcodes_by_id($request[1], 200));
        case 'qrcodesprint':
          exit(get_qrcodes_by_id($request[1], 400));
        case 'questioncount':
          exit(get_question_count());
        default:
          handle_error('unknown get-call: ' . $request[0]);
      }
      break;
    case 'DELETE':
      switch ($request[0]) {
        case 'questions':
          exit(delete_question($request[1]));
        default:
          handle_error('unknown delete-call: ' . $request[0]);
      }
      break;
    default:
      handle_error('something went very wrong bro.');
      break;
  }
?>
