# MUPIN website prototype

This project represents an attempt at building the Backend/Frontend infrastructure for managing the Piedmontese computer history museum (MUPIN).

The focus of the project was the Backend, hence the very dry approach to UI/UX.

The project's core is [SimpleMVC](https://github.com/simplemvc/skeleton), a PHP framework that uses [PHP-DI](https://php-di.org/) as its DI container and [Plates](https://platesphp.com/) as its template engine.
It also adopts [PSR-7](https://www.php-fig.org/psr/psr-7/) interfaces for HTTP request/response.

The web application is backed by a MariaDB SQL Database for storing the Museum's catalogue.
A database SQL dump is available inside the <em>db_scripts</em> folder.

-------------------------------
## Website features

### 1. Basic Search Engine

It is a simple search engine powered by FULLTEXT indices inside the DB.

The results are ordered by relevance thanks to a scoring system.

The user can either search in the whole catalogue or select one of the available categories from the dropdown menu.

### 2. Advanced Search Engine

Though still very simple in the implementation, this feature enables users to perform a more refined lookup by selecting not only the category but also the field/s to scan.




