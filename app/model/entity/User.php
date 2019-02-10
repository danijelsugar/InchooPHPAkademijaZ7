<?php

class User {

    private $id;

    private $firstname;

    private $lastname;

    private $email;

    private $image;

    public function __construct($id, $firstname, $lastname, $email, $image)
    {
        $this->setId($id);
        $this->setFirstname($firstname);
        $this->setLastname($lastname);
        $this->setEmail($email);
        $this->setImage($image);
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    public function __call($name, $arguments)
    {
        $function = substr($name, 0, 3);
        if ($function === 'set') {
            $this->__set(strtolower(substr($name, 3)), $arguments[0]);
            return $this;
        } else if ($function === 'get') {
            return $this->__get(strtolower(substr($name, 3)));
        }

        return $this;
    }

    public static function userData( $user_id )
    {
        $list = [];
        $user_id = intval($user_id);
        $db = Db::connect();
        $statement = $db->prepare("select * from user where id = :user_id");
        $statement->bindValue('user_id', $user_id);
        $statement->execute();
        foreach ($statement->fetchAll() as $user) {
            $list = new User($user->id, $user->firstname, $user->lastname, $user->email, $user->image);
        }
        return $list;
    }


}