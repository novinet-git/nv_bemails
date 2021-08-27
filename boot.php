<?php
$oBeMails = new nvBeMails;

$addon = rex_addon::get("nv_bemails");
if (file_exists($addon->getAssetsPath("css/style.css"))) {
    rex_view::addCssFile($addon->getAssetsUrl("css/style.css"));
}
