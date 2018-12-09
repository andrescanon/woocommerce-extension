<?php

namespace My\Tests;


use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;

class RecommenderBoxTest extends AbstractTestCase
{
    //SWITCH TO TRUE IF WE HAVE A GOOD CONNECTION TO THE REAL API
    private $connectionToRealAPIWorks = false;

    /**
     * @before
     */
    public function init()
    {
        $this->wd->get(self::$baseUrl);
    }

    public function testRecommenderBox()
    {
        //Acts like the old TitlePageTest when API is not available.

        //Check that we are on the correct website
        $this->assertContains('Demo Site For STACC', $this->wd->getTitle());

        if ($this->connectionToRealAPIWorks) {
            //Check if the Box is present
            $this->assertTrue($this->ifElementExists('//section[contains(@class, "related products")]'));

            //Check if the box has products
            $this->assertTrue($this->ifElementExists('//section[contains(@class, "related products")]//h2[contains(@class, "product")]'));
        }
    }
}
