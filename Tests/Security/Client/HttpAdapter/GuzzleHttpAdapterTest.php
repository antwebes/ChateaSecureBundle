<?php
/**
 * User: José Ramón Fernandez Leis
 * Email: jdeveloper.inxenio@gmail.com
 * Date: 2/10/15
 * Time: 9:23
 */

namespace Ant\Bundle\ChateaSecureBundle\Tests\Security\Client\HttpAdapter;


use Ant\Bundle\ChateaSecureBundle\Client\HttpAdapter\GuzzleHttpAdapter;

class GuzzleHttpAdapterTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $guzzleHttpAdapter;

    protected function setUp()
    {
        parent::setUp();

        $this->client = $this->getMock('Guzzle\Service\ClientInterface');
        $this->guzzleHttpAdapter = new GuzzleHttpAdapter('http://api.local', 'an_id', 'a_secret', $this->client);
    }

    public function testWithUserCredentials()
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->any())
            ->method('getClientIP')
            ->will($this->returnValue('8.8.8.8'));

        $command = $this->getMock('Guzzle\Service\Command\CommandInterface');

        $command->expects($this->once())
            ->method('execute');

        $this->client->expects($this->once())
            ->method('getCommand')
            ->with('withUserCredentials', array('client_id' => 'an_id', 'username' => 'AUSER', 'password' => 'APASSWORD', 'userIP' => '8.8.8.8', 'client_secret' => 'a_secret'))
            ->will($this->returnValue($command));

        $this->guzzleHttpAdapter->setRequest($request);
        $this->guzzleHttpAdapter->withUserCredentials('AUSER', 'APASSWORD');
    }



    public function testWithUserCredentialsWithNoRequest()
    {
        $command = $this->getMock('Guzzle\Service\Command\CommandInterface');

        $command->expects($this->once())
            ->method('execute');

        $this->client->expects($this->once())
            ->method('getCommand')
            ->with('withUserCredentials', array('client_id' => 'an_id', 'username' => 'AUSER', 'password' => 'APASSWORD', 'client_secret' => 'a_secret'))
            ->will($this->returnValue($command));

        $this->guzzleHttpAdapter->withUserCredentials('AUSER', 'APASSWORD');
    }
}