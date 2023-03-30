<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    $target_dir = 'uploads/';
    $target_file = $target_dir . basename($_FILES['image']['name']);
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
      echo 'Image uploaded successfully';
    } else {
      echo 'Error uploading image';
    }
  } else {
    echo 'No image file uploaded';
  }
}
?>