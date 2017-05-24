# chatapp
A simple PHP based Chat Application prototype.

## Installation
This project uses composer, so for installing dependencies just type to the terminal `php composer.phar install` in the project directory.

## Database
The project has its database in *db* folder. Database is called chatapp.db. Three users are already inserted in the database. You may also register your own users in *signup* page.

All chats are held as rooms in the database in the table *rooms*. Every room can have any amount of people. A mapping table *members* stores many to many relationship between users and rooms. This mapping table also holds user level in `status` column. `1` is used for room admins, `0` is for regular members and `-1` is used for former members.

All messages are held in *messages* table, with sender's `user_id` and `room_id` of the room that the message is sent.

All users are held in *users* table. Users' passwords are hashed by PHP's built-in `password_hash` function.

## Structure
This project uses Slim Framework for handling HTTP requests, a basic sqlite3 for database and twig and javascript for the frontend. For database operations, *Eloquent* is used.

As I have no prior PHP experience before, it was challenging for me to decide how to start coding. I decided to search for tutorials on PHP web applications and I followed [this course](https://www.codecourse.com/lessons/slim-3-authentication) to setup a startup configuration and handle user registration and login systems.

Thanks,
