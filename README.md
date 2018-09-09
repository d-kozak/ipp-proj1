# Finite automata determining in PHP

Grammar describing the input
```
S->(START_TWO)
START_TWO->{STATES},{ALPHABET},{RULES},state,{FINISH}
STATES -> state STATES_N
STATES_N -> epsilon
STATES_N -> ,state STATES_N
ALPHABET -> symbol ALPHABET_N
ALPHABET_N -> epsilon
ALPHABET_N -> ,symbol ALPHABET_N
FINISH -> state FINISH_N
FINISH_N -> epsilon
FINISH_N -> ,state FINISH_N
RULES -> RULE RULES_N
RULES -> epsilon
RULE -> state symbol -> state
RULES_N -> epsilon
RULES_N -> ,RULE RULES_N
```

