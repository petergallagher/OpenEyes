@coding
  Feature: Create New Intravitreal Event
  Regression coverage of this event is approx 75%

    Scenario: Route 5:  Regression Tests
    Login and create a New Intravitreal Event
    Site 1:  Queens
    Firm 1:  Anderson Cataract
    Open/Close Left/Right Sides

      Given I am on the OpenEyes "master" homepage
      And I enter login credentials "admin" and "admin"
      And I select Site "1"
      Then I select a firm of "1"

      Then I search for hospital number "1009465"

      Then I select the Latest Event

      Then I expand the Cataract sidebar
      And I add a New Event "Intravitreal"

      Then I select Add Left Side
      Then I select Close Left Side
      And I select Close Right Side
      Then I select Add Right Side

      Then I Save the Intravitreal injection