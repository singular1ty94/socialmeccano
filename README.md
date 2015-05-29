Social Mecanno - powered by Question2Answer
-----------------------------

This is the Social Meccano fork of the [Question2Answer framework][Q2A].
This version was created by  Brett Michael Orr and Samuel Hammill, for the QUT project, Social Mecanno, under a GPLv2 license.

Social Meccano adds social features to Q2A that do not currently exist in any other Q&A-style site, including Stack Exchange or its clones.

##Social Features
* Friends
* Groups
* Individual Chat
* Group Chat
* Notifications

##Requirements
* PHP 5.2 or later, with the MySQLi extension.
* MySQL 4.1 or later, MySQL 5.x for the best performance.

##Pre-Install
1. Create a database table
2. Register a database user with full administrative rights. 
3. In `qa-config.php` enter the user, password and database name.
4. Enter the same details in `qa-plugin/chat/chat-engine/lib/confiq.php`

##One-Click Install
In the vein of projects like Wordpress, there is a simple one-click install.
Simply point your browser to `http://yoursite.com/` and follow the prompts.

##Post-Installation Consideration
After you have installed the system, you can customize the site’s styles, or proceed directly to the admin center. You may want to consider doing the following things before opening the site up to the public:

* Change the Site’s name.
* Enable or disable features such as custom HTML sidebar
* Enable plugins such as Badges or Tag Cloud
* Protect your toolbox via your .htaccess file, or remove toolbox altogether


[Q2A]: http://www.question2answer.org/
