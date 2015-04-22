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
