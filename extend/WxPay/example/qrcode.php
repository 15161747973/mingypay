<?php
class Code{
    public function getCode($url)
    {
        error_reporting(E_ERROR);
        require_once 'phpqrcode/phpqrcode.php';
//        $url = urldecode($_GET["data"]);
        $data = QRcode::png($url);
        return $data;
    }
}
