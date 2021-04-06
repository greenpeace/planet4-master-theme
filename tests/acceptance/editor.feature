Feature: editor basics
  In order to publish content
  As a logged in admin
  I need to be able to use the editor

Background:
  Given I am logged in as administrator
  And I open the dashboard

  Scenario: Create a new post
    Given I am on a new post page
    When I add a title "Test title"
    And I add a paragraph "Test paragraph"
    And I publish the post
    Then I see a validation message "Post published."
    And the post is visible on the website

  Scenario: Add a youtube video in a post
    Given I am on a new post page
    When I add a title "Test video"
    And I paste a video link "https://www.youtube.com/watch?v=dQw4w9WgXcQ"
    And I publish the post
    Then I see the video in the editor
    And I see the video in the post published
