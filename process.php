<?php
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
  // TODO: (Better: redirect to question overview, once that exists)
  header('Location: ' . $_SERVER['HTTP_REFERER']);

  // TODO:
  // sample qr code URL: https://chart.googleapis.com/chart?chs=500x500&cht=qr&chl={%22question%22:%22asda%22,%22answers%22:[%22asdas%22,%22asd%22,%22asd%22,%22gfdags%22],%22correctAnswer%22:0,%22id%22:0,%22type%22:1}&choe=UTF-8
?>
