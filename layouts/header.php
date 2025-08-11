<?php $user = current_user(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= !empty($page_title) ? remove_junk($page_title) : (!empty($user) ? ucfirst($user['name']) : "Inventory Management System") ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker3.min.css"/>
    <link rel="stylesheet" href="libs/css/main.css"/>
    <script>
        function updateClock() {
            document.getElementById('live-time').innerHTML = new Date().toLocaleString('en-US', {
                month: 'long', day: 'numeric', year: 'numeric',
                hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: true
            });
        }
        setInterval(updateClock, 1000);
        window.onload = updateClock;
    </script>
</head>
<body>
<?php if ($session->isUserLoggedIn(true)): ?>
    <header id="header">
        <div class="logo pull-left">Inventory System</div>
        <div class="header-content">
            <div class="header-date pull-left"><strong id="live-time"><?= date("F j, Y, g:i a") ?></strong></div>
            <div class="pull-right">
                <ul class="info-menu list-inline">
                    <li class="profile">
                        <a href="#" data-toggle="dropdown" class="toggle">
                            
                            <span><?= remove_junk(ucfirst($user['name'])); ?> <i class="caret"></i></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="profile.php?id=<?= (int)$user['id']; ?>"><i class="glyphicon glyphicon-user"></i> Profile</a></li>
                            <li><a href="edit_account.php"><i class="glyphicon glyphicon-cog"></i> Settings</a></li>
                            <li class="last"><a href="logout.php"><i class="glyphicon glyphicon-off"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    <div class="sidebar">
        <?php include_once($user['user_level'] === '1' ? 'admin_menu.php' : ($user['user_level'] === '2' ? 'special_menu.php' : 'user_menu.php')); ?>
    </div>
<?php endif; ?>

<div class="page">
    <div class="container-fluid">
