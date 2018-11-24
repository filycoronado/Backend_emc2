<?php
$objetivo = dirname(__FILE__).'/../../common/images/';
$enlace = 'images';
symlink($objetivo, $enlace);

echo readlink($enlace);
?>