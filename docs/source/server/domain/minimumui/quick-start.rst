Quick start in steps
====================

1. Your application needs to have a possibility to *create tokens* in **File Repository** on backend side (no one should see your administrative token).
2. For each user you need to generate a temporary token with minimal permissions (eg. upload only, with restrictions for password, mime types, tags etc.)
3. On your website you need to redirect user to the file repository upload form (MinimumUI endpoint) with specifying the "back" parameter in query string, so the user will go back on your website again and pass the uploaded file URL
4. You need to validate the URL from your user, if it comes eg. from proper domain where File Repository runs
