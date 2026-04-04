<!DOCTYPE html>
<html lang="en">

<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <title> Chloe Becker's Portfolio</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./js/script.js">
</head>

<body>
    <?php require 'header.php'; ?>
    <form>
        <label for="fname">First name:</label><br>
        <input type="text" id="fname" name="fname"><br>

        <label for="lname">Last name:</label><br>
        <input type="text" id="lname" name="lname">

        <label for="company">Company:</label><br>
        <input type="text" id="company" name="company">

        <label for="email">Email:</label><br>
        <input type="text" id="email" name="email">

        <label for="message">Message:</label><br>
        <textarea id="message" name="message"></textarea>
    </form>
</body>

</html>