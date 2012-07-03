<?php

if(!session_id()) session_start();
//Require
require_once "Core/App.php";

App::setRoute('/^welcome/', 'welcome', 'GET');

//Set Application Env. Variables
App::Init();

//handler functions
function welcome() {
  echo "<h2>" . ucfirst(__FUNCTION__) . "</h2>";
}

//Route and render output
App::Run();

?>