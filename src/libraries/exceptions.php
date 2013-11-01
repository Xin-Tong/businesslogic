<?php
class OPException extends Exception
{
  public static function raise($exception)
  {
    getLogger()->warn($exception->getMessage());
    $class = get_class($exception);
    $baseController = new BaseController;
    switch($class)
    {
      case 'OPAuthorizationException':
      case 'OPAuthorizationSessionException':
      case 'OPAuthorizationPermissionException':
        if(isset($_GET['__route__']) && substr($_GET['__route__'], -5) == '.json')
        {
          echo json_encode($baseController->forbidden('You do not have sufficient permissions to access this page.'));
        }
        else
        {
          getRoute()->run('/error/403', EpiRoute::httpGet);
        }
        die();
        break;
      case 'EpiDatabaseException':
      case 'EpiDatabaseConnectionException':
      case 'EpiDatabaseQueryException':
        if(isset($_GET['__route__']) && substr($_GET['__route__'], -5) == '.json')
        {
          echo json_encode($baseController->error('A database exception occured.'));
        }
        else
        {
          getRoute()->run('/error/500', EpiRoute::httpGet);
        }
        die();
        break;
      case 'OPAuthorizationOAuthException':
        echo json_encode($baseController->forbidden($exception->getMessage()));
        die();
        break;
      default:
        getLogger()->warn(sprintf('Uncaught exception (%s:%s): %s', $exception->getFile(), $exception->getLine(), $exception->getMessage()));
        $message = 'An unknown error occurred.';
        if($exception->getMessage() != '')
          $message = $exception->getMessage();

        // != '' && != 0
        $result = null;
        if($exception->getCode() != '')
          $result['code'] = $exception->getCode();

        if(isset($_GET['__route__']) && substr($_GET['__route__'], -5) == '.json')
          echo json_encode($baseController->error($exception->getMessage(), $result));
        else
          getRoute()->run('/error/500', EpiRoute::httpGet);
        break;
    }
  }
}
class OPAuthorizationException extends OPException{}
class OPAuthorizationOAuthException extends OPAuthorizationException{}
class OPAuthorizationSessionException extends OPAuthorizationException{}
class OPAuthorizationPermissionException extends OPAuthorizationException{}
class OPInvalidImageException extends OPException{}
class OPInvalidMapException extends OPException{}

function op_exception_handler($exception)
{
  static $handled;
  $baseController = new BaseController;
  if(!$handled)
  {
    OPException::raise($exception);
    $handled = 1;
  }
}
set_exception_handler('op_exception_handler');
