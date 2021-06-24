<?php
class Vips
{
    protected $database, $config;
    public $error, $success;

    public function __construct()
    {
        $this->config = require('config.php');
    }

    protected function database(): PDO
    {
        return @new PDO("mysql:host={$this->config['db']['host']};dbname={$this->config['db']['database']}", $this->config['db']['user'], $this->config['db']['password']);
    }

    public function login($username, $password): void
    {
        $pdo = $this->database();

        $stmnt = $pdo->prepare('SELECT * FROM `users` WHERE `name` = ?');
        $stmnt->bindParam(1, $username, PDO::PARAM_STR);
        $stmnt->execute();
        $user = $stmnt->fetch(PDO::FETCH_OBJ);

        if (!$user) {
            $this->error = "Nie ma takiego użytkownika!";
            return;
        }

        if (password_verify($password, $user->password)) {
            $_SESSION['user'] = $user->name;
            header('Location: dashboard.php');
        } else {
            $this->error = "Wprowadziłeś błędne dane!";
        }
    }

    public function checkAccess($login = false): bool
    {
        $access = isset($_SESSION['user']) ? true : false;
        if (!$login) {
            if (!$access) {
                header('Location: index.php');
                exit();
            }
        }

        return $access;
    }

    public function isAdmin(): bool
    {
        $pdo = $this->database();

        $query = $pdo->query("SELECT `id` FROM `users` WHERE `name` = '{$_SESSION['user']}' AND `role` = 'Administrator'")->fetch();

        if (!$query) {
            header('Location: index.php');
            exit();
        }

        return (bool) $query;
    }

    public function getCountVips(): int
    {
        $pdo = $this->database();
        return $pdo->query('SELECT count(*) FROM `vips`')->fetchColumn();
    }

    public function getCountServers(): int
    {
        $pdo = $this->database();
        return $pdo->query('SELECT count(*) FROM `servers`')->fetchColumn();
    }

    public function getServers(): array
    {
        $pdo = $this->database();

        $stmnt = $pdo->query('SELECT * FROM `servers` ORDER BY `id` DESC');
        return $stmnt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getUsers(): array
    {
        $pdo = $this->database();

        $stmnt = $pdo->query('SELECT id,name,role FROM `users` ORDER BY `id` ASC');
        return $stmnt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getVips(): array
    {
        $pdo = $this->database();

        $stmnt = $pdo->query('SELECT vips.*, servers.name server_name, servers.id server_id FROM `vips` INNER JOIN `servers` ON vips.server_address = servers.address ORDER BY `id` DESC');
        return $stmnt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getVipRoles(): array
    {
        return $this->config['roles'];
    }

    public function addServer($name, $address): void
    {
        $pdo = $this->database();
        $stmnt = $pdo->prepare('INSERT INTO `servers` (`name`, `address`) VALUES(?, ?)');
        $stmnt->bindParam(1, $name, PDO::PARAM_STR);
        $stmnt->bindParam(2, $address, PDO::PARAM_STR);
        $stmnt->execute();
        $this->success = "Pomyślnie dodałeś serwer!";
    }

    public function editServer($data): void
    {
        $pdo = $this->database();

        $stmnt = $pdo->prepare('UPDATE `servers` SET `name` = ?, `address` = ? WHERE `id` = ?');
        $stmnt->bindParam(1, $data['server_name'], PDO::PARAM_STR);
        $stmnt->bindParam(2, $data['server_address'], PDO::PARAM_STR);
        $stmnt->bindParam(3, $data['server_id'], PDO::PARAM_STR);
        $stmnt->execute();
        $this->success = "Pomyślnie edytowałeś serwer!";
    }

    public function deleteServer($id, $address): void
    {
        $pdo = $this->database();
        $pdo->query("DELETE FROM `vips` WHERE `server_address` = '$address'");
        $pdo->query("DELETE FROM `servers` WHERE `id` = $id");
        $this->success = "Pomyślnie usunąłeś serwer i dodanych do niego vip-ów!";
    }

    public function addVip($data): void
    {
        $pdo = $this->database();

        $server = isset($data['vip_all_servers']) ? 'all' : $data['server_address'];

        $stmnt = $pdo->prepare('INSERT INTO `vips` SET `name` = ?, `steamid` = ?, `flags` = ?, `expire` = ?, `server_address` = ?');
        $stmnt->bindParam(1, $data['vip_name'], PDO::PARAM_STR);
        $stmnt->bindParam(2, $data['vip_steamid'], PDO::PARAM_STR);
        $stmnt->bindParam(3, $data['vip_flags'], PDO::PARAM_STR);
        $stmnt->bindParam(4, $data['vip_expire'], PDO::PARAM_STR);
        $stmnt->bindParam(5, $server, PDO::PARAM_STR);
        $stmnt->execute();
        $this->success = "Pomyślnie dodałeś vip-a";
    }

    public function editVip($data): void
    {
        $pdo = $this->database();

        $server = isset($data['vip_all_servers']) ? 'all' : $data['vip_server'];

        $stmnt = $pdo->prepare('UPDATE `vips` SET `name` = ?, `steamid` = ?, `flags` = ?, `expire` = ?, `server_address` = ? WHERE `id` = ?');
        $stmnt->bindParam(1, $data['vip_name'], PDO::PARAM_STR);
        $stmnt->bindParam(2, $data['vip_steamid'], PDO::PARAM_STR);
        $stmnt->bindParam(3, $data['vip_flags'], PDO::PARAM_STR);
        $stmnt->bindParam(4, $data['vip_expire'], PDO::PARAM_STR);
        $stmnt->bindParam(5, $server, PDO::PARAM_STR);
        $stmnt->bindParam(6, $data['vip_id'], PDO::PARAM_STR);
        $stmnt->execute();
        $this->success = "Pomyślnie edytowałeś vip-a";
    }

    public function deleteVip($id): void
    {
        $pdo = $this->database();
        $pdo->query("DELETE FROM `vips` WHERE `id` = $id");
        $this->success = "Pomyślnie usunąłeś vip-a";
    }

    public function addUser($data): void
    {
        $pdo = $this->database();
        $stmnt = $pdo->prepare('INSERT INTO `users` (`name`, `password`, `role`) VALUES(?, ?, ?)');
        $password = password_hash($data['user_password'], PASSWORD_BCRYPT);
        $stmnt->bindParam(1, $data['user_name'], PDO::PARAM_STR);
        $stmnt->bindParam(2, $password, PDO::PARAM_STR);
        $stmnt->bindParam(3, $data['user_role'], PDO::PARAM_STR);
        $stmnt->execute();
        $this->success = "Pomyślnie dodałeś użytkownika!";
    }

    public function editUser($data): void
    {
        $pdo = $this->database();

        isset($data['password']) ? $stmnt = $pdo->prepare('UPDATE `users` SET `name` = ?, `password` = ?, `role` = ? WHERE `id` = ?') : $stmnt = $pdo->prepare('UPDATE `users` SET `name` = ?, `role` = ? WHERE `id` = ?');

        $stmnt->bindParam(1, $data['user_name'], PDO::PARAM_STR);
        if (isset($data['password'])) {
            $password = password_hash($data['user_password'], PASSWORD_BCRYPT);
            $stmnt->bindParam(2, $password, PDO::PARAM_STR);
        }
        isset($data['password']) ? $stmnt->bindParam(3, $data['user_role'], PDO::PARAM_STR) : $stmnt->bindParam(2, $data['user_role'], PDO::PARAM_STR);
        isset($data['password']) ? $stmnt->bindParam(4, $data['user_id'], PDO::PARAM_STR) : $stmnt->bindParam(3, $data['user_id'], PDO::PARAM_STR);
        $stmnt->execute();
        $this->success = "Pomyślnie edytowałeś użytkownika!";
    }

    public function deleteUser($id): void
    {
        if ($id == 1) return;

        $pdo = $this->database();
        $pdo->query("DELETE FROM `users` WHERE `id` = $id");
        $this->success = "Pomyślnie usunąłeś użytkownika";
    }

    public function getRandomColor(): string
    {
        $colors = ['#fbab87', '#85cd7d', 'violet', '#26ca67', '#cc43fb', '#bb9970', '#8cd3d9', '#c10e36', '#b54108', '#9f603b', '#4f277d', '#34abb6', '#212327', '#dd627e', 'aqua', '#e8d386', 'orange', '#a70618', 'green', '#750376', 'pink'];
        return $colors[array_rand($colors)];
    }
}
