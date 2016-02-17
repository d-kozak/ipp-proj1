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
        print_r($new_rules);

        $new_finish_states = array();

        foreach ($this->getStates() as $state) {
            if (!empty(array_intersect($this->get_epsilon_uzaver($state), $this->getFinishStates())))
                $new_finish_states[] = $state;
        }

        print_info_line("new finish states without epsilon");
        print_r($new_finish_states);

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

        echo "Epsilon uzaver stavu " . $state . " je: ";
        print_r($epsilon_uzaver);
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

    public function print_FI()
    {
        print_info_line("printing current FI");
        print_info_line("-------------------");

        $result = "({";
        $this->add_states_for_printing($result);
        $result .= "},\n";

        $result .= "{";
        $this->add_alphabet_for_printing($result);
        $result .= "},\n";

        $result .= "{\n";
        $this->add_rules_for_printing($result);
        $result .= "},\n";

        $result .= "{" . $this->getStartState() . "}\n";

        $result .= "{";
        $this->add_finish_states_for_printing($result);
        $result .= "},\n)";

        echo $result;

        print_info_line("--------end--------");
    }

    function add_states_for_printing(&$result)
    {
        $states = $this->getStates();
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
        foreach ($this->getRules() as $rule) {
            $result .= $rule->getLeftState() . " " . $rule->getCharacter() . " -> " . $rule->getRightState() . ",\n";
        }
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