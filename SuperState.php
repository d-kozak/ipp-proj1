<?php
#Modul pro tridu reprezentujici slozeny stav
#DKA:xkozak15

/**
 * Class SuperState reprezentuje slozeny stav v algoritmu determinizace
 * @see FI::determinize
 */
class SuperState
{
    private $states;

    /**
     * SuperState constructor.
     * @param $states - pole stavu, ze kterych se ma nove vytvareny superstav skladat
     */
    public function __construct($states)
    {
        if(!is_array($states)){
            print_error_line("Internal error, states passed to SuperState constructor should be an array");
            $states = array($states);
        }
        sort($states);
        $this->states = $states;
    }

    /**
     * funkce vrati retezcovou reprezentaci slozeneho stavu
     * @return string
     */
    public function get_super_state_id(){
        $result = "";
        foreach($this->states as $state){
            $result .= $state."_";
        }
        return substr($result,0,-1);
    }

    /**
     * funkce vrati retezcovou reprezentaci slozeneho stavu
     * @return string
     */
    function __toString()
    {
        $result = "";
        foreach($this->states as $state){
            $result .= $state."_";
        }
        return substr($result,0,-1);
    }

    /**
     * Fce vraci iterator nad jednotlivymi stavy
     * @return ArrayIterator
     */
    public function get_iterator(){
        return new ArrayIterator($this->states);
    }

    /**
     * Funkce vraci pole stavu, ktere slozeny stav zahrnuje
     * @return array
     */
    public function getStates()
    {
        return $this->states;
    }


}
