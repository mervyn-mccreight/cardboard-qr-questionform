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
      if ($this->getId() == "") {
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
?>
