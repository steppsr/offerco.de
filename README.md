# OfferCo.de

### Introduction

OfferCo.de is a web-based service that provides short URLs for offer codes. Users can input an offer code along with a description, and the service generates a unique short code. When this short code is accessed, the original offer code and description are retrieved and displayed.

### Features

* Short Code Generation: Generate a concise and unique short code for each offer code submitted.
* Offer Code Retrieval: Retrieve the original offer code and its description using the generated short code.
* User-Friendly Interface: Easy to navigate interface for generating and retrieving offer codes.
* Secure Storage: Offer codes and descriptions are securely stored and retrieved from the database.

### Installation

1. Clone the repository to your web server.
2. Configure your database settings in dbconfig.php.
3. Ensure that your server meets the following requirements:
    * PHP 7.0 or higher
    * MySQL Database

### Usage

* Generating a Short Code:
    3.1 Navigate to the 'Get Offer Short Code' tab.
    3.2 Enter the full offer code and an optional description.
    3.3 Submit the form to receive a unique short code.

* Retrieving an Offer Code:
    3.1 Use the generated short code in the 'View Offer' tab or by accessing `https://offerco.de/[short_code]`.
    3.2 The original offer code and description will be displayed.

### API Integration

The service integrates with `https://api.dexie.space/v1/offers/` to retrieve additional details about the offer. This includes the status of the offer, dates, price, and items offered and requested.

### Files Description

* index.php: Main file handling both the generation and retrieval of offer codes.
* library.php: Contains utility functions for random string generation, user input sanitization, and base58 encoding.
* dbconfig.php: Configuration file for database settings (not included in this repository for security reasons).

### Security

* Input sanitization to prevent SQL injection and XSS attacks.
* Secure database connection handling.

### Contribution

Contributions to the project are welcome. Please follow the standard GitHub pull request process.

### License
Apache-2.0
