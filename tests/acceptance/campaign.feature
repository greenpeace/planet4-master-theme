Feature: campaign basics
  In order to manage campaigns
  I need to be able to create a campaign

Background:
  Given I am logged in as administrator
  And I open the dashboard

  Scenario: Create a new campaign
    Given I am on a new campaign page
    When I add a title "Test campaign title"
    And I add a paragraph "Test campaign paragraph"
    And I publish the campaign
    Then I see a validation message "Page published."
    And the campaign is visible on the website with title "Test campaign title" and paragraph "Test campaign paragraph"

  Scenario: Import a campaign
    Given I am on a import campaign page
    When I select xml file to import "test-campaign.lorem-ipsum.xml"
    And I upload file and import
    And I select the import of attachments
    And I submit the import form
    Then I see a successful import message
    And the campaign is imported in DB as draft
