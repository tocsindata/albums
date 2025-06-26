# albums
UserSpice Albums - WIP

### This is a work in Prgress
# Albums Plugin for UserSpice

* Drag this folder into `/usersc/plugins/`.
* Log in as an admin → *Plugin Manager* → *Install*.

## Features
* Creates `albums` and `album_photos` tables.
* Secure admin page (`plugins/albums/admin.php`) for:
  * Creating an album
  * Drag-&-drop / multi-file uploads
  * Auto thumbnail generation (requires GD)
* `renderAlbumGrid($page, $perPage)` helper renders the Bootstrap grid shown in the docs.

Add `<?= renderAlbumGrid($_GET['page'] ?? 1, 8); ?>` anywhere in your theme.

Enjoy!
