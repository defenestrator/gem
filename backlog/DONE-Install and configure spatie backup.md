Install the spatie/laravel-backup package from composer
- configure it only to run in Production 
- configure it to push to Digital Ocean Spaces "Privates" bucket and create a folder named "backups/".
- On the Privates bucket create directories "backups/db" and "backups/laravel"
- Do a full backup once per day, non-blocking queue run if needed
- Do a database backup every 6 hours
- retain last 48 backups of the database or for 30 days
- retain the last 30 backups of the full site or for 30 days
- Prune backups that are outside of those bounds.
- Do not cause regressions