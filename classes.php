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
            return false;

        } else if (!in_array($this->right_state, $states)) {
            print_error_line("Pravy stav " . $this->right_state . " pravidla neni v mnozine stavu");
            return false;

        }
        $alphabet = $FI->getAlphabet();
        $alphabet[] = " "; // pridani epsilonu do abecedy
        if (!in_array($this->character, $alphabet)) {
            print_error_line("Znak pravidla '" . $this->character . "' neni v abecede");
            return false;
        }
        return true;
    }

    public function is_epsilon_rule()
    {
        return $this->character == " ";
    }

    function __toString()
    {
        return $this->left_state . $this->character . $this->right_state;
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

    /**
     * @param mixed $left_state
     */
    public function setLeftState($left_state)
    {
        $this->left_state = $left_state;
    }

    /**
     * @param mixed $character
     */
    public function setCharacter($character)
    {
        $this->character = $character;
    }

    /**
     * @param mixed $right_state
     */
    public function setRightState($right_state)
    {
        $this->right_state = $right_state;
    }


}

class FI
{
    private $states;
    private $alphabet;
    private $rules;
    private $startState;
    private $finishStates;

    public static function createFromRules(Array $rules,Array $endStates){
        print_var($rules);
        print_var($endStates);

        $states = array();
        $alphabet = array();
        foreach($rules as $rule) {
            $states[] = $rule->getLeftState();
            $alphabet[] = $rule->getCharacter();
        }
        $startState = $rules[0]->getLeftState();

        return new FI($states,$alphabet,$rules,$startState,$endStates);
    }

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
        global $arguments;

        if ($arguments["case_in"]) {
            foreach ($states as &$state) {
                $state = strtolower($state);
            }

            foreach ($alphabet as &$symbol) {
                $symbol = strtolower($symbol);
            }

            foreach ($rules as &$rule) {
                $rule->setLeftState(strtolower($rule->getLeftState()));
                $rule->setCharacter(strtolower($rule->getCharacter()));
                $rule->setRightState(strtolower($rule->getRightState()));
            }

            $startState = strtolower($startState);

            foreach ($finishStates as &$f_state) {
                $f_state = strtolower($f_state);
            }
        }

        $states = array_unique($states);
        $states = array_values($states);

        $alphabet = array_unique($alphabet);
        $alphabet = array_values($alphabet);

        $rules = array_unique($rules);
        $rules = array_values($rules);

        $finishStates = array_unique($finishStates);
        $finishStates = array_values($finishStates);

        $startState = is_array($startState) ? $startState[0] : $startState;

        $this->states = $states;
        $this->alphabet = $alphabet;
        $this->rules = $rules;
        $this->startState = $startState;
        $this->finishStates = $finishStates;
    }

    public function check_fi()
    {
        global $debug;

        if ($debug)
            print_info_line("Started semantic analysis");

        if (empty($this->alphabet)) {
            print_error_line("Alphabet is empty");
            return false;
        }

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

        if (!$this->check_states())
            return false;

        if ($debug)
            print_info_line("Finished semantic analysis");

        return true;
    }

    public function remove_epsilon_rules()
    {
        $new_rules = array();

        foreach ($this->getStates() as $state) {
            $epsilon_uzaver = $this->get_epsilon_uzaver($state);

            foreach ($epsilon_uzaver as $eps_state) {
                $non_epsilon_rules = $this->get_non_epsilon_rules($eps_state);

                foreach ($non_epsilon_rules as $non_eps_rule) {
                    $new_rules[] = new Rule($state, $non_eps_rule->getCharacter(), $non_eps_rule->getRightState());
                }
            }
        }

        print_info_line("rules without epsilon rules == ");
        print_var($new_rules);

        $new_finish_states = array();

        foreach ($this->getStates() as $state) {
            $intersection = array_intersect($this->get_epsilon_uzaver($state), $this->getFinishStates());
            if (!empty($intersection))
                $new_finish_states[] = $state;
        }

        print_info_line("new finish states without epsilon");
        print_var($new_finish_states);

        $this->rules = $new_rules;
        $this->finishStates = $new_finish_states;
    }

    public function get_epsilon_uzaver($state)
    {
        if (!in_array($state, $this->states)) {
            print_error_line("internal error, epsilon uzaver called with illegal state " . $state);
            exit(666);
        }

        $epsilon_uzaver = [$state];

        $changed = true;
        while ($changed) {
            $changed = false;

            foreach ($epsilon_uzaver as $eps_state) {
                $rules = $this->get_epsilon_rules($eps_state);

                foreach ($rules as $rule) {
                    $right_state = $rule->getRightState();

                    if (!in_array($right_state, $epsilon_uzaver)) {
                        $epsilon_uzaver[] = $right_state;
                        $changed = true;
                    }
                }
            }
        }

        print_info_line("Epsilon uzaver stavu " . $state . " je: ");
        print_var($epsilon_uzaver);
        return $epsilon_uzaver;
    }

    private function get_epsilon_rules($state)
    {
        $result = array();

        foreach ($this->rules as $rule) {
            if ($rule->getLeftState() == $state and $rule->is_epsilon_rule())
                $result[] = $rule;
        }

        return $result;
    }

    private function get_non_epsilon_rules($state)
    {
        $result = array();

        foreach ($this->rules as $rule) {
            if ($rule->getLeftState() == $state and !$rule->is_epsilon_rule())
                $result[] = $rule;
        }

        return $result;
    }

    private function check_states()
    {
        foreach ($this->rules as $rule) {
            if (!$rule->check_rule($this))
                return false;
        }
        return true;
    }

    public function determinize()
    {
        global $debug;
        $this->remove_epsilon_rules();
        if($debug)
            $this->print_FI();

        print_info_line("-----------------------Determinization started---------------------------");

        $Sd = new SuperState([$this->getStartState()]);
        $Qnew = [new SuperState([$Sd])];
        $Rd = [];
        $Qd = [];
        $Fd = [];

        while(!empty($Qnew)){
            $Qcarka = array_shift($Qnew);
            $Qd[] = $Qcarka;
            print_info_line("New qcarka: ");
            print_var($Qcarka);
            print_info_line("new qd: ");
            print_var($Qd);

            foreach($this->getAlphabet() as $symbol){
                $Qcarkacarka = array();
                foreach($Qcarka->getStates() as $qcarkastate){
                    $specific_rules = $this->get_rules_with_left_state_and_symbol($qcarkastate,$symbol);
                    $specific_states = $this->get_right_states_from_array_of_rules($specific_rules);

                    $Qcarkacarka = array_merge($Qcarkacarka,$specific_states);
                }
                print_info_line("Qcarkacarka for symbol " . $symbol . ":");
                print_var($Qcarkacarka);

                $Qcarkacarka = array_unique($Qcarkacarka);
                $newState = new SuperState($Qcarkacarka);
                if(!empty($Qcarkacarka)){
                    $rule = new Rule($Qcarka,$symbol,$newState);
                    if(!in_array($rule,$Rd)) {
                        $Rd[] = $rule;

                        print_info_line("new rule: ");
                        print_var($rule);
                    }
                }
                if(!in_array($newState,$Qd) && !empty($Qcarkacarka)){
                    if(!in_array($newState,$Qnew)) {
                        $Qnew[] =  $newState;
                    }
                    print_info_line("Qnew for next iteration: ");
                    print_var($Qnew);
                    print_info_line("Current Qd :");
                    print_var($Qd);
                }
            }
            if(!empty(array_intersect($this->getFinishStates(),$Qcarka->getStates()))){
                $Fd[] = $Qcarka;
            }
            //fgetc(STDIN);
            print_info_line("------------------------------------------------------");
        }

        $this->states = $Qd;
        $this->rules = $Rd;
        $this->startState = $Sd;
        $this->finishStates = $Fd;

        print_info_line("-----------------------Determinization finished---------------------------");
    }

    private function get_right_states_from_array_of_rules($rules){
        $result = array();
        foreach($rules as $rule){
            $result[] = $rule->getRightState();
        }
        return $result;
    }

    private function get_rules_with_left_state_and_symbol($left_state,$symbol){
        $result  = array();
        foreach($this->getRules() as $rule){
            if($rule->getLeftState() == $left_state && $rule->getCharacter() == $symbol){
                $result[] = $rule;
            }
        }
        return $result;
    }

    public function print_FI()
    {
        global $arguments;

        print_info_line("printing current FI");
        print_info_line("-------------------");

        $result = "(\n{";
        $this->add_states_for_printing($result);
        $result .= "},\n";

        $result .= "{";
        $this->add_alphabet_for_printing($result);
        $result .= "},\n";

        $result .= "{\n";
        $this->add_rules_for_printing($result);
        $result .= "},\n";

        $result .= $this->getStartState() . ",\n";

        $result .= "{";
        $this->add_finish_states_for_printing($result);
        $result .= "}\n)";

        fprintf($arguments["output"], $result);

        print_info_line("--------end--------");
    }

    function add_states_for_printing(&$result)
    {
        $states = $this->getStates();
        usort($states, "state_cmp");
        //sort($states);
        $len = count($states);
        for ($i = 0; $i < $len; $i++) {
            if ($i < $len - 1)
                $result .= $states[$i] . ", ";
            else
                $result .= $states[$i];
        }
    }

    function add_finish_states_for_printing(&$result)
    {
        $states = $this->getFinishStates();
        usort($states,"state_cmp");
        $len = count($states);
        for ($i = 0; $i < $len; $i++) {
            if ($i < $len - 1)
                $result .= $states[$i] . ", ";
            else
                $result .= $states[$i];
        }
    }

    function add_alphabet_for_printing(&$result)
    {
        $alphabet = $this->getAlphabet();
        sort($alphabet);
        $len = count($alphabet);
        for ($i = 0; $i < $len; $i++) {
            if ($i < $len - 1)
                $result .= '\'' . $alphabet[$i] . '\', ';
            else
                $result .= '\'' . $alphabet[$i] . '\'';
        }
    }

    function add_rules_for_printing(&$result)
    {
        $rules = $this->getRules();
        usort($rules,"rule_cmp");
        foreach ($rules as $rule) {
            $result .= $rule->getLeftState() . " '" . $rule->getCharacter() . "' -> " . $rule->getRightState() . ",\n";
        }
        $result = substr($result,0,-2);
        $result .= "\n";
    }

    public function wsfa()
    {
        print_info_line("-------------------here----------------");
        $this->determinize();
        $this->compute_ending_states();
        $this->complete_rules();
    }

    private function complete_rules()
    {
        $name = "qFalse";
        $new_rules = array();

        foreach ($this->getStates() as $state) {
            foreach ($this->getAlphabet() as $symbol) {
                if (!$this->contains_rule_with_left($state, $symbol)) {
                    $new_rules[] = new Rule($state, $symbol, $name);
                }
            }
        }

        if (!empty($new_rules)) {

            if (!in_array($name, $this->getStates())) {
                $this->states [] = $name;
                foreach ($this->getAlphabet() as $symbol) {
                    $this->rules[] = new Rule($name, $symbol, $name);
                }
            }

            $this->rules = array_merge($this->rules, $new_rules);
        }
    }

    private function contains_rule_with_left($left_state, $symbol)
    {
        foreach ($this->getRules() as $rule) {
            if ($rule->getLeftState() == $left_state and $rule->getCharacter() == $symbol)
                return true;
        }
        return false;
    }

    private function compute_ending_states()
    {

        $good_states = array();
        $stack = $this->getFinishStates();

        while (!empty($stack)) {

            $state = $stack[array_rand($stack, 1)];
            $stack = array_diff($stack, [$state]);

            foreach ($this->get_all_left_states_from_right_state($state) as $state) {
                if (!in_array($state, $good_states)) {
                    $good_states [] = $state;
                    $stack[] = $state;
                }
            }
        }
        $this->states = $good_states;
        if (!in_array($this->startState, $good_states))
            $this->states[] = "qFalse";
    }

    private function get_all_left_states_from_right_state($state)
    {
        $result = array();

        foreach ($this->getRules() as $rule) {
            if ($rule->getRightState() == $state)
                $result[] = $rule->getLeftState();
        }

        return $result;
    }

    public function check_string($string)
    {
        $this->check_string_symbols($string);
        $this->determinize();
        $current_state = $this->getStartState();

        //$this->print_FI();

        $strlen = strlen($string);
        for ($i = 0; $i < $strlen; $i++, $next_state = $current_state) {
            $char = substr($string, $i, 1);
            $next_state = null;

            print_info_line("Checking char: " . $char);
            foreach ($this->get_rules_with_left_state($current_state) as $rule) {
                print_info_line("Checking rule :" . $rule);

                if ($rule->getCharacter() == $char)
                    $next_state = $rule->getRightState();
            }
            if ($next_state == null) {
                print_error_line("There is no way to go from state " . $current_state . " with symbol " . $char);
                echo "0";
                exit(0);
            }
        }
        print_info_line("The string was accepted");
        echo "1";
        exit(0);
    }

    private function check_string_symbols($string)
    {
        $strlen = strlen($string);
        for ($i = 0; $i < $strlen; $i++) {
            $char = substr($string, $i, 1);

            if (!in_array($char, $this->alphabet)) {
                print_error_line("Symbol " . $char . " is not in alphabet");
                exit(1);
            }
        }
    }

    private function get_rules_with_left_state($state)
    {
        $rules = array();
        foreach ($this->rules as $rule) {
            if ($rule->getLeftState() == $state)
                $rules[] = $rule;
        }
        return $rules;
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

function rule_cmp(Rule $a,Rule $b){
    $res = state_cmp($a->getLeftState(),$b->getLeftState());
    if($res != 0)
        return $res;
    $res = strcmp($a->getCharacter(),$b->getCharacter());
    if($res != 0)
        return $res;
    return state_cmp($a->getRightState(),$b->getRightState());
}

function state_cmp($a, $b)
{
    if(is_string($a) && is_string($b))
        return strcmp($a,$b);
    elseif ($a instanceof SuperState && $b instanceof SuperState) {
        return state_cmp($a->getStates(), $b->getStates());
    } elseif($a instanceof SuperState)
        return state_cmp($a->getStates(),$b);
    elseif($b instanceof SuperState)
        return state_cmp($a,$b->getStates());

    $len = min(count($a),count($b));

    for($i = 0 ; $i < $len ; $i++){
        if($a[$i] > $b[$i]){
            return 1;
        } elseif($a[$i] < $b[$i]){
            return -1;
        }
    }
    if(count($a) > count($b))
        return 1;
    elseif(count($b) > count($a))
        return -1;
    else return 0;
}