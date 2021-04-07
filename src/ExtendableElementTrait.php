<?php

declare(strict_types=1);

namespace SimpleSAML\XML;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Constants;

/**
 * Trait grouping common functionality for elements implementing the xs:any element.
 *
 * @package simplesamlphp/xml-common
 */
trait ExtendableElementTrait
{
    /** @var \SimpleSAML\XML\XMLElementInterface[] */
    protected array $elements = [];

    /** @var string|array */
    protected $namespace = Constants::XS_ANY_NS_ANY;


    /**
     * Set an array with all elements present.
     *
     * @param array \SimpleSAML\XML\XMLElementInterface[]
     */
    public function setElements(array $elements): void
    {
        Assert::allIsInstanceOf($elements, XMLElementInterface::class);

        // Get namespaces for all elements
        $actual_namespaces = array_map(
            function($elt) {
                return $elt->getNamespaceURI();
            },
            $elements
        );

        if ($this->namespace === Constants::XS_ANY_NS_LOCAL) {
            // If ##local then all namespaces must be null
            Assert::allNull($actual_namespaces);
        } elseif (is_array($this->namespace)) {
            // Make a local copy of the property that we can edit
            $allowed_namespaces = $this->namespace;

            // Replace the ##targetedNamespace with the actual namespace
            if (($key = array_search(Constants::XS_ANY_NS_TARGET, $allowed_namespaces)) !== false) {
                $allowed_namespaces[$key] = static::NS;
            }

            // Replace the ##local with null
            if (($key = array_search(Constants::XS_ANY_NS_LOCAL, $allowed_namespaces)) !== false) {
                $allowed_namespaces[$key] = null;
            }

            $diff = array_diff($actual_namespaces, $allowed_namespaces);
            Assert::isEmpty(
                $diff,
                sprintf(
                    'Elements from namespaces [ %s ] are not allowed inside a %s element.',
                    rtrim(implode(', ', $diff)),
                    static::NS
                )
            );
        } else {
            // All elements must be namespaced, ergo non-null
            Assert::allNotNull($actual_namespaces);

            if ($this->namespace === Constants::XS_ANY_NS_OTHER) {
                // Must be any namespace other than the parent element
                Assert::allNotSame($actual_namespaces, static::NS);
            } elseif ($this->namespace === Constants::XS_ANY_NS_TARGET) {
                // Must be the same namespace as the one of the parent element
                Assert::allSame($actual_namespaces, static::NS);
            }
        }

        $this->elements = $elements;
    }


    /**
     * Get an array with all elements present.
     *
     * @return \SimpleSAML\XML\XMLElementInterface[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }


    /**
     * Set the value of the namespace-property
     *
     * @param string|array $namespace
     */
    public function setNamespace($namespace): void
    {
        Assert::true(is_array($namespace) || is_string($namespace));

        if (!is_array($namespace)) {
            // Must be one of the predefined values
            Assert::oneOf($namespace, Constants::XS_ANY_NS);
        } else {
            // Array must be non-empty and cannot contain ##any or ##other
            Assert::notEmpty($namespace);
            Assert::allNotSame($namespace, Constants::XS_ANY_NS_ANY);
            Assert::allNotSame($namespace, Constants::XS_ANY_NS_OTHER);
        }

        $this->namespace = $namespace;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        if (empty($this->elements)) {
            return true;
        }

        $empty = true;
        foreach ($this->elements as $elt) {
            $empty = $empty && $elt->isEmptyElement();
        }

        return $empty;
    }
}
