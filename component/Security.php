<?php

class Security {

  public static function GetRequestToken($seconds = 10800) {
    $storage = new SessionStorage('Security');
    $requestToken = sha1(microtime(true) . rand(11111, 99999));
    $storage[$requestToken] = strtotime(now()) + $seconds ;

    return $requestToken;
  }

  public static function checkRequest($requestToken) {
    $storage = new SessionStorage('Security');

    if (!isset($requestToken)) {
      throw new SecurityException("Security exception: no token provided!", 1);
    } else if (!isset($storage[$requestToken])) {
      throw new SecurityException("Security exception: invalid token provided!",
                                  2);
    }
    else if ($storage[$requestToken] < strtotime(now())) {
      unset($storage[$requestToken]);
      throw new SecurityException("Security exception: token expired!", 3);
    } else {
      // remove request token not to get clogged up
      unset( $storage[ $requestToken ] );
    }
  }

}
