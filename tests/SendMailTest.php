<?php
/**
 * Class SendMailTest
 * @package App\Tests
 *
 * since: 29.05.2022
 * author: alexej@kisselev.de
 */

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SendMailTest extends WebTestCase
{
    /**
     * @test
     */
    public function email_send_correctly(): void
    {
        // setup
        //$client = static::createClient();
        //  perform action
        //$client->request('GET', '/email');

        //  make assertion
        $sentMail = self::getMailerMessage();
        //  one email sent
        self::assertEmailCount(1);
        //  send to the correct person
        self::assertEmailHeaderSame($sentMail, 'To', 'alexej@kisselev.de');
        //  has correct body content
        self::assertEmailTextBodyContains($sentMail, 'The test mail');
        //  has an attachment
        self::assertEmailAttachmentCount($sentMail,1);
    }
}