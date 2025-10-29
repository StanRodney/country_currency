
#  Country Currency API

A Laravel-based RESTful API that provides detailed country data including capital, region, population, currency code, exchange rate, and estimated GDP.  
It also includes endpoints for filtering, sorting, updating, and generating a visual country summary image.

---

## 📌 Hosted Links
- **Live API Base URL:** [https://country-currency-main-vzxm73.laravel.cloud](https://country-currency-main-vzxm73.laravel.cloud)
- **GitHub Repository:** [https://github.com/StanRodney/country_currency.git](https://github.com/StanRodney/country_currency.git)

---

##  Developer
**Name:** Anita Rodney-Ajayi  
**Email:** anotarodney30@gmail.com

---

## ⚙️ Features
✅ Fetch and store all countries from the [REST Countries API](https://restcountries.com/)  
✅ Integrate real-time exchange rates from the [ExchangeRate API](https://open.er-api.com/)  
✅ Compute estimated GDP based on population and exchange rate  
✅ Filter by region or currency  
✅ Sort countries by GDP (ascending or descending)  
✅ Generate a PNG summary image of top 5 countries by GDP  
✅ Retrieve API status with total countries and last refresh date

---

## 🚀 Endpoints Overview

| Method | Endpoint | Description |
|:------:|:----------|:-------------|
| **POST** | `/api/countries/refresh` | Fetch and update countries from external APIs |
| **GET** | `/api/countries` | Retrieve all countries |
| **GET** | `/api/countries?region=Africa` | Filter by region |
| **GET** | `/api/countries?currency=NGN` | Filter by currency code |
| **GET** | `/api/countries?sort=gdp_desc` | Sort by GDP descending |
| **GET** | `/api/countries/{name}` | Retrieve details of a specific country |
| **POST** | `/api/countries` | Create or update a country record |
| **DELETE** | `/api/countries/{name}` | Delete a country by name |
| **GET** | `/api/status` | Get total countries and last refresh time |
| **GET** | `/api/countries/image` | Display the generated summary image |

---

##  Technologies Used
- **Backend:** Laravel 12
- **HTTP Client:** Laravel HTTP Client (for API calls)
- **Image Generation:** Intervention Image Library
- **Database:** MySQL
- **Testing:** `.http` request collection (`test_api.http`)
- **Hosting:** Laravel Cloud

---

## 🗂️ Project Structure


country_currency_api/
├── app/
│ ├── Http/Controllers/CountryController.php
│ ├── Models/Country.php
├── database/
│ └── migrations/
│ └── 2025_10_27_141429_create_countries_table.php
├── routes/
│ └── api.php
├── storage/
│ └── app/public/cache/summary.png
├── test_api.http
└── README.md


---

## 🧩 Sample Output (Summary Image)
The API generates a summary PNG file located at:


storage/app/public/cache/summary.png

It includes:
- Total number of countries
- Timestamp of last refresh
- Top 5 countries by GDP

Example:


Country Summary Report

Total Countries: 250
Last Refreshed: 2025-10-29 12:20:14

Top 5 Countries by GDP:

United States of America — GDP: 362,110,651,177.89

China — GDP: 262,851,433,008.58

France — GDP: 142,675,209,800.11

Italy — GDP: 106,167,239,747.05

Germany — GDP: 109,290,413,409.88


---

##  Testing the API
Use the included `test_api.http` file in your IDE (PhpStorm, VS Code REST Client, etc.)  
Each request is ready for direct testing against your local or hosted environment.

Example test:


GET https://country-currency-main-vzxm73.laravel.cloud/api/countries?region=Africa&sort=gdp_desc

Accept: application/json


---

## 🛠Setup (Local)
1. Clone the repo
   ```bash
   git clone https://github.com/StanRodney/country_currency.git


Install dependencies

composer install


Configure .env with your database settings

Run migrations

php artisan migrate


Start the server

php artisan serve

Image Example

The generated summary image can be accessed via:

GET /api/countries/image


or directly viewed in:

storage/app/public/cache/summary.png

✅ Submission Summary

This project meets the following requirements:

Properly defined API routes

CRUD operations implemented

External API integration and error handling

Summary image generation using Intervention Image

Well-structured, tested, and deployed to Laravel Cloud
