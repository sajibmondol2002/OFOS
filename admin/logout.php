<?php
require_once __DIR__ . '/../inc/functions.php';

session_unset();
session_destroy();
redirect('../index.php');
