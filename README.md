# MUPIN website prototype

![home](https://github.com/Elanigiro/MupinDemo/blob/master/screens/home.png)

This project represents an attempt at building the Backend/Frontend infrastructure for managing the <em>Piedmontese Computer History Museum</em> (MUPIN).

The focus of the project was the Backend, hence the very dry approach to UI/UX.

The project's core is [SimpleMVC](https://github.com/simplemvc/skeleton), a PHP framework that uses [PHP-DI](https://php-di.org/) as its DI container and [Plates](https://platesphp.com/) as its template engine.
It also adopts [PSR-7](https://www.php-fig.org/psr/psr-7/) interfaces for HTTP request/response.

The web application is backed by a MariaDB SQL Database for storing the Museum's catalogue.
A database SQL dump is available inside the <em>db_scripts</em> folder.

-------------------------------
## Website features

### <strong>1. Basic Search Engine</strong>

![search results](https://github.com/Elanigiro/MupinDemo/blob/master/screens/search_results.png)

It is a simple search engine powered by FULLTEXT indices inside the DB.

The results are ordered by relevance thanks to a scoring system.

The user can either search in the whole catalogue or select one of the available categories from the dropdown menu.

### <strong>2. Advanced Search Engine</strong>

Though still very simple in the implementation, this feature enables users to perform a more refined lookup by selecting not only the category but also the field/s to scan.

### <strong>3. Login/Logout</strong>

![login](https://github.com/Elanigiro/MupinDemo/blob/master/screens/login.png)

In order to access advanced features, admins have to authenticate with email and password.

Their log in data is safely stored in a SQL DB: passwords are saved as salted BCRYPT hashes.

In case of successful authentication, the email will be stored in the SESSION array and file so that the admin is not required to log-in again.

In order to guarantee a certain level of protection from CSRF and Session Hijacking: 
   - SessID Cookie is always generated by the Backend ([use_strict_mode](https://www.php.net/manual/en/session.configuration.php#ini.session.use-strict-mode))
   - SessID Cookie is HTTP only, Same-site: Lax and with Session Lifetime
   - A Timestamp is saved in the SESSION file at login so that the Server can decide when the session is to be considered expired

Finally, before performing any admin operation, the Server verifies that the user is correctly authenticated in the current Session.

### <strong>4. Add/Delete User</strong>

Admins are allowed to un/register other admins.

### <strong>5. Add catalogue Item</strong>

![add item](https://github.com/Elanigiro/MupinDemo/blob/master/screens/add_item.png)

Admins can add items to the catalogue.

### <strong>6. Edit a single item</strong>

![edit item](https://github.com/Elanigiro/MupinDemo/blob/master/screens/edit_item.png)

Admins can click on the edit button to edit any field of the selected item, excluding the <strong>item-id</strong> which is read-only.

### <strong>7. Edit item's pictures</strong>

![edit pictures](https://github.com/Elanigiro/MupinDemo/blob/master/screens/edit_pics.png)

Admins can delete/add pictures from/to a specific item as they see fit.

Not all file formats are supported, though, and a limit of 10 pics per item is enforced.

--------------------------
## Implementation Details

### <strong>1. src/Utils</strong>

Contains all the utility classes which are basically <em>static classes</em> used for server-side validation, session management, log management, etc.

### <strong>2. src/Alias</strong>

Contains classes that behave exactly as the classes they imitate but have a different name.

This is necessary to ensure the DI container is able to select the correct dependency in corner cases.

### <strong>3. src/Model</strong>

Contains the Object representations of the DB entities.

Many of the object's property feature a Documentation comment.

This <em><strong>Annotations</strong></em> are used through <em>Reflective programming</em> to:
   - provide validation information to the Server and to the Client (Plates)
   - build a basic ORM system by annotating PKs/FKs


<em>NOTE: Virtual models are Object representations of non-existing entities.</em>

### <strong>4. src/Repository</strong>

Contains the classes that manage the Data Access Layer for CRUD operations.

### <strong>5. src/Service</strong>

Contains the classes that manage the Transactions and provide a layer of abstraction between the Repositories and the Controllers.

### <strong>6. src/Controller</strong>

Contains the classes that control the HTTP request/response exchange with the client.
Also, they validate the requests and prevent anauthorized access to the data.

### <strong>7. tests/</strong>

Finally, this folder contains the classes dedicated to the Unit-Testing of the source code.

The Unit-Testing is performed by <strong>phpunit</strong>.