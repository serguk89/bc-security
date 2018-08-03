<?php
/**
 * @package BC_Security
 */

namespace BlueChip\Security\Modules\Checklist;

abstract class Check
{
    /**
     * @var string Check class (either basic or advanced).
     */
    const CHECK_CLASS = '';


    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;


    /**
     * Construct the check.
     *
     * @param string $name
     * @param string $description
     */
    protected function __construct(string $name, string $description)
    {
        $this->name = $name;
        $this->description = $description;
    }


    /**
     * @return string Check unique ID. Basically name of class implementing the check.
     */
    public static function getId(): string
    {
        return static::class;
    }


    /**
     * @return string Class of check.
     */
    public static function getClass(): string
    {
        return static::CHECK_CLASS;
    }


    /**
     * @return string Check description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }


    /**
     * @return string Check name (title).
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * By default, every check makes sense.
     *
     * @return bool
     */
    public function makesSense(): bool
    {
        return true;
    }


    /**
     * Perform the check.
     *
     * @return \BlueChip\Security\Modules\Checklist\CheckResult
     */
    abstract public function run(): CheckResult;
}
