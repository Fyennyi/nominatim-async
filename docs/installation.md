# Installation

The Nominatim Async Client can be installed via Composer or manually via Git.

## Requirements

- **PHP**: 8.1 or higher.
- **Guzzle**: ^7.0.
- **Async Cache**: (Automatically installed as a dependency).

## Installation

=== "Composer (Recommended)"

    Run the following command in your terminal:

    ```bash
    composer require fyennyi/nominatim-async
    ```

=== "Git / Manual"

    1. Clone the repository:
       ```bash
       git clone https://github.com/Fyennyi/nominatim-async.git
       cd nominatim-async
       ```

    2. Install dependencies:
       ```bash
       composer install
       ```

    3. Include the autoloader in your project:
       ```php
       require_once 'nominatim-async/vendor/autoload.php';
       ```