<?php


namespace My\Tests;


use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;

class AdminPanelTest extends AbstractTestCase
{
    /**
     * @before
     */
    public function init()
    {
        //Go to login page
        $this->wd->get(self::$baseUrl . '/wp-login.php');
    }

    public function testAdminPanel()
    {
        //Check that we are on the correct website
        $this->assertContains('Log In', $this->wd->getTitle());

        //Insert login data and press login
        $this->wd->findElement(WebDriverBy::xpath("//form[@id = 'loginform']/p/label/input[@id = 'user_login']"))->sendKeys(getenv('ADMIN_USERNAME'));
        $this->wd->findElement(WebDriverBy::xpath("//form[@id = 'loginform']/p/label/input[@id = 'user_pass']"))->sendKeys(getenv('ADMIN_PASSWORD'));
        $this->wd->findElement(WebDriverBy::xpath("//form[@id = 'loginform']/p/input[@id = 'wp-submit']"));
        $this->wd->wait(10, 500)->until(WebDriverExpectedCondition::titleContains('Demo Site'));

        //Check if we are on the Dashboard
        $this->assertContains('Dashboard', $this->wd->getTitle());

        //Go to our Admin page
        $this->wd->findElement(WebDriverBy::xpath("//li[@id = 'toplevel_page_woocommerce']/ul/li/a[.='STACC']"))->click();
        $this->wd->wait(10, 500)->until(WebDriverExpectedCondition::titleContains('Demo Site'));

        //Check if we are on STACC Options page
        $this->assertContains('STACC Options', $this->wd->getTitle());

        //Check if Shop ID exists
        $this->assertTrue($this->ifElementExists('//form/table//th[.="Shop ID"]'));

        //Doesn't check further so we're not sending even more data towards the API.
    }
}
