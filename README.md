# Laravel Project with Docker and Swagger Integration

This project is a Laravel application setup with Docker and integrated Swagger (OpenAPI) documentation.

## Table of Contents
- [Project Overview](#project-overview)
- [Technologies Used](#technologies-used)
- [Installation](#installation)
- [Running the Application](#running-the-application)
- [Swagger API Documentation](#swagger-api-documentation)
- [Docker Commands](#docker-commands)
- [Environment Configuration](#environment-configuration)

## Project Overview
This is a Laravel application with Docker containers to help you run the app in an isolated, consistent environment. It also includes Swagger integration for API documentation, which can be accessed within the application.

## Technologies Used
- **Laravel**: A PHP framework for building web applications.
- **Docker**: Containerization tool for creating isolated environments.
- **Swagger/OpenAPI**: For generating API documentation that can be accessed via a web interface.
- **PostgreSQL**: Database for storing application data.
- **Redis**: Used for caching and session management (if required).

## Installation

Follow the steps below to set up this project locally.

### 1. Clone the repository:
```bash
git clone https://github.com/satrijo/whatsapp-clone.git
cd whatsapp-clone
```

### 2. Build and start the Docker containers:
The project comes with a `docker-compose.yml` file for setting up Docker containers.

```bash
docker-compose up --build -d
```

This command will:
- Build and start the Laravel application container
- Set up the PostgreSQL database container
- Set up the Redis container (if configured)

### 3. Set up the environment variables:
Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Configure your `.env` file as needed (database credentials, etc.).

### 4. Run migrations:
Once the containers are running, run the Laravel migrations to set up the database:

```bash
docker-compose exec app php artisan migrate
```

### 5. Install Composer dependencies:
Make sure all Laravel dependencies are installed by running:

```bash
docker-compose exec app composer install
```

## Running the Application

### 1. Access the application:
Once the Docker containers are running, you can access the Laravel application at:
```
http://localhost
```

### 2. Access the Swagger Documentation:
Swagger API documentation will be available at the following URL:
```
http://localhost/docs
```
This will allow you to interact with the API through a web interface and test endpoints directly.

## Swagger API Documentation

Swagger is integrated into the project using the [Swagger Laravel package](https://github.com/DarkaOnLine/L5-Swagger). The API documentation is automatically generated from the annotations in the controller methods.

### How to add new Swagger annotations:
1. **Define the route in your controller** (e.g., `ChatroomController`).
2. **Use OpenAPI annotations** like `@OA\Get`, `@OA\Post`, etc., to document the API route. Example:
   ```php
   /**
    * @OA\Post(
    *     path="/api/chatrooms",
    *     summary="Create a new chatroom",
    *     description="Creates a new chatroom in the application.",
    *     tags={"Chatrooms"},
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             @OA\Property(property="name", type="string", example="General Chat"),
    *             @OA\Property(property="max_members", type="integer", example=100)
    *         )
    *     ),
    *     @OA\Response(
    *         response=201,
    *         description="Chatroom created",
    *         @OA\JsonContent(
    *             @OA\Property(property="id", type="integer", example=1),
    *             @OA\Property(property="name", type="string", example="General Chat")
    *         )
    *     )
    * )
    */
   ```

### Regenerate Swagger Documentation:
To regenerate the Swagger documentation after adding or changing annotations, run the following command:

```bash
docker-compose exec app php artisan l5-swagger:generate
```

This will regenerate the `swagger.json` file which is used by Swagger UI to render the documentation.

## Docker Commands

Here are some useful Docker commands:

- **Start the application**:
  ```bash
  docker-compose up -d
  ```

- **Stop the application**:
  ```bash
  docker-compose down
  ```

- **Access the Laravel application container**:
  ```bash
  docker-compose exec app bash
  ```

- **View logs**:
  ```bash
  docker-compose logs -f
  ```

### WebSocket Integration

Real-time updates for chat messages are provided via WebSocket. To listen to chat messages, subscribe to the `PresenceChannel` in the format `chat.{chatroom_id}`. The event `MessageSent` broadcasts any new messages in this channel.

#### Example:

```javascript
Echo.join(`chat.${chatroomId}`)
    .listen('MessageSent', (e) => {
        console.log(e.message);
    });

Echo.private(`chatroom.${chatroomId}`)
    .listen('UserEnteredChatroom', (e) => {
        console.log(`${e.user.name} has entered the chatroom!`);
    });
```
## Environment Configuration

You may need to update your `.env` file based on the Docker container environment. By default, the application uses the following environment variables for Docker:

- **APP_URL**: The URL for the Laravel application (`http://localhost` by default).
- **DB_CONNECTION**: The database connection (`pgsql` by default).
- **DB_HOST**: The database host (`db` by default, which corresponds to the PostgreSQL container).
- **DB_PORT**: The database port (default: `5432`).
- **DB_DATABASE**: The database name (`whatsapp` by default).
- **DB_USERNAME**: The PostgreSQL user (`postgres` by default).
- **DB_PASSWORD**: The password for PostgreSQL (`password` by default).

Make sure your `.env` file matches these settings for Docker to work properly.

---
