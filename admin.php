<?php
// usersc/plugins/albums/admin.php
require_once '../../../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';

if (!securePage($_SERVER['PHP_SELF']) || !hasPerm(['albums_admin',1])) {
    die('No permission');
}

$db     = DB::getInstance();
$errors = $success = [];

/* ---- handle submissions ---------------------------------------------- */
if (isset($_POST['create_album'])) {
    $name = trim(Input::get('album_name'));

    if ($name === '') {
        $errors[] = 'Album name cannot be empty.';
    } else {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $targetDir = __DIR__ . "/uploads/{$slug}";
        $thumbDir  = "{$targetDir}/thumbnail";

        if (!is_dir($thumbDir) && !mkdir($thumbDir, 0755, true)) {
            $errors[] = "Failed to create upload folders.";
        } else {
            /* --- save uploads (multiple allowed) --- */
            $files = $_FILES['photos'] ?? null;
            if ($files && $files['error'][0] !== 4) { // 4=no file uploaded
                $coverThumbPath = '';
                foreach ($files['tmp_name'] as $idx => $tmp) {
                    if ($files['error'][$idx] !== 0) continue;

                    $filename = basename($files['name'][$idx]);
                    $dest     = "{$targetDir}/{$filename}";
                    if (!move_uploaded_file($tmp, $dest)) continue;

                    /* thumbnail */
                    $thumbFile = "{$thumbDir}/{$filename}";
                    if (make_thumb($dest, $thumbFile)) {
                        if ($coverThumbPath === '') $coverThumbPath = ltrim(str_replace($_SERVER['DOCUMENT_ROOT'],'',$thumbFile),'/');
                        $db->insert('album_photos', [
                            'album_id'       => null,    // temp until album row exists
                            'filepath'       => ltrim(str_replace($_SERVER['DOCUMENT_ROOT'],'',$dest),'/'),
                            'thumbnail_path' => ltrim(str_replace($_SERVER['DOCUMENT_ROOT'],'',$thumbFile),'/'),
                        ]);
                    }
                }
                /* create album row (after at least one image) */
                if ($coverThumbPath !== '') {
                    $db->insert('albums', [
                        'name'            => $name,
                        'slug'            => $slug,
                        'cover_thumbnail' => $coverThumbPath,
                    ]);
                    $albumId = $db->lastId();
                    $db->query("UPDATE album_photos SET album_id = ? WHERE album_id IS NULL", [$albumId]);
                    $success[] = "Album \"{$name}\" created with ".count($files['name'])." image(s).";
                } else {
                    $errors[] = 'No valid images were uploaded.';
                }
            } else {
                $errors[] = 'Please select at least one image.';
            }
        }
    }
}

/* ---- page HTML -------------------------------------------------------- */
echo "<div class='content mt-4'><h3>Album Manager</h3>";

foreach ($errors as $e)   { echo "<div class='alert alert-danger'>{$e}</div>"; }
foreach ($success as $s)  { echo "<div class='alert alert-success'>{$s}</div>"; }
?>

<form method="post" enctype="multipart/form-data" class="mt-4">
  <div class="mb-3">
    <label class="form-label">Album name</label>
    <input type="text" name="album_name" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Photos (JPEG/PNG, multiple allowed)</label>
    <input type="file" name="photos[]" class="form-control" accept=".jpg,.jpeg,.png" multiple required>
  </div>
  <button class="btn btn-primary" name="create_album" type="submit">Create album + upload</button>
</form>

<hr>

<h4>Existing albums</h4>
<?php
echo renderAlbumGrid($_GET['page'] ?? 1, 12);
echo "</div>";

require_once $abs_us_root.$us_url_root.'users/includes/template/footer.php';
