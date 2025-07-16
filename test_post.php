<?php
// Simple POST test

echo "Request method: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "POST data: ";
var_dump($_POST);
?>
<form method="POST">
  <input name="test" value="test">
  <button type="submit">Test Submit</button>
</form> 