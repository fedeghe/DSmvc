<?php
class Person {
    private $name;
    private $surname;
    private $nick;

    public function __construct($n, $s, $nick = false) {
        $this->name = $n;
        $this->surname = $s;
        $this->nick = $nick;
    }

    public function sayHello() {
        return 'Hi, I`m ' . $this->name . ' ' . $this->surname . ($this->nick ? ' '.$this->nick : '') ;
    }
}