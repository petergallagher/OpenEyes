@orbisPreOpAssess
Feature: Orbis Nursing pre-operative assessment

  Scenario: Route 1

    Given I am on the OpenEyes "master" homepage
    And I enter login credentials "admin" and "admin"
    And I select Site "1"
    Then I select a firm of "3"

    Then I search for hospital number "1009465"

    Then I select the Latest Event
    Then I expand the Glaucoma sidebar
    Then I add a New Event "Nursing pre-operative assessment"
    Then I ensure that the Event page "Nursing pre-operative assessment" is displayed correctly

    Then I tick the Patient ID verified and ID band applied checkbox
    And I select Translator present "Yes"
    And I select Translator present "No"
    And I select Translator present "N/A"
    And enter a Translator name of "Mr Translator"

    Then I select a Special attention wristband attached of "Allergies"
    Then I select a Special attention wristband attached of "Diabetes"
    Then I select a Special attention wristband attached of "Hypertension"
    Then I select a Special attention wristband attached of "Sickle Cell"

    Then I tick the Medication history verified checkbox
    And I select Medical history discrepancy found "Yes"
    Then I enter discrepancy note of "Not up-to-date"
    And I select Medical history discrepancy found "No"
    Then I click the Add Medication button


