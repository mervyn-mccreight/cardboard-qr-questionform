<?php

  function get_questions()
  {
    $questions = array();

    $iterator = new FilesystemIterator("questions/", FilesystemIterator::SKIP_DOTS);
    while($iterator->valid()) {

      $file = fopen($iterator->getPathname(), "r");
      $content = fread($file, filesize($iterator->getPathname()));
      array_push($questions, $content);

      $iterator->next();
    }

    return "[" . implode(",", $questions) . "]";
  }




  $possible_url = array("get_questions");

  $value = "An error has occurred";

  if (isset($_GET["action"]) && in_array($_GET["action"], $possible_url))
  {
    switch ($_GET["action"])
      {
        case "get_questions":
          $value = get_questions();
          break;
      }
  }

  exit($value);
?>
