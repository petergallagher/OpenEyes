@orbisEvents
Feature: Orbis Only Event Checker

Scenario: Ensure ALL Events are loaded correctly

  Given I am on the OpenEyes "master" homepage
  And I enter login credentials "admin" and "admin"
  And I select Site "1"
  Then I select a firm of "3"

  Then I search for hospital number "1009465"

  Then I select the Latest Event
  Then I expand the Glaucoma sidebar

  And I add a New Event "Anaesthesia Patient Discharge"
  Then I ensure that the Event page "Anaesthesia Patient Discharge" is displayed correctly

  Then I expand the Glaucoma sidebar
  And I add a New Event "Anaesthesia Pre Op Assessment"
  Then I ensure that the Event page "Anaesthesia Pre Op Assessment" is displayed correctly

  Then I expand the Glaucoma sidebar
  And I add a New Event "Biometry"
  Then I ensure that the Event page "Biometry" is displayed correctly