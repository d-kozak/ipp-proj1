<?php

/**
 * Created by PhpStorm.
 * User: david
 * Date: 18.2.16
 * Time: 9:05
 */
class SuperState
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


}
