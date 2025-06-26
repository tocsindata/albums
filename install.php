<?php
// usersc/plugins/albums/install.php
// Runs once when you click “install” in the plugin manager.

$db = DB::getInstance();

/* ---- create tables ---------------------------------------------------- */
$db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `albums` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(255)     NOT NULL,
  `slug`            VARCHAR(255)     NOT NULL UNIQUE,
  `cover_thumbnail` VARCHAR(512)     NOT NULL,
  `cover_full`      VARCHAR(512)     DEFAULT NULL,
  `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NULL   ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
SQL);

$db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `album_photos` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `album_id`        BIGINT UNSIGNED NOT NULL,
  `filepath`        VARCHAR(512)     NOT NULL,
  `thumbnail_path`  VARCHAR(512)     NOT NULL,
  `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `album_fk` (`album_id`),
  CONSTRAINT `album_fk`
    FOREIGN KEY (`album_id`) REFERENCES `albums`(`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
SQL);

/* ---- minimal permissions (admin only) --------------------------------- */
$db->insert('permissions', ['permission' => 'albums_admin']);
$permId = $db->lastId();
$db->insert('permission_page_matches', [
  'permission_id' => $permId,
  'page'          => 'plugins/albums/admin.php'
]);

return ['status' => true, 'message' => 'Albums plugin installed'];
