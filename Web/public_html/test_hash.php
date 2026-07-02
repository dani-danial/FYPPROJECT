<?php
$password_from_app = "12345678";
$hash_from_db = '$2y$12$9mtwup6cfU0ZlmfkXB...'; // Paste the FULL hash from your DB here

if (password_verify($password_from_app, $hash_from_db)) {
    echo "Password Matches!";
} else {
    echo "Password Mismatch!";
}
?>