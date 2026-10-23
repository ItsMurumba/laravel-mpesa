<?php

namespace Itsmurumba\Mpesa\Exceptions;

use Exception;

/**
 * Thrown when the package cannot talk to Daraja, e.g. when no access token can be obtained.
 */
class MpesaException extends Exception
{
}
