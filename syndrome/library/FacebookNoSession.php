<?php
/**
 * This is Facebook.php but it doesn't use a session, this is for use primarily in crons
 */
require_once "BaseFacebook.php";

class FacebookNoSession extends BaseFacebook
{

  /**
   * Identical to the parent constructor, except that
   * we start a PHP session to store the user ID and
   * access token if during the course of execution
   * we discover them.
   *
   * @param Array $config the application configuration.
   * @param bool $use_sesion
   * @see BaseFacebook::__construct in facebook.php
   */
  public function __construct($config, $use_session = true) {
    parent::__construct($config);
  }

  protected static $kSupportedKeys =
    array('state', 'code', 'access_token', 'user_id');

  /**
   * Provides the implementations of the inherited abstract
   * methods.  The implementation uses PHP sessions to maintain
   * a store for authorization codes, user ids, CSRF states, and
   * access tokens.
   */
  protected function setPersistentData($key, $value) {
    if (!in_array($key, self::$kSupportedKeys)) {
      self::errorLog('Unsupported key passed to setPersistentData.');
      return;
    }

  }

  protected function getPersistentData($key, $default = false) {
    if (!in_array($key, self::$kSupportedKeys)) {
      self::errorLog('Unsupported key passed to getPersistentData.');
      return $default;
    }
    return false;
  }

  protected function clearPersistentData($key) {
    if (!in_array($key, self::$kSupportedKeys)) {
      self::errorLog('Unsupported key passed to clearPersistentData.');
      return;
    }
  }

  protected function clearAllPersistentData() {
  }

  protected function constructSessionVariableName($key) {
    return implode('_', array('fb',
                              $this->getAppId(),
                              $key));
  }
}