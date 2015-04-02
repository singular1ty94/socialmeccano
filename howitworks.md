#How Q2A Works

All requests go through `index.php`. There are no other pages, the header URL is parsed.

Example request for user profile.

index.php
|
|
qa-include/qa-index.php
|
|
qa-include/qa-page.php
|
| (rout to lower page)
|
qa-include/qa-pages/user.php
|
| (checks what part of the user we want)
|
qa-include/qa-pages/user-profile.php
|
| (creates $qa_content multi-d array)
v
V (bubble)
v
qa-include/qa-pages/user.php
v
V (bubble)
v
qa-include/qa-page.php      [OPPORTUNITY TO OUTPUT JSON]
|
| Choose not to output JSON, instead to output HTML
|
qa-include/app/format.php
|
| (prepare $qa_content for delivery)
|
qa-include/qa-theme-base.php                [OUTPUTS HTML]
---::qa-include/qa-theme/Carbon/theme.php   [OUTPUTS HTML]
     (generates User data etc, no chance to get $qa_content output to JSON again)