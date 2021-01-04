Feature: I can create an administrator account using server's shell interface,
  so I could gain access to the instance and start preparing it to my needs

Scenario: I try to create an administrator account with e-mail "unity@solidarity.local"
  Given I create admin account with e-mail "unity@solidarity.local" and "you-cant-break-it" password
  When I login as "unity@solidarity.local" with "you-cant-break-it"
  Then I expect to be logged in
