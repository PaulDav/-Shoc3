<?php

require_once("path.php");

require_once("class/clsPage.php");
require_once("class/clsSystem.php");

require_once("class/clsContent.php");

session_start();

$System = new clsSystem();
$Page = new clsPage();
$objContent = new clsContent();



$PanelA = '';
$PanelB = '';
$PanelC = '';

$PanelB .= $objContent->getContent('Home');


$Page->ContentPanelA = $PanelA;
$Page->ContentPanelB = $PanelB;
$Page->ContentPanelC = $PanelC;

$Page -> Display();

?>