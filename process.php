<?php
  abstract class DataType
  {
      const COIN  = 0;
      const QUESTION = 1;
  }

  abstract class Data implements JsonSerializable {
    protected $id;
    protected $type;

    abstract public function jsonSerialize();

    abstract public function toJson();

    public function getId() {
      return $this->id;
    }

    public function setId($id) {
      $this->id = $id;
    }

    protected abstract function getPath();

    public function saveToFile() {
      // open questions file
      if (!file_exists($this->getPath())) {
          mkdir($this->getPath(), 0777, true);
      }

      $fi = new FilesystemIterator($this->getPath(), FilesystemIterator::SKIP_DOTS);
      if ($this->getId() == -1) {
        $this->setId(iterator_count($fi));
      }
      $fileName = $this->getPath() . $this->getId() . ".json";

      // overwrite file with write access
      $dataFile = fopen($fileName, "w");

      // write new questions
      fwrite($dataFile, $this->toJson());

      // close file
      fclose($dataFile);
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
      $this->type = DataType::QUESTION;
    }

    public function jsonSerialize() {
      return get_object_vars($this);
    }

    public function toJson() {
      return json_encode($this);
    }

    protected function getPath() {
      return "questions/";
    }
  }

  class Coin extends Data {
    public function __construct($id) {
      $this->id = $id;
      $this->type = DataType::COIN;
    }

    public function jsonSerialize() {
      return get_object_vars($this);
    }

    public function toJson() {
      return json_encode($this);
    }

    protected function getPath() {
      return "coins/";
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
  $submittedQuestion->saveToFile();

  // create coin only after question has been saved to ensure the id has been initialized
  $coin = new Coin($submittedQuestion->getId());
  $coin->saveToFile();

  // redirect back to previous page
  // TODO: (Better: redirect to question overview, once that exists)
  header('Location: ' . $_SERVER['HTTP_REFERER']);

  // TODO:
  // sample qr code URL: https://chart.googleapis.com/chart?chs=500x500&cht=qr&chl={%22question%22:%22asda%22,%22answers%22:[%22asdas%22,%22asd%22,%22asd%22,%22gfdags%22],%22correctAnswer%22:0,%22id%22:0,%22type%22:1}&choe=UTF-8
?>
