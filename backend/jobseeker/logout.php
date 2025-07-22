<?php
session_start();
session_unset();
session_destroy();

// Redirect to login page (adjust path as needed)
header("Location: /Final_Project1/job-matching-system/frontend/shared/login.html");
exit();
