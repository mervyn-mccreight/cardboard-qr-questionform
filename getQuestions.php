<?php
  $questions = array();

  $iterator = new FilesystemIterator("questions/", FilesystemIterator::SKIP_DOTS);
  while($iterator->valid()) {

    $file = fopen($iterator->getPathname(), "r");
    $content = fread($file, filesize($iterator->getPathname()));
    array_push($questions, $content);

    $iterator->next();
  }

  echo "[" . implode(",", $questions) . "]"
?>
