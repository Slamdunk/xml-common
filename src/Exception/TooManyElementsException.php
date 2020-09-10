<?php

declare(strict_types=1);

namespace SimpleSAML\XML\Exception;

/**
 * This exception may be raised when the passed DOMElement contains too much child-elements of a certain type
 *
 * @package simplesamlphp/saml2
 */
class TooManyElementsException extends SchemaViolationException
{
}
