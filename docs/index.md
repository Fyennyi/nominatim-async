# Nominatim Async PHP

An asynchronous PHP client for the [Nominatim](https://nominatim.org/) API (OpenStreetMap), built on top of Guzzle Promises.

## Overview

`fyennyi/nominatim-async` allows you to perform forward and reverse geocoding, address lookups, and more, without blocking your application's execution flow. It is highly efficient and designed for modern asynchronous PHP applications.

## Key Features

- **True Async**: Utilizes Guzzle Promises for non-blocking HTTP requests.
- **Rich Models**: Returns structured `Place` and `Address` objects instead of raw arrays.
- **Built-in Caching**: Integrated with `async-cache-php` for seamless, non-blocking caching.
- **Rate Limiting**: Automatically handles Nominatim's usage policy requirements.
- **Fluent API**: Easy to use and read.
