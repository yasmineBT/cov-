<?php
session_start();

//session supprimer quand il log out
session_destroy();

// redirige vers index xelcome page
header('Location: index.php');
exit();
?>
