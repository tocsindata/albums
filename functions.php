<?php
// usersc/plugins/albums/functions.php

/**
 * Render a paginated Bootstrap grid of album covers.
 */
function renderAlbumGrid(int $page = 1, int $perPage = 8): string
{
    $db        = DB::getInstance();
    $page      = max(1, $page);
    $perPage   = max(1, $perPage);
    $offset    = ($page - 1) * $perPage;

    $totalRows = (int) $db->query("SELECT COUNT(*) AS cnt FROM albums")->first()->cnt;
    $totalPages = (int) ceil($totalRows / $perPage);

    $albums = $db->query(
        "SELECT id, name, slug, cover_thumbnail
           FROM albums
       ORDER BY created_at DESC
          LIMIT {$perPage} OFFSET {$offset}"
    )->results();

    ob_start();
    echo '<div class="row pt-2 pb-5 mb-5 gx-4 gy-5 albums">';
    foreach ($albums as $a) {
        $name  = htmlspecialchars($a->name, ENT_QUOTES, 'UTF-8');
        $slug  = urlencode($a->slug);
        $thumb = htmlspecialchars($a->cover_thumbnail, ENT_QUOTES, 'UTF-8');

        echo <<<HTML
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3 cover-image">
            <a href="/album/{$slug}">
                <img data-src="{$thumb}" class="lazyload rounded" src="{$thumb}">
                <div class="pb-3">{$name}</div>
            </a>
        </div>
HTML;
    }
    echo '</div>';

    // simple pager
    if ($totalPages > 1) {
        echo '<nav aria-label="Album pagination"><ul class="pagination justify-content-center">';
        for ($i = 1; $i <= $totalPages; $i++) {
            $active = $i === $page ? ' active' : '';
            echo "<li class='page-item{$active}'><a class='page-link' href='?page={$i}'>{$i}</a></li>";
        }
        echo '</ul></nav>';
    }

    return ob_get_clean();
}

/**
 * Tiny helper to make a 300-px wide thumbnail (GD required).
 */
function make_thumb(string $src, string $dest, int $maxWidth = 300): bool
{
    [$w, $h, $type] = getimagesize($src);
    if (!$w || !$h)   { return false; }

    $ratio = $maxWidth / $w;
    $newW  = $maxWidth;
    $newH  = (int) round($h * $ratio);

    switch ($type) {
        case IMAGETYPE_JPEG: $srcImg = imagecreatefromjpeg($src); break;
        case IMAGETYPE_PNG:  $srcImg = imagecreatefrompng($src);  break;
        default: return false;
    }

    $dstImg = imagecreatetruecolor($newW, $newH);
    imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $w, $h);

    $ok = imagejpeg($dstImg, $dest, 85);
    imagedestroy($srcImg);
    imagedestroy($dstImg);
    return $ok;
}
