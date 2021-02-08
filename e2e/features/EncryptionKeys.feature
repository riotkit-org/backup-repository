@bahub @security
Feature: Bahub should allow me to generate GPG keys and store them, so I can leave this thing and do not care

    Scenario: As a backup manager I would like to generate keys by just running a Bahub task
              So I could later just make backups without giving any credentials or keys

        Given I generate keys for existing backup configuration entry "fs"
        Then I should have gpg key described as "Mikhail Bakunin"


    Scenario: As a backup manager I can generate a key for given backup definition only ONCE
              I should be notified, that I cannot generate more keys when I try second or any next time

        Given I generate keys for existing backup configuration entry "fs"
        And I generate keys for existing backup configuration entry "fs"
        Then I should see error output from bahub containing "already created, skipping creation"
