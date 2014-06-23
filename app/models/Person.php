<?php
class Person {
    private $name;
    private $surname;

    public function __construct($n, $s) {
        $this->name = $n;
        $this->surname = $s;
    }

    public function sayHello() {
        return 'Hello I`m ' . $this->name . ' ' . $this->surname;
    }
}