# Database Setup Scripts for OfferCode API

This folder contains scripts to set up and manage the MySQL database for the OfferCode API. Below you'll find instructions on how to use these scripts.

## Requirements

- **MySQL**: Version 5.7 or later (MariaDB is also supported).
- **PHP**: To run the `addkey.php` script.
- **Bash** or any command line environment that can execute bash scripts.

## Setting Up the Database

### 1. Create the Database

First, you need to edit the `setup_database.sql` file and change the name for your database on the first two lines:

```bash
nano setup_database.sql
```

### 2. Update setup script with your settings

Update the `setup.sh` script with your settings.

`nano setup.sh`

```bash
#!/bin/bash

DB_USER="your_username"
DB_PASS="your_password"
DB_NAME="offercode_db"
SQL_FILE="setup_database.sql"

mysql -u $DB_USER -p$DB_PASS $DB_NAME < $SQL_FILE

echo "Database setup completed."

```

### 3. Run Setup

Now you can run a simple Bash script to do all the setup.

```bash
# Make the script executable
chmod +x setup.sh

# Run the script
./setup.sh
```

## Adding API Keys

The script assumes you have `dbconfig.php` correctly configured with your database connection details.

`php addkey.php example_user`
