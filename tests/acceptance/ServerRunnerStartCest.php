<?php


class ServerRunnerStartCest {

    public function _before(AcceptanceTester $I) {

    }

    public function _after(AcceptanceTester $I) {

    }

    /**
     * @env phantomjs
     *
     * @param AcceptanceTester $I
     */
    public function tryToTest(AcceptanceTester $I) {
        $I->wantTo('Test that server runner can start server. ');
        $I->amOnUrl('http://codeception.com');
        $I->click('div.home-btns a.btn-install');
        $I->seeInCurrentUrl('quickstart');
    }
}
