<?php
/**
 * @file    graphics_controller.php
 * @brief   Interface for accessing graphics via URI
 *
 * @author  Frank Hellenkamp <jonas@depage.net>
 * @author  Sebastian Reinhold <sebastian@bitbernd.de>
 **/

use depage\graphics\graphics;

/**
 * @brief Interface for accessing graphics via URI
 *
 * Translates GET data to graphics actions.
 **/
class graphics_controller {
    /**
     * @brief Default options array for graphics factory
     **/
    public $defaults = array(
        'extension'     => 'gd',
        'background'    => 'transparent',
    );

    // {{{ __construct()
    /**
     * @brief graphics_controller class constructor
     *
     * @param $options (array) image processing parameters
     **/
    public function __construct($options = NULL) {
        $conf = new config($options);
        $this->options = $conf->getDefaultsFromClass($this);
    }
    // }}}
    // {{{ convert()
    /**
     * @brief Translates GET data to graphics actions
     *
     * Createѕ graphics object and performs action on image. It saves the image
     * to the cache and displays it.
     *
     * @return void
     **/
    public function convert() {
        $command    = explode('-', $_GET['command']);
        $size       = explode('x', $command[1]);
        $root       = $_SERVER['DOCUMENT_ROOT'] . '/depage-cms/';

        $action     = preg_replace("[^A-Za-z]", '', $command[0]);
        $file       = escapeshellcmd($_GET['file']);
        $extension  = strtolower(preg_replace("[^A-Za-z]", '', $_GET['ext']));
        $width      = intval($size[0]);
        $height     = intval($size[1]);

        $cachedFile = ("{$root}cache/graphics/{$file}.{$action}-{$width}x{$height}.{$extension}");

        $img = graphics::factory(
            array(
                'extension'     => $this->defaults['extension'],
                'background'    => $this->defaults['background'],
            )
        );

        $cachePath = dirname($cachedFile);
        if (!is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        try {
            $img->{"add$action"}($width, $height)->render($root . $file, $cachedFile);

            if ($extension === 'jpg' || $extension === 'jpeg') {
                header("Content-type: image/jpeg");
                imagejpeg(imagecreatefromjpeg($cachedFile));
            } else if ($extension === 'png') {
                header("Content-type: image/png");
                imagejpeg(imagecreatefrompng($cachedFile));
            } else if ($extension === 'gif') {
                header("Content-type: image/gif");
                imagejpeg(imagecreatefromgif($cachedFile));
            }

        } catch (depage\graphics\graphics_exception $expected) {
            // TODO handle exception
        }
    }
    // }}}
}