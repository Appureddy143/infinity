YourStream - Mobile Streaming Site
You have successfully built a complete, dynamic streaming application using PHP and a PostgreSQL database. This guide explains how to deploy it to the internet using Neon (for the database) and Render (for the PHP code).
Project Files
Here is a summary of all the files in your project:
Core Application
index.php: The homepage.
details.php: Movie details page.
series.php: Series details page (shows seasons/episodes).
player.php: The custom video player.
profile.php: User's profile and full watch history.
search.php: Search results page.
User System
login.php: User login form.
register.php: User registration form.
logout.php: Script to log the user out.
login_process.php: Backend logic for checking credentials.
Database & History
db_connect.php: (CRITICAL) Connects to your database.
database_schema.sql: (CRITICAL) The blueprint for your entire database.
update_history.php: Backend script to save watch progress.
Admin Panel (Backend)
admin.php: The main admin dashboard (secure).
admin_add_movie.php: Backend logic to add a new movie.
admin_add_series.php: Backend logic to add a new series.
admin_edit_movie.php: Form to edit a movie.
admin_update_movie.php: Backend logic to save movie edits.
admin_edit_series.php: Form to edit a series (and its seasons/episodes).
admin_update_series.php: Backend logic to save series edits.
admin_delete.php: Backend logic to delete any content.
Deployment Files
Dockerfile: (NEW) The "recipe" for Render to build your server.
.dockerignore: (NEW) Tells Docker which files to ignore.
Deployment Instructions
Follow these 4 steps to get your site live.
Step 1: Put Your Code on GitHub
Create a free account on GitHub.
Create a new, private repository (e.g., "yourstream-app").
Upload all your project files, including the new Dockerfile and .dockerignore, into this repository.
Step 2: Set Up Your Database on Neon
Create a free account on Neon.
Create a new project.
On your project dashboard, find the Connection Details box. Keep this page open.
In the sidebar, go to the SQL Editor.
Open the database_schema.sql file from your project, copy all the text, and paste it into the Neon SQL Editor.
Click the "Run" button. This will create all your tables (users, movies, seasons, etc.).
Step 3: Deploy Your App on Render (Docker Method)
Create a free account on Render.
On your dashboard, click New + and select Web Service.
Connect your GitHub account and select the repository you created in Step 1.
Render will automatically detect your Dockerfile. It will say "Runtime: Docker". This is correct.
Give your service a name (e.g., yourstream).
Scroll down to Environment Variables. This is the most important part.
Go back to your Neon dashboard. Find the Connection String that looks like this:
postgres://[user]:[password]@[host]/[dbname]
Add the following 5 Environment Variables to Render:
DB_HOST: The [host] part (e.g., ep-example-123456.us-east-2.compute.amazonaws.com)
DB_PORT: 5432
DB_NAME: The [dbname] part
DB_USER: The [user] part
DB_PASS: The [password] part
Click the Create Web Service button. Render will now build your Dockerfile and deploy it. It might take a few minutes. When it's done, your site will be live at a URL like https://yourstream.onrender.com.
Step 4: Create Your Admin User
Your site is live, but you can't log in to the admin panel. You need to make your user an admin.