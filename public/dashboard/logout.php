<?php
session_start();
session_destroy();
header("Location: /grading-system/public/role.php");
exit();
