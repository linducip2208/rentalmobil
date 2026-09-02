<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a vehicle cannot be booked for the requested period.
 * Controllers map this to HTTP 409 with a customer-friendly message.
 */
class VehicleUnavailableException extends RuntimeException {}
