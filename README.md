# Smart Campus Services Management System (SCSMS)

**Module:** Group 5 – Campus Transport System  

This project implements a small **campus shuttle** web app in **PHP + MySQL**, with a parallel **XML pipeline** (DTD, XSD, XPath, XSLT) for learning outcomes.

## System overview

- **MySQL** stores operational trips (`transport_trips`): route, driver, departure/arrival times, capacity, available seats, and status.
- **PHP** exposes CRUD screens, search, and utilities that read/write `xml/transport.xml`.
- **XML** mirrors the relational data for validation, XPath exercises, and an **XSLT** HTML dashboard (`html/transport_report.html`).

## Folder layout

| Path | Purpose |
|------|---------|
| `database/scsms_transport.sql` | Creates `scsms_transport` and seeds sample trips |
| `php/` | Application entry points (`index.php`, CRUD, tools) |
| `xml/` | `transport.xml`, `transport.dtd`, `transport.xsd`, `transport.xsl` |
| `html/` | Generated `transport_report.html` (from XSLT) |
| `assets/css/style.css` | Shared layout and tables |
| `assets/js/app.js` | Nav toggle + delete confirmation |

## Installation (XAMPP on Windows)

1. Copy the entire `FINAL_PROJECT` folder under your web root, for example:  
   `C:\xampp\htdocs\FINAL_PROJECT`
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Import the database:
   - Open `http://localhost/phpmyadmin`
   - **Import** → choose `database/scsms_transport.sql` → Go  
   Or from a shell:  
   `mysql -u root < "D:\MAAM GRETA\FINAL_PROJECT\database\scsms_transport.sql"`
4. Default credentials (as configured in `php/config.php`):
   - Host: `localhost`
   - User: `root`
   - Password: *(empty)*
   - Database: `scsms_transport`
5. Open the app:  
   `http://localhost/FINAL_PROJECT/php/index.php`  
   (Adjust the path if your folder name or location differs.)

## XML, DTD, and XSD

- **`transport.xml`** holds `<trip>` records (route, driver, schedule, capacity, seats, status).
- **`transport.dtd`** describes the **nesting and required elements** (grammar). A parser can check that the document obeys the declared hierarchy.
- **`transport.xsd`** adds **datatypes and rules**: positive capacity, seats ≤ capacity, allowed status literals, time patterns, and **XSD 1.1 `xs:assert`** blocks (e.g. departure not after arrival).  
  **Note:** PHP’s `DOMDocument::schemaValidate()` uses **libxml**, which may not evaluate every **XSD 1.1** assertion. For full 1.1 validation, use tools such as oXygen or XMLSpy.

## XPath

`php/xpath_queries.php` runs **five XPath 1.0** expressions (available trips, capacity filter, driver filter, morning departures, full trips) and prints matching `<trip>` fragments in tables.

## XSLT

`xml/transport.xsl` transforms `transport.xml` into a **dashboard-style HTML** page (summary cards + responsive table).  

`php/generate_xml.php` exports the database to XML and immediately runs the XSLT to refresh `html/transport_report.html`.

## XQuery (self-study)

`php/xquery_trips.php` shows a **sample XQuery** that counts trips per route and reproduces the same result with **PHP + DOMXPath** (because XQuery engines are not built into PHP by default).

## Security notes

- SQL uses **prepared statements** (`mysqli`).
- HTML output uses **`htmlspecialchars`** via the `h()` helper.
- Forms validate times, numeric ranges, and allowed status values server-side.
- Delete uses **POST** and a **JavaScript `confirm()`** before submit.

## Quick workflow for demos

1. Import `scsms_transport.sql`.
2. Browse trips at `php/index.php` (add / edit / delete / search).
3. Run `php/generate_xml.php` to sync XML and the HTML report.
4. Open `php/xpath_queries.php`, `php/validate_xml.php`, and `php/xquery_trips.php`.
5. Open `html/transport_report.html` for the XSLT dashboard.

---

*Academic starter project – adjust credentials and paths for production use.*
