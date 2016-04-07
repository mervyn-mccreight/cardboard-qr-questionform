<?php
  // Import the data-model.
  require_once("data.php");

  /**
   * Class to hold google-api qr-code urls for a pair (question, coin).
   *
   * It implements the JsonSerializable-interface.
   */
  class QRCodeUrls implements JsonSerializable {
    private $question;
    private $coin;

    /**
     * Constructor.
     *
     * @param string      $questionUrl       The question qr-code url.
     * @param string      $coinUrl           The coin qr-code url.
     *
     * @return QRCodeUrls
     */
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

  /***************************************************************************/
  /* QUESTIONS */

  /**
   * Function to handle the "/questions/" REST-GET call to get all questions.
   *
   * It looks for all existing questions on the file-system and
   * returns them all in JSON-format.
   * The JSON will be an JSON-object, containing an JSON-object "questions",
   * which contains an array of all questions in JSON.
   *
   * @return string - The JSON answer.
   */
  function get_questions() {
    if (!file_exists("questions/")) {
      return '{"questions": []}';
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

    return '{"questions":' . "[" . implode(",", $questions) . "]" . "}";
  }

  /**
   * Function to handle the "/questions/<id>" REST-GET call.
   * It returns the questions json, if a question with the given id exists.
   * Otherwise it returns an empty 404 HttpMessage.
   *
   * @param int         $id             The question id.
   *
   * @return string - The JSON-string for the question.
   */
  function get_question_by_id($id) {
    $filePath = "questions/".$id.".json";

    if (file_exists($filePath)) {
      $file = fopen($filePath, "r");
      $content = fread($file, filesize($filePath));
      fclose($file);
      return $content;
    }

    http_response_code(404);
    return "";
  }

  /**
   * Function to handle the "/questions/<id>" REST-DELETE call.
   * It deletes the question with the given id, if existing.
   * The same for the respective coin.
   * Otherwise nothing happens.
   *
   * @param int         $id             The question id.
   *
   * @return string - an empty string.
   */
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

  /**
   * Function to handle the "/qrcodes/<id>" and "/qrcodesprint/<id>" REST-GET calls.
   *
   * It returns a JSON-string in which the google-api qr-code urls for both
   *  - the question
   *  - the coin
   * are contained in fields with respective names.
   *
   * @param int         $id             The question id.
   * @param int         $dimension      The wanted qr-code dimension.
   *
   * @return string - The answer JSON-string.
   */
  function get_qrcodes_by_id($id, $dimension) {
    $questionFilename = "questions/".$id.".json";

    if (!file_exists($questionFilename)) {
      http_response_code(404);
      return "";
    }

    $questionShortObject = (object) ['id' => $id, 'type' => DataType::QUESTION];

    $questionUrl = "https://chart.googleapis.com/chart?cht=qr&choe=UTF-8"
          ."&chs=".$dimension."x".$dimension
          ."&chl=".urlencode(json_encode($questionShortObject));

    $coinFilename = "coins/".$id.".json";

    if (!file_exists($coinFilename)) {
      http_response_code(404);
      return "";
    }

    $file = fopen($coinFilename, "r");
    $coinContent = fread($file, filesize($coinFilename));
    fclose($file);

    $coinUrl = "https://chart.googleapis.com/chart?cht=qr&choe=UTF-8"
          ."&chs=".$dimension."x".$dimension
          ."&chl=".urlencode($coinContent);

    $qrCodes = new QRCodeUrls($questionUrl, $coinUrl);
    return $qrCodes->toJson();
  }

  /**
   * Function to handle the "/questioncount" REST-GET call.
   *
   * It returns the count of currently existing questions as an string,
   * representing the integer value of the sum.
   *
   * @return string - The sum of questions.
   */
  function get_question_count() {
    if (!file_exists("questions/")) {
      return "0";
    }

    $iterator = new FilesystemIterator("questions/", FilesystemIterator::SKIP_DOTS);
    return "".iterator_count($iterator);
  }

  /**
   * Function to handle the "/questions" REST-POST call.
   *
   * It stores the transmitted question on the file system
   * and redirects to the previous page.
   */
  function save_question() {
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
    header('Location: ' . $_SERVER['HTTP_REFERER']);
  }


  /***************************************************************************/
  /* PARTICLE SYSTEMS */

  /**
   * Function to handle the "/particlesystems/" REST-GET call to get all
   * particle systems.
   *
   * It looks for all existing particle systems on the file-system and
   * returns them all in JSON-format.
   * The JSON will be an JSON-object, containing an JSON-object "particleSystems",
   * which contains an array of all particle systems in JSON.
   *
   * @return string - The JSON answer.
   */
  function get_particlesystems() {
    if (!file_exists("particle/")) {
      return '{"particleSystems": []}';
    }

    $particleSystems = array();

    $iterator = new FilesystemIterator("particle/", FilesystemIterator::SKIP_DOTS);
    while($iterator->valid()) {

      $file = fopen($iterator->getPathname(), "r");
      $content = fread($file, filesize($iterator->getPathname()));
      fclose($file);
      array_push($particleSystems, $content);

      $iterator->next();
    }

    return '{"particleSystems":' . "[" . implode(",", $particleSystems) . "]" . "}";
  }

  /**
   * Function to handle the "/particlesystems/<id>" REST-GET call.
   * It returns the particle system json, if a system with the given id exists.
   * Otherwise it returns an empty 404 HttpMessage.
   *
   * @param int         $id             The particle system id.
   *
   * @return string - The JSON-string for the particle system.
   */
  function get_particlesystem_by_id($id) {
    $filePath = "particle/".$id.".json";

    if (file_exists($filePath)) {
      $file = fopen($filePath, "r");
      $content = fread($file, filesize($filePath));
      fclose($file);
      return $content;
    }

    http_response_code(404);
    return "";
  }

  /**
   * Function to handle the "/particlesystems/<id>" REST-DELETE call.
   * It deletes the particle system with the given id, if existing.
   * Otherwise nothing happens.
   *
   * @param int         $id             The particle system id.
   *
   * @return string - an empty string.
   */
  function delete_particlesystem($id) {
    $particleFilename = "particle/".$id.".json";

    if (file_exists($particleFilename)) {
      unlink($particleFilename);
    }

    return "";
  }

  /**
   * Function to handle the "/particleqrcode/<id>" and "/particleqrcodeprint/<id>" REST-GET calls.
   *
   * It returns a JSON-string in which the google-api qr-code urls for the system.
   * are contained in fields with respective names.
   *
   * @param int         $id             The particle system id.
   * @param int         $dimension      The wanted qr-code dimension.
   *
   * @return string - The answer JSON-string.
   */
  function get_particlesystem_qrcode_by_id($id, $dimension) {
    $filename = "particle/".$id.".json";

    if (!file_exists($filename)) {
      http_response_code(404);
      return "";
    }

    $file = fopen($filename, "r");
    $particleContent = fread($file, filesize($filename));
    fclose($file);

    $Url = "https://chart.googleapis.com/chart?cht=qr&choe=UTF-8"
          ."&chs=".$dimension."x".$dimension
          ."&chl=".urlencode($particleContent);

    return '{"url":"'.$Url.'"}';
  }

  /**
   * Function to handle the "/particlesystems" REST-POST call.
   *
   * It stores the transmitted particle system on the file system
   * and redirects to the previous page.
   */
  function save_particle_system() {
    // create particle system from POST data
    $submittedParticleSystem = new ParticleSystem(
                              $_POST["particleSystemId"],
                              $_POST["startColor"],
                              $_POST["endColor"]
                            );
    $submittedParticleSystem->saveToFile();

    // redirect back to previous page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
  }


  /***************************************************************************/
  /* MAIN CALL HANDLING */

  /**
   * Function to handle an error.
   * It simply returns an empty 400 HttpMessage.
   *
   * @return void
   */
  function handle_error() {
    http_response_code(400);
    exit("");
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

          if ($requestSize == 3) {
            if ($request[2] == "") {
              exit(get_question_by_id($request[1]));
            }
          }

          handle_error();
        case 'qrcodes':
          exit(get_qrcodes_by_id($request[1], 200));
        case 'qrcodesprint':
          exit(get_qrcodes_by_id($request[1], 400));
        case 'particleqrcode':
          exit(get_particlesystem_qrcode_by_id($request[1], 200));
        case 'particleqrcodeprint':
          exit(get_particlesystem_qrcode_by_id($request[1], 400));
        case 'questioncount':
          exit(get_question_count());
        case 'particlesystems':
          if ($requestSize == 1) {
            exit(get_particlesystems());
          }

          if ($requestSize == 2) {
            if ($request[1] == "") {
              exit(get_particlesystems());
            }
            exit(get_particlesystem_by_id($request[1]));
          }

          if ($requestSize == 3) {
            if ($request[2] == "") {
              exit(get_particlesystem_by_id($request[1]));
            }
          }

          handle_error();
        default:
          handle_error();
      }
      break;
    case 'DELETE':
      switch ($request[0]) {
        case 'questions':
          exit(delete_question($request[1]));
        case 'particlesystems':
          exit(delete_particlesystem($request[1]));
        default:
          handle_error();
      }
      break;
    case 'POST':
      switch ($request[0]) {
        case 'particlesystems':
          save_particle_system();
          exit("");
        case 'questions':
          save_question();
          exit("");
        default:
          handle_error();
      }
    default:
      handle_error();
  }
?>
