# Laravel Coding Test for Artificially

Welcome to the first stage of our recruitment process at **Artificially**! This test is designed to assess your proficiency in **Laravel**, focusing on creating RESTful APIs, middleware, a custom Artisan command, and unit testing.

Our website: [artificially.io](https://artificially.io)

---

## Objective

You are tasked with building a **File Management API**. This API will allow users to manage files (upload, list, delete), and you must also create an Artisan command that randomly selects a file from storage and performs a simple action with it (e.g., log its details).

---

## Requirements

### 1. API Features

1. **API Endpoints**:
   - `GET /files` – Retrieve a list of all uploaded files.
   - `POST /files` – Upload a new file.
   - `GET /files/{id}` – Retrieve a single file for download/display.
   - `DELETE /files/{id}` – Delete a file.

2. **Middleware**:
   - Protect the `POST` and `DELETE` routes using a custom token check.
   - Only requests with `Authorization: Bearer artificially-token` should succeed on these endpoints.

3. **Validation**:
   - Ensure that the `POST /files` endpoint validates:
     - **file** (required, must be a valid file type).
   - Any additional metadata you collect about the file (e.g., name, description) should also be validated.

4. **Service Layer**:
   - Create a `FileService` class to handle file-related business logic (e.g., storing files, deleting files).

5. **Custom Artisan Command**:
   - A command, for example: `php artisan file:random`.
   - This command should:
     - Select a random file from your storage folder/database.
     - Perform an action (e.g., log its name and path, or print details to the console).

6. **Unit Tests**:
   - Write tests to validate:
     - Uploading a file.
     - Retrieving the list of files.
     - Middleware token protection.
     - The custom Artisan command’s functionality.

---

## Instructions

1. **Clone the Repository**  
   Clone this repository to your local environment:
   ```bash
   git@github.com:ArtificiallyLTD/Laravel-Coding-Test.git
   cd Laravel-Coding-Test
    ```
2. **Setup the Project**
    - Install the project dependencies:
      ```bash
      composer install
      ```
    - Create a new `.env` file:
      ```bash
      cp .env.example .env
      ```
    - Generate an application key:
      ```bash
      php artisan key:generate
      ```
    - Create a new SQLite database:
      ```bash
      touch database/database.sqlite
      ```
    - Run the database migrations:
      ```bash
      php artisan migrate
      ```
3. **Build the API**  
   Implement the API features outlined in the requirements section.
   - Create the necessary routes, controllers, middleware, and services.
   - Implement the custom Artisan command.
   - Implement file uploads and retrieval logic.
   - Enforce token-based authentication on the POST and DELETE routes via middleware

4. **Create Custom Artisan Command**  
   Create a custom Artisan command that selects a random file from your storage folder/database and performs an action with it.
5. **Write Unit Tests**  
   Write unit tests to validate the functionality of the API endpoints, middleware, and custom Artisan command.
6. **Submit Your Solution**  
   Once you have completed the task, push your code to a **private** repository on GitHub and invite the following users as collaborators: `contact@artificially.io`
7. **Review**  
   We will review your code and provide feedback on your submission.

## Evaluation Criteria

Your submission will be evaluated based on:
1.	**Code Quality**
- Clean, readable code with appropriate comments.
- Proper use of Laravel features (controllers, services, middleware, storage, etc.). 
2. **Functionality**
- Complete, working endpoints (upload, list, delete, retrieve).
- Middleware that correctly restricts certain endpoints.
- Random file command working as expected. 
3. **Testing**
- Comprehensive coverage of the outlined features.
- Proper handling of edge cases. 
4. **Documentation**
- Clear and concise documentation in README.md on setup, usage, and testing steps.
