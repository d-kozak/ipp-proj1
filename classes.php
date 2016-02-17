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
    public function __construct($left_state, $character, $right_state)
    {
        $this->left_state = $left_state;
        $this->character = $character;
        $this->right_state = $right_state;
    }

    public function check_rule(FI $FI)
    {
        $states = $FI->getStates();

        if (!in_array($this->left_state, $states)) {
            print_error_line("Levy stav " . $this->left_state . " pravidla neni v mnozine stavu");
            exit(41);

        } else if (!in_array($this->right_state, $states)) {
            print_error_line("Pravy stav " . $this->right_state . " pravidla neni v mnozine stavu");
            exit(41);
        } else if (!in_array($this->character, $FI->getAlphabet())) {
            print_error_line("Znak pravidla " . $this->character . " neni v abecede");
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
        $this->alphabet = $alphabet;
        $this->rules = $rules;
        $this->startState = is_array($startState) ? $startState[0] : $startState;
        $this->finishStates = $finishStates;
    }

    public function check_fi()
    {
        global $debug;

        if($debug)
            print_info_line("Started semantic analysis");

        if (!in_array($this->startState, $this->states)) {
            print_error_line("Start state " . $this->startState . " not found in states set");
            return false;
        }

        foreach ($this->finishStates as $f_state) {
            if (!in_array($f_state, $this->states)) {
                print_error_line("Finish state " . $f_state . " not found in states set");
                return false;
            }
        }

        if(!$this->check_states())
            return false;

        if($debug)
            print_info_line("Finished semantic analysis");

        return true;
    }

    private function check_states(){
        foreach($this->rules as $rule){
            if(!$rule->check_rule($this))
                return false;
        }
        return true;
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