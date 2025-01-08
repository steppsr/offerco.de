#!/bin/bash

# Replace these with your actual credentials and database name
DB_USER="your_username"
DB_PASS="your_password"
DB_NAME="offercode_db"
SQL_FILE="setup_database.sql"

mysql -u $DB_USER -p$DB_PASS $DB_NAME < $SQL_FILE

echo "Database setup completed."