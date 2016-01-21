![Build status](http://vps2.gridwaves.com:8111/app/rest/builds/buildType:(id:Framework_DevelopmentBuild)/statusIcon)


# Framework for PHP website development by Kristijan Burnik

Base
- Event triggering and listening mechanisms for objects inherting the Base class

Built-in templating language
- express views with short and simple looping and branching

Project
- define your project setup and make multiple projects with the same framework instance

Application
- measure the runtime of the app via single call

Console
- never use echo, print or print_r to see output again
- avoid mixing results with debugging info
- define the TESTMODE and omit PRODUCTION for outputing console text as HTTP headers

Scheduler
- easily schedule tasks to run in background or via cron job

Component
- Pagination - simple mechanism for paginating over model results
- Session - user session handling
- LoginModel - avoid writing common user login boilerplate
- Security - CSRF checks and tokens

DataProvider
- abstraction for SQL DBs - MySQL implemented
- write queries via PHP - avoid using SQL specific queries when possible

TestCase
- test your app with unit tests and integration tests

Routing and ViewProviders
- route dynamically according to a RegEx or an exact URL sufix match
- specify ViewProviders separate from the Controller to decouple the view filenames/instances

Model/View/Controller
- BaseModel class has internal access to a queried data provider
- EntityModel class has internal access to a more abstract class - the data driver
- Controller can be bound to a view template easily and is decoupled from the view to allow for testing
- Views can be written in the built-in template language

Entity & EntityModel + DataDriver
- fully separated layers for the Model and the underlying data

Storage + Cache
- store data in memory / session / filesystem / database with the same map-like interface

Async
- run a BaseModel public function asynchronously (in a separate process)
- little help from an .sh script :-)

FileUpload
- simplified file uploading and file type restrictions

Formaters
- multiple formats for outputing: XML, JSON, CSV, ... easily extendable

XHRResponder
- for easily creating a RESTful API
- EntityModelXHRResponder for common entity model methods (find, count, insert, update, delete)


Validation
- helpful and common RegEx and validation methods

Logging
- a project wide or directory wide error listener, works asynchronously to detect and notify errors

Utility
- Shell colors
- Image processor for resizing and other common image tasks
- auxiliary useful functions in global namespace such as array_pick, produce, produceview, getext, etc.

MISC
- MySQL and InMemory mock support for models




