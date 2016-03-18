<?php
  // import the data-model.
  require_once('data.php');

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
?>
