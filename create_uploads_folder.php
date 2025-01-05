<?php
$upload_dir = 'uploads/';
if (!file_exists($upload_dir) && !is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
echo "Uploads folder created successfully.";
?>