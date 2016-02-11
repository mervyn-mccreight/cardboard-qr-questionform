<?php
  abstract class Data implements JsonSerializable {
    protected $id;

    abstract public function jsonSerialize();

    abstract public function toJson();

    public function getId() {
      return $this->id;
    }

    public function setId($id) {
      $this->id = $id;
    }
  }

  class Question extends Data {
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
  }

  // TODO: generate corresponding coin (+JSON file)

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

  // TODO: move saving to file into question class

  // new list of questions
  $newQuestions = array();
  $found = false;

  // open questions file
  if (!file_exists('questions/')) {
      mkdir('questions/', 0777, true);
  }

  $fi = new FilesystemIterator("questions/", FilesystemIterator::SKIP_DOTS);
  if ($submittedQuestion->getId() == -1) {
    $submittedQuestion->setId(iterator_count($fi));
  }
  $fileName = "questions/question_" . $submittedQuestion->getId() . ".json";

  // overwrite file with write access
  $dataFile = fopen($fileName, "w");

  // write new questions
  fwrite($dataFile, $submittedQuestion->toJson());

  // close file
  fclose($dataFile);

  // redirect back to previous page
  // TODO: (Better: redirect to question overview, once that exists)
  header('Location: ' . $_SERVER['HTTP_REFERER']);
?>
