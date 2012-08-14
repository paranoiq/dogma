Dogma
========

Dogma is a collection of OO libraries sharing the same base and the same ideology - simplicity, self-explaining API, good design.
Includes Database, Datasheet, XML/HTML DOM, HTTP, Filesystem and Email tools

Most parts are currently under development and some back compatibility breaks can happen

API dcumentation: http://apigen.juzna.cz/doc/paranoiq/dogma/


Requirements
--------
 - **PHP 5.3**
 - **Nette Framework 2.0**
 - PHP extension **mailparse** and **imap** for Mail & Imap


Parts
--------
 - **common** - data types (eg. `Enum`) and utilities *(alpha)*
 - **Datasheet** - manipulation or extraction of data in spreadsheet formats (CSV, XLS, XLSX) and HTML tables *(dev)*
 - **Database** - simple extension of Nette\Database *(dev)*
 - **Dom** - extension of PHP DOM library with simplified XPath-like query language for quick data extraction from XML/HTML documents *(beta)*
 - **Graph** - some algorithm(s) for manipulating graphs - mathematics, not graphics! *(stable)*
 - **Http** - asynchronous HTTP client for multichannel parallel requests based on CURL *(stable)*
 - **Imap** - IMAP client *(dev)*
 - **Io** - object wrapper over PHP filesystem functions *(beta)*
 - **Mail** - MIME mail parser *(beta)*
 - **Model** - basic model components, Entity, Repository etc. *(dev)*
 - **Queue** - Beanstalk queue server client *(dev)*


Author:
--------
Vlasta Neubauer, https://twitter.com/paranoiq
