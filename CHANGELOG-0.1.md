CHANGELOG for 0.1.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 0.1 minor versions.

To get the diff for a specific change, go to https://github.com/antwebes/ChateaSecureBundle/commit/XXX where XXX is the change hash
To get the diff between two versions, go to https://github.com/antwebes/ChateaSecureBundle/compare/v0.1.0...v0.1.1

* 0.1.2 (2015-04-10)
 * fix error when login with empty values in form login
 	* throw exception UsernameNotFoundException so symfony catch exception and show incorrect credentials

* 0.1.3 (2015-04-22)
 * user locked cannot logued.
 	* Client/HttpAdapter/GuzzleHttpAdapter.php return $ex->getResponse()->getBody(true) to can parse and translate the error

* 0.1.4 ( 2015-05-20)
 * Include parameter "homepage_path" to redirect in loginAction when user is logged
 
* 0.1.5 ( 2015-07-27)
 * Added autologin option in querystring to login with access token
 * If a user is allready login, we simply redirect without reauthenticate
 
* 0.1.6 ( 2015-10-26)
 * With autologin, if the current logged in user has a different access token the the passed in the autologin param, the user is logged

* 0.1.7 (2016-01-??)
 * Update documentation about remember-me
 