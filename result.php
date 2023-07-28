<!DOCTYPE html>
<html>
<head>
    <title>Test Result</title>
    <style>
        /* Add your CSS styling here */
    </style>
</head>
<body>
<?php
session_start();
$finalScore = $_SESSION['score'] ?? 0;
?>

<h1>Test Result</h1>
<p>Your final score is: <?php echo $finalScore; ?></p>
<p>Thank you for taking the test!</p>
<form method="get">
    <button type="submit" name="restart" value="1">Start Again</button>
</form>
</body>
</html>
