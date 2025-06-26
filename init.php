<?php
// usersc/plugins/albums/init.php

// ① add a link under Admin → Plugins
$menuHooks = Hooks::getInstance();
$menuHooks->addHook('adminPluginSettingsMenu', 5, function () {
    if (!hasPerm([1])) { return; }                 // site admins
    echo '<li><a href="/usersc/plugins/albums/admin.php"><i class="fa fa-image"></i> Album Manager</a></li>';
});

// ② expose helpers everywhere
require_once __DIR__ . '/functions.php';
