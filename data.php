<?php
  /**
    * Enumeration-Class to describe the type of the data.
    */
  abstract class DataType
  {
      const COIN  = 0;
      const QUESTION = 1;
      const PARTICLESYSTEM = 2;
  }

  /**
   * Abstract class to hold abstract data information.
   *
   * It implements the JsonSerializable-interface.
   */
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

    /**
     * Get the next available question id on the file-system.
     *
     * The id-range is [0 - MAX_INT].
     * This function does not fill id-gaps, it looks for the highest existing id
     * and takes the next.
     *
     * @return int - the next available id.
     */
    protected function get_new_id() {
      if (!file_exists("questions/")) {
        return 0;
      }

      $files = array();
      $iterator = new FilesystemIterator("questions/", FilesystemIterator::SKIP_DOTS);

      while($iterator->valid()) {
        array_push($files, $iterator->getFileName());
        $iterator->next();
      }

      if (sizeof($files) == 0) {
        return 0;
      }

      natsort($files);

      $lastFile = end($files);
      $name = explode(".", $lastFile);

      return intval($name[0]) + 1;
    }

    /**
     * Returns the directory-path, in which the files of this data-type will be stored.
     *
     * @return string - The directory-path.
     */
    protected abstract function getPath();

    /**
     * Saves the data-object to a file.
     *
     * The content of the file will be the JSON, describing the content of the
     * data-object. The filename will be <id>.json.
     *
     * For this function to work, php needs to have write-access on the
     * servers file-system folder.
     *
     * @return void
     */
    public function saveToFile() {
      // open questions file
      if (!file_exists($this->getPath())) {
          mkdir($this->getPath(), 0777, true);
      }

      if ($this->getId() === "") {
        $this->setId($this->get_new_id());
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

  /**
   * Concrete question-data class.
   */
  class Question extends Data {
    private $question;
    private $answers;
    private $correctAnswer;

    /**
     * Constructor for the question class.
     *
     * @param int         $id             The question id.
     * @param string      $question       The question-text.
     * @param [string]    $answers        An array of possible multiple-choice answers.
     * @param int         $correctAnswer  The index of the correct answer in the answers-array.
     *
     * @return Question
     */
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

  /**
   * Concrete coin-data class.
   */
  class Coin extends Data {

    /**
     * Constructor for the coin class.
     *
     * The coin-id is logically linked to the question-id.
     * The question with the equivalent id "unlocks" the respective coin in the
     * FH Wedel QR-Schnitzeljagd application.
     *
     * @param int         $id             The coin id.
     *
     * @return Coin
     */
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

  class ParticleSystem extends Data {
    private $startColor;
    private $endColor;

    public function __construct($id, $startColor, $endColor) {
      $this->id = $id;
      $this->startColor = $startColor;
      $this->endColor = $endColor;
      $this->type = DataType::PARTICLESYSTEM;
    }

    public function jsonSerialize() {
      return get_object_vars($this);
    }

    public function toJson() {
      return json_encode($this);
    }

    protected function getPath() {
      return "particle/";
    }

    protected function get_new_id() {
      if (!file_exists("particle/")) {
        return 0;
      }

      $files = array();
      $iterator = new FilesystemIterator("particle/", FilesystemIterator::SKIP_DOTS);

      while($iterator->valid()) {
        array_push($files, $iterator->getFileName());
        $iterator->next();
      }

      if (sizeof($files) == 0) {
        return 0;
      }

      natsort($files);

      $lastFile = end($files);
      $name = explode(".", $lastFile);

      return intval($name[0]) + 1;
    }
  }
?>
