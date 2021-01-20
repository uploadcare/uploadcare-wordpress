<?php declare(strict_types=1);


class UploadcareImageEditor extends WP_Image_Editor
{
    public function __construct($file)
    {
        parent::__construct($file);
    }

    public static function test($args = array())
    {
        $path = $args['path'] ?? null;
        if ($path === null) {
            return parent::test($args);
        }

        return \str_contains($path, \get_option('uploadcare_cdn_base'));
    }

    public static function supports_mime_type($mimeType): bool
    {
        return \in_array($mimeType, ['image/jpeg', 'image/png']);
    }

    public function load()
    {
        return true;
    }

    public function save($destfilename = null, $mime_type = null)
    {
        // TODO: Implement save() method.
    }

    public function resize($max_w, $max_h, $crop = false)
    {
        // TODO: Implement resize() method.
    }

    public function multi_resize($sizes)
    {
        // TODO: Implement multi_resize() method.
    }

    public function crop($src_x, $src_y, $src_w, $src_h, $dst_w = null, $dst_h = null, $src_abs = false)
    {
        // TODO: Implement crop() method.
    }

    public function rotate($angle)
    {
        // TODO: Implement rotate() method.
    }

    public function flip($horz, $vert)
    {
        // TODO: Implement flip() method.
    }

    public function stream($mime_type = null)
    {
        \header('Content-Type: application/javascript');
        \file_put_contents(STDOUT, "<script>console.log('Hello, World!')</script>");
        return true;
    }
}
