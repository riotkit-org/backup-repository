Notifications
#############

Bahub can be configured to send notifications when an event occur.

Supported external services
***************************

- Slack/Mattermost/Matrix Slack Bridge

Example configuration for Slack/Mattermost/Matrix Slack Bridge
**************************************************************

To configure Slack there is a webhook url needed, such url can be generated in Slack applications page.

.. code:: yaml

    notifiers:
    mattermost:
        type: slack
        url: "http://slack.com/some/slack/webhook/url"


Events
******

.. include:: ../../../server/.env.dist
   :start-after: <sphinx:notification_types>
   :end-before: </sphinx:notification_types>

