# FileUploader js Laravel Backend solution
## Requirements
###### Tested on:
- Laravel 5.4
- Php 7.0
- PostgreSQL 12 
## Install

###### Add to /config/app.conf in Providers section to end
```
\Avglukh\Fileuploader\FileUploaderServiceProvider::class
```
###### Write to terminal in project folder:
```
php artisan migrate
php artisan storage:link
```
## Routes
```
route{{'fileuploader.index'}} with 'type' param of query

/fileuploader?type={param}
{param}
- prelaod
- upload
- resize
- rename
- remove
- asmain
- sort
```