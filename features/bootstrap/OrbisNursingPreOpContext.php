<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;

use Behat\Mink\Driver\Selenium2Driver;
use \SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;

class OrbisNursingPreOpContext extends PageObjectContext
{
    public function __construct(array $parameters)
    {

    }

    /**
     * @Then /^I tick the Patient ID verified and ID band applied checkbox$/
     */
    public function iTickPatientIDCheckbox()
    {
        /**
         * @var OrbisNursingPreOp $orbisNursingPreOp
         */
        $orbisNursingPreOp = $this->getPage('OrbisNursingPreOp');
        $orbisNursingPreOp->patientIdVerifiedCheckbox();

    }

    /**
     * @Given /^I select Translator present "([^"]*)"$/
     */
    public function iSelectTranslatorPresent($translator)
    {
        /**
         * @var OrbisNursingPreOp $orbisNursingPreOp
         */
        $orbisNursingPreOp = $this->getPage('OrbisNursingPreOp');
    }

    /**
     * @Given /^enter a Translator name of "([^"]*)"$/
     */
    public function enterATranslatorNameOf($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then /^I select a Special attention wristband attached of "([^"]*)"$/
     */
    public function iSelectASpecialAttentionWristbandAttachedOf($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then /^I tick the Medication history verified checkbox$/
     */
    public function iTickTheMedicationHistoryVerifiedCheckbox()
    {
        throw new PendingException();
    }

    /**
     * @Given /^I select Medical history discrepancy found "([^"]*)"$/
     */
    public function iSelectMedicalHistoryDiscrepancyFound($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then /^I enter discrepancy note of "([^"]*)"$/
     */
    public function iEnterDiscrepancyNoteOf($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Then /^I click the Add Medication button$/
     */
    public function iClickTheAddMedicationButton()
    {
        throw new PendingException();
    }
}