Dogma
========

Dogma is a collection of OO libraries sharing the same base and the same ideology - simplicity, self-explaining API, good design.
Includes Database, Datasheet, XML/HTML DOM, HTTP, Filesystem and Email tools

Most parts are currently under development and some back compatibility breaks can happen


Requirements
--------
 - **PHP 5.3** - needed, some key part rely completely on 5.3 features and cannot be translated to 5.2
 - **Nette Framework 2.0** - Core, Caching, Database, Debugger, Loaders, Utils
 - PHP extension **mailparse** and **imap** for Mail


Parts
--------
 - **common** - data types (eg. `Enum`) and utilities *(beta)*
 - **Datasheet** - manipulation or extraction of data in spreadsheet formats (CSV, XLS, XLSX) and HTML tables *(dev)*
 - **Database** - simple extension of Nette\Database *(stable, will be discontinued in future!)*
 - **Dom** - extension of PHP DOM library with simplified XPath-like query language for quick data extraction from XML/HTML documents *(beta)*
 - **Graph** - some algorithm(s) for manipulating graphs - mathematics, not graphics! *(stable)*
 - **Http** - asynchronous HTTP client for multichannel parallel requests based on CURL. see `readme-http.md` *(stable)*
 - **Io** - object wrapper over PHP filesystem functions *(beta)*
 - **Mail** - IMAP client and MIME mail parser *(dev)*

 - see also **Jack** - simple client for Beanstalk queue server (https://github.com/paranoiq/Jack)

Author:
--------
Vlasta Neubauer, https://twitter.com/paranoiq
