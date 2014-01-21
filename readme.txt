Framework for PHP website development by Kristijan Burnik

Base
- Event triggering and listening mechanisms for objects inherting the Base class

Built-in templating language
- express views with short and simple looping and branching

TestCase & TestModule
- test your app with unit tests and each-class-method based tests

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

Pagination
- simple mechanism for paginating over model results

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




