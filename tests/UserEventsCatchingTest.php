<?php declare(strict_types=1);

namespace My\Tests;

use Exception;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;

class UserEventsCatchingTest extends AbstractTestCase
{
    /**
     * @before
     */
    public function init()
    {
        $this->wd->get(self::$baseUrl);
    }

    public function testScenario()
    {
        //Check that we are on the correct website
        $this->assertContains('Demo Site For STACC', $this->wd->getTitle());

        //Check if we are on the Shop page
        $this->assertContains('Shop', $this->wd->findElement(WebDriverBy::xpath('//header[h1]/h1[contains(@class,"page-title")]'))->getText());

        $this->deleteLog();
        $this->wd->wait(10, 500)->until(WebDriverExpectedCondition::titleContains('Demo Site'));

        //Click on the first product
        $this->wd->findElement(WebDriverBy::xpath('//li[a]/a[img][contains(@class,"LoopProduct-link")]'))->click();

        //Check if we are on the product page
        $this->wd->wait(10, 500)->until(WebDriverExpectedCondition::titleContains('Demo Site'));
        $this->assertTrue($this->ifElementExists('//button[@name="add-to-cart"]'));

        //Read the log
        $log = file_get_contents(self::$baseUrl."/wp-content/debug.log");
        $this->assertContains('view', $log);

        $this->deleteLog();
        $this->wd->wait(10, 500)->until(WebDriverExpectedCondition::titleContains('Demo Site'));

        //Click on the add-to-cart button
        $this->wd->findElement(WebDriverBy::xpath('//button[@name="add-to-cart"]'))->click();

        //Check if we are on the product page
        $this->wd->wait(10, 500)->until(WebDriverExpectedCondition::titleContains('Demo Site'));
        $this->assertTrue($this->ifElementExists('//button[@name="add-to-cart"]'));

        //Read the log
        $log = file_get_contents(self::$baseUrl."/wp-content/debug.log");
        $this->assertContains('add', $log);

        //Go back onto the home page
        $this->wd->get(self::$baseUrl);

        $this->deleteLog();
        $this->wd->wait(10, 500)->until(WebDriverExpectedCondition::titleContains('Demo Site'));

        //Use the search functionality and check for event
        $search = $this->wd->findElement(WebDriverBy::xpath('//input[@id="woocommerce-product-search-field-0"]'));
        $search->click();
        $search->sendKeys("Searching");
        $this->wd->getKeyboard()->pressKey(WebDriverKeys::ENTER);
        $this->wd->wait(5);

        //Read the log
        $log = file_get_contents(self::$baseUrl."/wp-content/debug.log");
        $this->assertContains('search', $log);
    }

    private function ifElementExists($path){
        try{
            $this->wd->findElement(WebDriverBy::xpath(($path)));
            return True;
        } catch (Exception $e){
            return False;
        }
    }

    private function deleteLog(){
        //Delete debug.log
        $this->wd->navigate()->to(self::$baseUrl."/wp-content/dellog.php");
        $this->wd->navigate()->back();
    }
}