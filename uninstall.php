<?php
// usersc/plugins/albums/uninstall.php
$db = DB::getInstance();
$db->query("DROP TABLE IF EXISTS album_photos");
$db->query("DROP TABLE IF EXISTS albums");
$db->query("DELETE FROM permissions WHERE permission = ?", ['albums_admin']);
return ['status' => true, 'message' => 'Albums plugin uninstalled'];
