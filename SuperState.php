<?php

/**
 * Created by PhpStorm.
 * User: david
 * Date: 18.2.16
 * Time: 9:05
 */
class SuperState implements Iterator
{
    private $states;

    /**
     * SuperState constructor.
     * @param $states
     */
    public function __construct($states)
    {
        if(!is_array($states)){
            print_error_line("Internal error, states passed to SuperState constructor should be an array");
            $states = [$states];
        }
        sort($states);
        $this->states = $states;
    }

    public function get_super_state_id(){
        $result = "";
        foreach($this->states as $state){
            $result .= $state."_";
        }
        return substr($result,0,-1);
    }

    function __toString()
    {
        $result = "";
        foreach($this->states as $state){
            $result .= $state."_";
        }
        return substr($result,0,-1);
    }


    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        $var = current($this->states);
        echo "current: $var\n";
        return $var;
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $var = next($this->states);
        echo "next: $var\n";
        //return $var;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        $var = key($this->states);
        echo "key: $var\n";
        return $var;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        $key = key($this->states);
        $var = ($key !== NULL && $key !== FALSE);
        echo "valid: $var\n";
        return $var;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        echo "rewinding\n";
        reset($this->states);
    }
}
