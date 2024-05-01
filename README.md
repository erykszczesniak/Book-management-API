# Book Management API
Overview
This documentation outlines the REST API endpoints for managing books and authors within a Symfony application


**### composer install** This installs the necessary PHP dependencies.


# Configure the Database
Adjust the .env file for database connection details:


# MySQL Example
DATABASE_URL="mysql://username:password@localhost:3306/my_database"



# Create the Database
php bin/console doctrine:database:create

# Run Migrations
php bin/console doctrine:migrations:migrate

# Start the Symfony Server
symfony server:start


## Endpoints

### Books

* **List All Books** : `GET /books`
* **Get Single Book** : `GET /book/{id}`
* **Create Book** : `POST /book`
* **Update Book** : `PUT /book/{id}`
* **Delete Book** : `DELETE /book/{id}`

### Authors

* **List All Authors** : `GET /authors`
* **Search Authors** : `GET /authors/search?term={searchTerm}`
* **Add Author to Book** : `POST /book/{bookId}/add-author/{authorId}`
* **Remove Author from Book** : `DELETE /book/{bookId}/author/{authorId}`
* **Get Books by Author** : `GET /author/{id}/books`

## Entity Constraints

* A book can have a maximum of 3 authors.
* Books have a boolean `public` field indicating public availability.
