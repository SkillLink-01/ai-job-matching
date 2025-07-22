<?php
require_once 'db_connect.php';

$db = getMongoDB();
if ($db) {
    echo "✅ MongoDB connection successful.";
} else {
    echo "❌ Failed to connect.";
}
?>
