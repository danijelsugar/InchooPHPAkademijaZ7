<?php

class User {

    private $id;

    private $firstname;

    private $lastname;

    private $email;

    private $image;

    private $role;

    public function __construct($id, $firstname, $lastname, $email, $image, $role)
    {
        $this->setId($id);
        $this->setFirstName($firstname);
        $this->setLastName($lastname);
        $this->setEmail($email);
        $this->setImage($image);
        $this->setRole($role);
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

    public static function userData($userid)
    {
        $list = [];
        $user_id = intval($userid);
        $db = Db::connect();
        $statement = $db->prepare("select * from user where id = :userid");
        $statement->bindValue('userid', $userid);
        $statement->execute();
        foreach ($statement->fetchAll() as $user) {
            $list = new User($user->id, $user->firstname, $user->lastname, $user->email, $user->image, $user->role);
        }
        return $list;
    }


}