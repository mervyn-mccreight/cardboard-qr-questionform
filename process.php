<?php
  class Question implements JsonSerializable {
    private $id;
    private $question;
    private $answers;
    private $correctAnswer;

    public function __construct($id, $question, $answers, $correctAnswer) {
      $this->id = $id;
      $this->question = $question;
      $this->answers = $answers;
      $this->correctAnswer = $correctAnswer;
    }

    public function jsonSerialize() {
      return get_object_vars($this);
    }

    public function toJson() {
      return json_encode($this);
    }

    public static function fromJson($json) {
      $stdObj = json_decode($json);
      return fromStdObj($stdObj);
    }

    public static function fromStdObj($stdObj) {
      return new Question($stdObj->id, $stdObj->question, $stdObj->answers, $stdObj->correctAnswer);
    }

    public function getId() {
      return $this->id;
    }
  }

  // create question from POST data
  $submittedQuestion = new Question(
                            $_POST["questionId"],
                            $_POST["question"],
                            array($_POST["answer1"],
                                  $_POST["answer2"],
                                  $_POST["answer3"],
                                  $_POST["answer4"]),
                            $_POST["correct-answer"] - 1
                          );

  // new list of questions
  $newQuestions = array();
  $found = false;

  // open questions file
  $fileName = "questions.json";
  $dataFile = fopen($fileName, "r+");

  // if file existed before
  if (filesize($fileName) > 0) {
    // read all file contents
    $fileContents = fread($dataFile, filesize($fileName));

    // create array of stdClass objects from contents
    $stdQuestions = json_decode($fileContents);

    // convert array elements into Question objects
    // also insert/overwrite with the submitted question
    foreach ($stdQuestions as $stdQuestion) {
      $question = Question::fromStdObj($stdQuestion);

      if ($question->getId() == $submittedQuestion->getId()) {
        array_push($newQuestions, $submittedQuestion);
        $found = true;
      } else {
        array_push($newQuestions, $question);
      }
    }
  }

  // insert submitted question if it has not been used to replace a previous question
  if (!$found) {
    array_push($newQuestions, $submittedQuestion);
  }

  // close read-access file
  fclose($dataFile);

  // overwrite file with write access
  // (ftruncate to clear the existing file lead to errors :-/)
  $dataFile = fopen($fileName, "w");

  // write new questions
  fwrite($dataFile, json_encode($newQuestions));

  // close file
  fclose($dataFile);

  // redirect back to previous page
  // (Better: redirect to question overview, once that exists)
  header('Location: ' . $_SERVER['HTTP_REFERER']);
?>
