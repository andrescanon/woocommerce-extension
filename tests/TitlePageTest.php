<?php declare(strict_types=1);
# Based on:
# https://github.com/lmc-eu/steward-example
namespace My\Tests;

use Facebook\WebDriver\WebDriverElement;

class TitlePageTest extends AbstractTestCase
{
    /**
     * @before
     */
    public function init()
    {
        $this->wd->get(self::$baseUrl);
    }

    public function testShouldContainMainElements()
    {
        // Check title contents
        $this->assertContains('Demo Site For STACC', $this->wd->getTitle());
    }
}