<?php
include "../config/database.php";

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM siswa WHERE id='$id'");

header("Location: siswa.php");
