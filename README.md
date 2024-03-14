### IMGW README

---

**Quick Start**

1. **Clone the Repository**:
   Download the repository to your local machine using the following command:
   ```
   git clone https://github.com/dundermave/imgw
   ```

2. **Navigate to the Directory**:
   Move into the project directory:
   ```
   cd <project_folder>
   ```

3. **Run Docker Compose**:
   Use the following command to start the application with Docker Compose:
   ```
   docker compose -f docker-compose.yml up
   ```

   This command will build the necessary Docker images and containers. Once completed, the application will be accessible at `localhost:8080`, and PHPMyAdmin can be accessed at `localhost:8081`.

---

##Installation using commands
   To run internal commands inside the container, type:
   ```
   docker exec -it imgw-mydrupal-1 bash
   ```

   Install Drush using the following command:
   ```
   composer require drush/drush && composer install
   ```

   Execute the Drupal automatic installation:
   ```
   echo 'yes' | drush site-install standard --db-url=mysql://user:userpassword@mymariadb:3306/imgw_db --account-name=admin --account-pass=admin --site-name="My IMGW website"
   ```

   Activate the IMGW module and rebuild the cache for the application:
   ```
   drush en imgw -y && drush cr
   ```

##Automatic Installation
1. Navigate through the Drupal automatic installation, providing the following database details:
  - Database name: `imgw_db`
  - Database username: `user`
  - Database password: `userpassword`
  - Set the host to `mymariadb`.


2. **Complete Configuration**.


3. **Enable IMGW Api Module**:
   Navigate to extensions and activate the IMGW Api module.



---
