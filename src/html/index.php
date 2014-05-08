<?php
$time_image_upload = 0;
$time_scale_to_base_size = 0;
$time_create_store_base_original = 0;
$time_put_photo_mysql = 0;
$time_resize_multiple = array();
/**
 * Front controller for OpenPhoto.
 *
 * This file takes all requests and dispatches them to the appropriate controller.
 * @author Jaisen Mathai <jaisen@jmathai.com>
 */

require sprintf('%s/libraries/initialize.php', dirname(dirname(__FILE__)));

if($configObj->get('site')->maintenance == 1)
{
  getRoute()->run('/maintenance', EpiRoute::httpGet);
}
elseif($assetEndpoint || $loginEndpoint || (!$runUpgrade && !$runSetup && $hasConfig))
{
  // if we're not running setup, don't need an upgrade and the config file exists, proceed as normal
  // else no config file then load up the setup dependencies
  $routeObj->run();
}
elseif($runUpgrade)
{
  $routeObj->run('/upgrade', ($_SERVER['REQUEST_METHOD'] == 'GET' ? EpiRoute::httpGet : EpiRoute::httpPost));
}
elseif($runSetup)
{
  // if we're not in the setup path (anything other than /setup) then redirect to the setup
  // otherwise we're on one of the setup steps already, so just run it
  if(!isset($_GET['__route__']) || strpos($_GET['__route__'], '/setup') === false)
    $routeObj->redirect('/setup');
  else
    $routeObj->run();
}
else
{
  $routeObj->run('/error/500', EpiRoute::httpGet);
}

#$filename = '/home/ubuntu/timelog.txt';
#file_put_contents($filename, "upload_time," . $time_image_upload . ",", FILE_APPEND);
#file_put_contents($filename, "scale_to_base," . $time_scale_to_base_size . ",", FILE_APPEND);
#file_put_contents($filename, "put_base_original_to_s3_time," . $time_create_store_base_original . ",", FILE_APPEND);
#file_put_contents($filename, "put_to_mysql_time," . $time_put_photo_mysql . ",", FILE_APPEND);
#file_put_contents($filename, "resize_time," . $time_resize_multiple . "\n", FILE_APPEND);
#echo "\n";
#echo "upload_time=" . $time_image_upload . "\n";
#echo "scale_to_base_time=" . $time_scale_to_base_size . "\n";
#echo "put_base_original_to_s3_time=" . $time_create_store_base_original . "\n";
#echo "put_to_mysql_time=" . $time_put_photo_mysql . "\n";
#print_r ($time_resize_multiple) . "\n";
