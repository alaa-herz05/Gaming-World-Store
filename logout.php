<?php

session_start();

session_destroy();

header("Location: hello.html");

exit();

?>