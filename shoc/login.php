<?php

require_once("path.php");

require_once("class/clsPage.php");

session_start();

$LoginPage = new clsPage();
$LoginPage->Title = "Login";

$ContentPanelB = '';

$ContentPanelB .= '<h1>'.$LoginPage->Title.'</h1>';

$ContentPanelB .= "<div class='hmenu'><ul>";
$ContentPanelB .= '<li><a href="account.php">&bull; Not a member?</a></li>';
$ContentPanelB .= '<li><a href="usrreset.php">&bull; Forgot your password?</a></li>';
$ContentPanelB .= "</ul></div>";

$ContentPanelB .= "<br/>";

$ContentPanelB .= '<form method="post" action="doLogin.php">';
$ContentPanelB .= '<table class="sdbluebox">';
$ContentPanelB .= '<tr><td colspan="2">Members log in here:</td></tr>';
$ContentPanelB .= '<tr><td>Login with an email address:</td><td><input type="text" name="handle" size="80"/></td></tr>';
$ContentPanelB .= '<tr><td>Password:</td><td><input type="password" name="passwd"/></td></tr>';
$ContentPanelB .= '<tr><td colspan="2"><input type="submit" value="Log in"/></td></tr>';
$ContentPanelB .= '</table></form>';

$LoginPage->ContentPanelB = $ContentPanelB;

$LoginPage -> Display();

?>