# Money Tracker
## Scheduled tasks Setup

This is most likely an item to add to your production server, but is also potentially something you'll want running in the background for your dev environment.
To set this up you will need to add the following Cron entry to your server.
```bash
* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
```
This Cron will call the Laravel command scheduler every minute. When the command `schedule:run` is executed, Laravel will evaluate your scheduled tasks and runs the tasks that are due.

For a full list of commands that will be run/scheduled as part of this setup, run this command:
```bash
php artisan schedule:list
```
