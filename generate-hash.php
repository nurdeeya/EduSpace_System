<?php

//$testPassword = 'TestPassword123!';
//$passwordHash = password_hash (
//    $testPassword,
//    PASSWORD_DEFAULT
//);

//echo $passwordHash;

echo "Student password hash:<br>";
echo password_hash('Student@123', PASSWORD_DEFAULT);

echo "<br><br>Lecturer password hash:<br>";
echo password_hash('Lecturer@123', PASSWORD_DEFAULT);