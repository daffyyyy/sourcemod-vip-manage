<?php
if (file_exists('installer.php')) header('Location: installer.php');
session_start();
require('inc/vips.classes.php');
$class = new Vips();
if ($class->checkAccess(true)) {
    header('Location: dashboard.php');
}
if (isset($_POST['submit'])) {
    $class->login($_POST['login'], $_POST['password']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>:: VIP WEB-PANEL 1.0a ::</title>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="Manage vip users from web panel">
    <meta name="author" content="daffyy">
    <link rel="shortcut icon" href="favicon.ico">


    <!-- FontAwesome JS-->
    <script defer src="assets/plugins/fontawesome/js/all.min.js"></script>

    <!-- App CSS -->
    <link id="theme-style" rel="stylesheet" href="assets/css/portal.css">
    <link rel="stylesheet" href="assets/css/custom.css">

</head>

<body class="app">
    <div class="container app-card">
        <div class="d-flex align-items-center justify-content-center" style="height: 12vh">
            <h1>Logowanie do panelu dodawania vip-ów</h1>
        </div>
        <div class="container d-flex align-items-center justify-content-center" style="height: 50vh">
            <form method="POST">
                <?php if (isset($class->error)) : ?>
                    <div class="alert alert-danger" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-lg" viewBox="0 0 16 16">
                            <path d="M6.002 14a2 2 0 1 1 4 0 2 2 0 0 1-4 0zm.195-12.01a1.81 1.81 0 1 1 3.602 0l-.701 7.015a1.105 1.105 0 0 1-2.2 0l-.7-7.015z" />
                        </svg>
                        <?= $class->error; ?>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="" class="form-label">Login</label>
                    <input type="text" class="form-control" id="login" name="login" />
                </div>
                <div class="mb-3">
                    <label for="" class="form-label">Hasło</label>
                    <input type="password" class="form-control" id="password" name="password" />
                </div>
                <div class="mb-3">
                    <input type="submit" class="btn btn-primary" name="submit" value="Zaloguj" />
                </div>


            </form>
        </div>
    </div>
</body>

</html>