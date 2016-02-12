<?php

  function get_questions()
  {
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

  $possible_url = array("get_questions", "get_qrcodes", "get_question_count");

  $value = "An error has occurred";

  if (isset($_GET["action"]) && in_array($_GET["action"], $possible_url))
  {
    switch ($_GET["action"])
      {
        case "get_questions":
          $value = get_questions();
          break;
        case "get_qrcodes":
          if (isset($_GET["id"]) && isset($_GET["dimension"]))
            $value = get_qrcodes_by_id($_GET["id"], $_GET["dimension"]);
          else
            $value = "ERROR";
          break;
        case "get_question_count":
          $value = get_question_count();
          break;
      }
  }

  exit($value);
?>
