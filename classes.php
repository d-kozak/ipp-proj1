<?php

/**
 * Created by PhpStorm.
 * User: david
 * Date: 15.2.16
 * Time: 11:12
 */
class Rule
{
    private $left_state;
    private $character;
    private $right_state;

    /**
     * Rule constructor.
     * @param $left_state
     * @param $character
     * @param $right_state
     */
    public function __construct($left_state, $character, $right_state, FI $FI)
    {
        $this->left_state = $left_state;
        $this->character = $character;
        $this->right_state = $right_state;

        $this->check_rule($FI);
    }

    private function check_rule(FI $FI)
    {
        print_error_line("Checking rule not implementted yet");
        $states = $FI->getStates();

        if (!in_array($this->left_state, $states)) {
            print_error_line("Levy stav ". $this->left_state ." pravidla neni v mnozine stavu");
            exit(41);

        } else if (!in_array($this->right_state, $states)) {
            print_error_line("Pravy stav ". $this->right_state . " pravidla neni v mnozine stavu");
            exit(41);
        } else if (!in_array($this->character, $FI->getAlphabet())){
            print_error_line("Znak pravidla ". $this->character ." neni v abecede");
            exit(41);
        }
    }

    /**
     * @return mixed
     */
    public function getLeftState()
    {
        return $this->left_state;
    }

    /**
     * @return mixed
     */
    public function getCharacter()
    {
        return $this->character;
    }

    /**
     * @return mixed
     */
    public function getRightState()
    {
        return $this->right_state;
    }


}

class FI
{
    private $states;
    private $alphabet;
    private $rules;
    private $startState;
    private $finishStates;

    /**
     * FI constructor.
     * @param $states
     * @param $alphabet
     * @param $rules
     * @param $startState
     * @param $finishStates
     */
    public function __construct($states, $alphabet, $rules, $startState, $finishStates)
    {
        $this->states = $states;
        $this->alphabet = $this->prepare_alphabet($alphabet);
        $this->rules = $this->parse_rules($rules);
        $this->startState = is_array($startState) ? $startState[0] : $startState;
        $this->finishStates = $finishStates;

        $this->check_fi();
    }

    private function prepare_alphabet($alphabet){
        $result = $this->remove_quotes_from_alphabet($alphabet);
        $result[] = 'E'; // temporary epsilon
        return $result;
    }

    private function remove_quotes_from_alphabet($alphabet){
        $result = array();
        foreach($alphabet as $character){
            $result[] = substr($character,1,-1);
        }
        return $result;
    }

    private function check_fi()
    {
        print_error_line("CHecking fi not implemented yet");

        if(!in_array($this->startState,$this->states)){
            print_error_line("Start state " .$this->startState . " not found in states set");
            exit(41);
        }

        foreach($this->finishStates as $f_state){
            if(!in_array($f_state,$this->states)){
                print_error_line("Finish state " .$this->startState . " not found in states set");
                exit(41);
            }
        }
    }

    private function parse_rules($rules)
    {
        $result = array();
        foreach ($rules as $rule) {
            $after_split = split("->", $rule);
            $result[] = new Rule($after_split[0][0],$after_split[0][2], $after_split[1], $this);
        }
        return $result;
    }

    /**
     * @return mixed
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * @return mixed
     */
    public function getAlphabet()
    {
        return $this->alphabet;
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @return mixed
     */
    public function getStartState()
    {
        return $this->startState;
    }

    /**
     * @return mixed
     */
    public function getFinishStates()
    {
        return $this->finishStates;
    }


}