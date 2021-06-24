<?php
if (isset($_POST['submit']) && count($_POST) >= 5) {
    try {
        $_POST['password_db'] = isset($_POST['password_db']) ? $_POST['password_db'] : '';
        $pdo = new PDO("mysql:host={$_POST['host_db']};dbname={$_POST['database_db']}", $_POST['user_db'], $_POST['password_db']);
    } catch (PDOException $e) {
        $error = "Błąd podczas połączenia z bazą danych!";
    }
    $config = file_get_contents('inc/config.php');
    $config = str_replace(
        [
            'change_host',
            'change_user',
            'change_password',
            'change_database'
        ],
        [
            $_POST['host_db'],
            $_POST['user_db'],
            $_POST['password_db'],
            $_POST['database_db'],
        ],
        $config
    );
    file_put_contents('inc/config.php', $config);
    try {
        $pdo->query("CREATE TABLE `servers` (
            `id` int(11) NOT NULL,
            `name` varchar(64) NOT NULL,
            `address` varchar(255) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
          ");
        $pdo->query("CREATE TABLE `users` (
            `id` int(11) NOT NULL,
            `name` varchar(64) NOT NULL,
            `password` varchar(255) NOT NULL,
            `role` varchar(32) NOT NULL DEFAULT 'Użytkownik'
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
          ");
        $pdo->query("CREATE TABLE `vips` (
            `id` int(11) NOT NULL,
            `name` varchar(64) NOT NULL,
            `steamid` varchar(64) NOT NULL,
            `flags` varchar(64) NOT NULL,
            `expire` date NOT NULL,
            `server_address` varchar(255) NOT NULL
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
          ");
        $pdo->query("INSERT INTO `servers` (`id`, `name`, `address`) VALUES
          (1, 'Wszystkie', 'all');
          ");
        $pdo->query("ALTER TABLE `servers`
        ADD PRIMARY KEY (`id`);
      ");
        $pdo->query("ALTER TABLE `users`
        ADD PRIMARY KEY (`id`);
        ");
        $pdo->query("ALTER TABLE `vips`
            ADD PRIMARY KEY (`id`);
        ");
        $pdo->query("ALTER TABLE `servers`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
        ");
        $pdo->query("ALTER TABLE `users`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
        ");
        $pdo->query("ALTER TABLE `vips`
        MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
        ");
    } catch (PDOException $e) {
        $error = "Błąd podczas tworzenia tabel!";
    }

    require('inc/vips.classes.php');
    $class = new Vips();

    $_POST['user_role'] = "Administrator";
    $class->addUser($_POST);
    unlink(__FILE__);
    header('Location: index.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>:: VIP WEB-PANEL 1.0a - Instalator::</title>

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
</head>

<body>
    <div class="container d-flex align-items-center justify-content-center" style="height: 100vh">
        <form method="POST">
            <?php if (isset($error)) : ?>
                <div class="alert alert-danger" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-lg" viewBox="0 0 16 16">
                        <path d="M6.002 14a2 2 0 1 1 4 0 2 2 0 0 1-4 0zm.195-12.01a1.81 1.81 0 1 1 3.602 0l-.701 7.015a1.105 1.105 0 0 1-2.2 0l-.7-7.015z" />
                    </svg>
                    <?= $error; ?>
                </div>
            <?php endif; ?>
            <h3>Baza danych</h3>
            <hr />
            <div class="mb-3">
                <label for="" class="form-label">Host bazy danych</label>
                <input type="text" class="form-control" id="host_db" value="localhost" name="host_db" />
            </div>
            <div class="mb-3">
                <label for="" class="form-label">Użytkownik bazy danych</label>
                <input type="text" class="form-control" id="user_db" name="user_db" />
            </div>
            <div class="mb-3">
                <label for="" class="form-label">Hasło bazy danych</label>
                <input type="password" class="form-control" id="password_db" name="password_db" />
            </div>
            <div class="mb-3">
                <label for="" class="form-label">Nazwa bazy danych</label>
                <input type="db" class="form-control" id="database_db" name="database_db" />
            </div>
            <h3>Administrator</h3>
            <hr />
            <div class="mb-3">
                <label for="" class="form-label">Użytkownik</label>
                <input type="text" class="form-control" id="user_name" name="user_name" />
            </div>
            <div class="mb-3">
                <label for="" class="form-label">Hasło</label>
                <input type="password" class="form-control" id="user_password" name="user_password" />
            </div>
            <div class="mb-3">
                <input type="submit" class="btn btn-primary" name="submit" value="Zainstaluj" />
            </div>
        </form>
    </div>
</body>

</html>