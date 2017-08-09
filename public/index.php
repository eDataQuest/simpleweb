<?php

// Init will include other files, set the search path, and connect the db.
require_once('../private/libs/init.php');

// Create front controller for the site
$fc = qFrontController::get();

// Create routes.  Remember routes must be entered from most specific to least specific.
if (qDBAdmin::isDBReady())
{
  $fc->addRoute('/',       'Home');
}
else
{
  $fc->addRoute('/', 'OffLine');
}
// Run the application
$fc->dispatch();
