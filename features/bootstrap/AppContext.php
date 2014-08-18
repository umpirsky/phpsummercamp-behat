<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Context class for working with out Silex application
 */
class AppContext implements SnippetAcceptingContext
{
    static private $app;

    private $currentUser;

    /**
     * @static
     * @BeforeSuite
     */
    static public function bootstrapSilex()
    {
        if (!self::$app) {
            self::$app = require __DIR__.'/../../app/bootstrap.php';
        }

        return self::$app;
    }

    /**
     * @Given /^the admin user exists in the database$/
     */
    public function theAdminUserExistsInTheDatabase()
    {
        $this->currentUser = self::$app['user_repository']->createAdminUser(
            'admin',
            'admin'
        );
    }

    /**
     * @Given /^there are (\d+) products$/
     */
    public function thereAreProducts($num)
    {
        for ($i = 0; $i < $num; $i++) {
            self::$app['product_repository']->createProduct(
                'Sickle-shaped Claw'.$num,
                9.99+$i
            );
        }
    }

    /**
     * @BeforeScenario
     */
    public function clearData()
    {
        self::$app['user_repository']->emptyTable();
        self::$app['product_repository']->emptyTable();
    }

    /**
     * @Given /^I author (\d+) products$/
     */
    public function iAuthorProducts($num)
    {
        for ($i = 0; $i < $num; $i++) {
            $product = self::$app['product_repository']->createProduct(
                'Sickle-shaped Claw'.$num,
                9.99+$i
            );

            $product->author = $this->currentUser;
            self::$app['product_repository']->update($product);
        }
    }

    /**
     * @Given /^the following products exist:$/
     */
    public function theFollowingProductsExist(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            $product = self::$app['product_repository']->createProduct(
                $row['name'],
                15.99
            );

            if ($row['is published'] == 'yes') {
                $product->isPublished = true;
            } else {
                $product->isPublished = false;
            }

            self::$app['product_repository']->update($product);
        }
    }
}
