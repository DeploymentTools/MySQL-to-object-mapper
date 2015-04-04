<?php
namespace MySQLExtractor\Common;

class Collection
{
    protected $elements;

    public function __construct(array $elements = array())
    {
        $this->elements = $elements;
    }

    public function add($element)
    {
        $this->elements[] = $element;
        return true;
    }

    public function set($key, $element)
    {
        $this->elements[$key] = $element;
    }

    public function get($key)
    {
        if ($this->containsKey($key)) {
            return $this->elements[$key];
        }
    }

    public function toArray()
    {
        return $this->elements;
    }

    public function indexOf($element)
    {
        return array_search($element, $this->elements, true);
    }

    public function count()
    {
        return count($this->elements);
    }

    public function remove($key)
    {
        if (isset($this->elements[$key])) {
            $removed = $this->elements[$key];
            unset($this->elements[$key]);
            return $removed;
        }
        return null;
    }

    public function removeElement($element)
    {
        if ($key = $this->indexOf($element)) {
            unset($this->elements[$key]);
            return true;
        }

        return null;
    }

    public function containsKey($key)
    {
        return isset($this->elements[$key]);
    }

    public function contains($element)
    {
        return in_array($element, $this->elements, true);
    }
}
