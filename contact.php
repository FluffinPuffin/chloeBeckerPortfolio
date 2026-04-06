<!DOCTYPE html>
<html lang="en">

<head>
    <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
    <title> Chloe Becker's Portfolio</title>
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <?php require 'header.php'; ?>

    <div class="contact-section">
        <h1>Contact</h1>
        <p>Fill out the form below and I'll get back to you.</p>

        <form class="contact-form">
            <div class="form-row">
                <div class="form-field">
                    <label for="fname">First name</label>
                    <input type="text" id="fname" name="fname" placeholder="Jane">
                </div>
                <div class="form-field">
                    <label for="lname">Last name</label>
                    <input type="text" id="lname" name="lname" placeholder="Doe">
                </div>
            </div>

            <div class="form-field">
                <label for="company">Company</label>
                <input type="text" id="company" name="company" placeholder="Acme Inc.">
            </div>

            <div class="form-field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="jane@example.com">
            </div>

            <div class="form-field">
                <label for="message">Message</label>
                <textarea id="message" name="message" placeholder="What's on your mind?"></textarea>
            </div>

            <button type="submit" class="contact-submit">Send message</button>
        </form>
    </div>
</body>

</html>
