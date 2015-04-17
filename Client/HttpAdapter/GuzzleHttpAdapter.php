<?php
/*
 * This file (GuzzleHttpAdapter.php) is part of the demo package.
 *
 * 2013 (c) Javier Fernández Rodríguez <jjbier@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 */
namespace Ant\Bundle\ChateaSecureBundle\Client\HttpAdapter;

use Ant\Bundle\ChateaSecureBundle\Client\HttpAdapter\Exception\ApiException;
use InvalidArgumentException;
use Guzzle\Service\ClientInterface;
use Guzzle\Service\Client;
use Guzzle\Service\Description\ServiceDescription;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\ServerErrorResponseException;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\CurlException;
/**
 * Class GuzzleHttpAdapter
 * @package Ant\Chatea\SecureBundle\Client\HttpAdapter
 */
class GuzzleHttpAdapter implements HttpAdapterInterface
{
    /**
     * @var ClientInterface
     */
    private $client;
    /**
     * @var string
     */
    private $clientId;
    /**
     * @var string
     */
    private $secret;

    /**
     * @param string $base_url The Server end point
     * @param string $clientId The public key of client
     * @param string $secret The private key of client
     * @param ClientInterface $client Client object
     * @throws InvalidArgumentException This exception is thrown if any parameter has errors
     */
    public function __construct($base_url, $clientId, $secret, ClientInterface $client = null)
    {
        if($client == null){
            $client = new Client();
        }

        if (!is_string($base_url) || 0 >= strlen($base_url)){
            throw new InvalidArgumentException("The field base_url must be a non-empty string");
        }
        if(!filter_var($base_url, FILTER_VALIDATE_URL)){
            throw new InvalidArgumentException("The field base_url in a url not valid ");
        }

        if (!is_string($clientId) || 0 >= strlen($clientId)){
            throw new InvalidArgumentException("The field clientId must be a non-empty string");
        }
        if (!is_string($secret) || 0 >= strlen($secret)){
            throw new InvalidArgumentException("The field secret must be a non-empty string");
        }
        $this->client = null === $client ? new Client() : $client;
        $this->clientId = $clientId;
        $this->secret = $secret;
        $this->client->setBaseUrl($base_url);
        $this->client->setDescription(ServiceDescription::factory(__DIR__.'./../../Resources/config/api-services.json'));
    }


    /**
     * The public key of client
     *
     * @return string The public key of client
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * The private key of client
     *
     * @return string The private key of client
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Enables the user to get service credentials as well as service credential
     * authentication settings for use on the client side of communication.
     *
     * @param string $username The client name
     *
     * @param string $password The secret credentials of user
     *
     * @return array|string Associative array with client credentials | Message with error in json format
     *
     * @throws InvalidArgumentException This exception is thrown if any parameter has errors
     *
     * @throws AuthenticationException This exception is thrown if you do not credentials or you cannot use this method
     *
     * @example Get client credentials
     *
     *      $authenticationInstande->withUserCredentials('username','password');
     *
     *  array("access_token"    => access-token-demo,
     *        "expires_in"      => 3600,
     *        "token_type"      => bearer,
     *        "scope"           => password,
     *        "refresh_token"   => refresh-token-demo
     *  );
     */
    public function withUserCredentials($username, $password)
    {
        if (!is_string($username) || 0 >= strlen($username)) {
            throw new InvalidArgumentException("username must be a non-empty string");
        }
        if (!is_string($password) || 0 >= strlen($password)) {
            throw new InvalidArgumentException("password must be a non-empty string");
        }

        $command = $this->client->getCommand('withUserCredentials',
            array('client_id'=>$this->getClientId(),'client_secret'=>$this->getSecret(),'username'=>$username,'password'=>$password)
        );

        try{
            return $command->execute();
        }catch (ServerErrorResponseException $ex){
            throw new ApiException();
        }catch (BadResponseException $ex){
            if($ex->getResponse()->getStatusCode() == 400){
                throw new AuthenticationException($ex->getResponse()->getBody(true), 400, $ex);
            }else{
                throw new ApiException();
            }
        }catch(ClientErrorResponseException $ex){
            throw new AuthenticationException($ex->getMessage(), 400, $ex);
        }catch(CurlException $ex){
            throw new AuthenticationException($ex->getMessage(), 400, $ex);
        }
    }

    /**
     * Enables the user to get service credentials as well as service credential
     * authentication settings for use on the client side of communication.
     *
     * @param $auth_code The unique client code
     *
     * @param $redirect_uri The url to redirect after you obtain client credentials
     *
     * @return array|string Associative array with client credentials | Message with error in json format
     *
     * @throws InvalidArgumentException This exception is thrown if any parameter has errors
     *
     * @throws AuthenticationException This exception is thrown if you do not credentials or you cannot use this method
     *
     * @example Get client credentials
     *
     *      $authenticationInstande->withAuthorizationCode('auth-code-demo','http://www.chateagratis.net');
     *
     *  array("access_token"    => access-token-demo,
     *        "expires_in"      => 3600,
     *        "token_type"      => bearer,
     *        "scope"           => password,
     *        "refresh_token"   => refresh-token-demo
     *  );
     */
    public function withAuthorizationCode($auth_code, $redirect_uri)
    {
        if (!is_string($auth_code) || 0 >= strlen($auth_code)) {
            throw new InvalidArgumentException("auth_code must be a non-empty string");
        }

        if (!is_string($redirect_uri) || 0 >= strlen($redirect_uri)) {
            throw new InvalidArgumentException("redirect_uri must be a non-empty string");
        }

        $command = $this->getCommand('withAuthorizationCode',
            array('client_id'=>$this->getClientId(),'client_secret'=>$this->getSecret(),'redirect_uri'=>$redirect_uri,'code'=>$auth_code)
        );

        try{
            return $command->execute();
        }catch (ServerErrorResponseException $ex){
            throw new ApiException();
        }catch (BadResponseException $ex){
            if($ex->getResponse()->getStatusCode() == 400){
                throw new AuthenticationException($ex->getMessage(), 400, $ex);
            }else{
                throw new ApiException();
            }
        }catch(ClientErrorResponseException $ex){
            throw new AuthenticationException($ex->getMessage(), 400, $ex);
        }catch(CurlException $ex){
            throw new ApiException();
        }
    }
    /**
     * Enables the apps to get service credentials as well as service credential
     * authentication settings for use on the apps side of communication.
     *
     * @return array|string Associative array with client credentials | Message with error in json format
     *
     * @throws AuthenticationException This exception is thrown if you do not credentials or you cannot use this method
     *
     * @example Get client credentials
     *
     *      $authenticationInstande->withClientCredentials();
     *
     *  array("access_token"    => access-token-demo,
     *        "expires_in"      => 3600,
     *        "token_type"      => bearer,
     *        "scope"           => password,
     *        "refresh_token"   => refresh-token-demo
     *  );
     */
    public function withClientCredentials()
    {
        $command = $this->getCommand('withClientCredentials',
            array('client_id'=>$this->getClientId(),'client_secret'=>$this->getSecret())
        );
        try{
            return $command->execute();
        }catch (ServerErrorResponseException $ex){
            throw new ApiException();
        }catch (BadResponseException $ex){
            if($ex->getResponse()->getStatusCode() == 400){
                throw new AuthenticationException($ex->getMessage(), 400, $ex);
            }else{
                throw new ApiException();
            }
        }catch(ClientErrorResponseException $ex){
            throw new AuthenticationException($ex->getMessage(), 400, $ex);
        }catch(CurlException $ex){
            throw new ApiException();
        }
    }

    /**
     *  After the client has been authorized for access, they can use a refresh token to get a new access token.
     *
     * @param string $refresh_token The client refresh token that you obtain in first request of credentials.
     *
     * @return array|string Associative array with client credentials | Message with error in json format
     *
     * @throws InvalidArgumentException This exception is thrown if any parameter has errors
     *
     * @throws AuthenticationException This exception is thrown if you do not credentials or you cannot use this method
     *
     * @example Get client credentials
     *
     *      $authenticationInstande->withRefreshToken('refresh-token-demo');
     *
     *  array("access_token"    => access-token-demo,
     *        "expires_in"      => 3600,
     *        "token_type"      => bearer,
     *        "scope"           => password,
     *        "refresh_token"   => refresh-token-demo
     *  );
     */
    public function withRefreshToken($refresh_token)
    {
        if (!is_string($refresh_token) || 0 >= strlen($refresh_token)) {
            throw new InvalidArgumentException("refresh_token must be a non-empty string");
        }

        $command = $this->getCommand('withRefreshToken',
            array('client_id'=>$this->getClientId(),'client_secret'=>$this->getSecret(),'refresh_token'=>$refresh_token)
        );

        try{
            return $command->execute();
        }catch (ServerErrorResponseException $ex){
            throw new ApiException();
        }catch (BadResponseException $ex){
            if($ex->getResponse()->getStatusCode() == 400){
                throw new AuthenticationException($ex->getMessage(), 400, $ex);
            }else{
                throw new ApiException();
            }
        }catch(ClientErrorResponseException $ex){
            throw new AuthenticationException($ex->getMessage(), 400, $ex);
        }catch(CurlException $ex){
            throw new ApiException();
        }
    }

    /**
     *  After the client has been authorized for access, they can use a refresh token to get a new access token.
     *
     * @param string $refresh_token The client refresh token that you obtain in first request of credentials.
     *
     * @return array|string Associative array with client credentials | Message with error in json format
     *
     * @throws InvalidArgumentException This exception is thrown if any parameter has errors
     *
     * @throws AuthenticationException This exception is thrown if you do not credentials or you cannot use this method
     *
     * @example Get client credentials
     *
     *      $authenticationInstande->withRefreshToken('refresh-token-demo');
     *
     *  array("access_token"    => access-token-demo,
     *        "expires_in"      => 3600,
     *        "token_type"      => bearer,
     *        "scope"           => password,
     *        "refresh_token"   => refresh-token-demo
     *  );
     */
    public function withFacebookId($facebook_id)
    {
        if (!is_string($facebook_id) || 0 >= strlen($facebook_id)) {
            throw new InvalidArgumentException("facebook_id must be a non-empty string");
        }

        $command = $this->getCommand('withFacebookId',
            array('client_id'=>$this->getClientId(),'client_secret'=>$this->getSecret(),'facebook_id'=>$facebook_id)
        );

        try{
            return $command->execute();
        }catch (ServerErrorResponseException $ex){
            throw new ApiException();
        }catch (BadResponseException $ex){
            if($ex->getResponse()->getStatusCode() == 400){
                throw new AuthenticationException($ex->getMessage(), 400, $ex);
            }else{
                throw new ApiException();
            }
        }catch(ClientErrorResponseException $ex){
            throw new AuthenticationException($ex->getMessage(), 400, $ex);
        }catch(CurlException $ex){
            throw new ApiException();
        }
    }

    /**
     * Disable the service credentials as well as the session.
     *
     * @param string $access_token The toke to revoke
     *
     * @return string  Message sucessfully if can revoke token | Message with error in json format
     *
     * @throws AuthenticationException This exception is thrown if you do not credentials or you cannot use this method
     *
     * @example Delete client credentials
     *
     *      $authenticationInstande->revokeToken('access-token-demo');
     *
     *      //output
     *          Access token revoked
     */
    public function revokeToken($access_token)
    {

        $command = $this->getCommand('RevokeToken',array('access_token'=>$access_token));
        $request = $command->prepare();
        $request->setHeader('Authorization','Bearer '.$access_token);
        try{

            return $request->send()->getBody(true);

        }catch (ServerErrorResponseException $ex){
            throw new ApiException();
        }catch (BadResponseException $ex){
            if($ex->getResponse()->getStatusCode() == 400){
                throw new AuthenticationException($ex->getMessage(), 400, $ex);
            }else{
                throw new ApiException();
            }
        }catch(ClientErrorResponseException $ex){
            throw new AuthenticationException($ex->getMessage(), 400, $ex);
        }catch(CurlException $ex){
            throw new ApiException();
        }
    }
    public function getCommand($name, array $args = array()){
        return $this->client->getCommand($name,$args);
    }
}