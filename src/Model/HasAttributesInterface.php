<?php
namespace Mongolid\Model;

/**
 * This interface declares attribute getter, setters and also a useful
 * `fill` method.
 *
 * It is supposed to be used in conjunction with Attributes trait
 *
 * @see HasAttributesTrait
 */
interface HasAttributesInterface
{
    /**
     * Set the model attributes using an array.
     * Notice: Even though the $object (if passed) hold the changes for simple cases,
     * you should always prefer to relay on the returned object, as it might change
     * when using polymorphable models.
     *
     * @param array                       $input  the data that will be used to fill the attributes
     * @param HasAttributesInterface|null $object if provided, $input will be merged with object attributes
     * @param bool                        $force  force fill (ignore fillable fields)
     *
     * @see PolymorphableModelInterface
     */
    public static function fill(array $input, HasAttributesInterface $object = null, bool $force = false): HasAttributesInterface;

    /**
     * Check if an attribute is set on the model.
     *
     * @param string $key the attribute to be checked
     */
    public function hasDocumentAttribute(string $key): bool;

    /**
     * Get an attribute from the model.
     *
     * @param string $key the attribute to be accessed
     *
     * @return mixed
     */
    public function getDocumentAttribute(string $key);

    /**
     * Get all attributes from the model.
     */
    public function getDocumentAttributes(): array;

    /**
     * Unset a given attribute on the model.
     *
     * @param string $key name of the attribute to be unset
     */
    public function cleanDocumentAttribute(string $key);

    /**
     * Set a given attribute on the model.
     *
     * @param string $key   name of the attribute to be set
     * @param mixed  $value value to be set
     */
    public function setDocumentAttribute(string $key, $value);

    /**
     * Stores original attributes from actual data from attributes
     * to be used in future comparisons about changes.
     *
     * Ideally should be called once right after retrieving data from
     * the database.
     */
    public function syncOriginalDocumentAttributes();

    /**
     * Retrieve original attributes.
     */
    public function getOriginalDocumentAttributes(): array;
}
