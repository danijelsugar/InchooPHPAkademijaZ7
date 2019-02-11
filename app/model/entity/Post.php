<?php

class Post
{
    private $id;

    private $content;

    private $user;

    private $date;

    private $hidden;

    private $likes;

    private $comments;

    private $tags;

    private $reports;

    private $userid;

    public function __construct($id, $content, $user,$date, $hidden, $likes,$comments,$tags, $reports, $userid)
    {
        $this->setId($id);
        $this->setContent($content);
        $this->setUser($user);
        $this->setDate($date);
        $this->setHidden($hidden);
        $this->setLikes($likes);
        $this->setComments($comments);
        $this->setTags($tags);
        $this->setReports($reports);
        $this->setUserid($userid);
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

    public static function all()
    {

        $list = [];
        $db = Db::connect();
        $statement = $db->prepare("select 
        a.id, a.content, a.user as userid, concat(b.firstname, ' ', b.lastname) as user, a.date, a.hidden,  
        count(c.id) as likes
        from 
        post a inner join user b on a.user=b.id 
        left join likes c on a.id=c.post 
        where a.date > ADDDATE(now(), INTERVAL -7 DAY) 
        group by a.id, a.content, concat(b.firstname, ' ', b.lastname), a.date, a.hidden 
        order by a.date desc limit 10");
        $statement->execute();
        foreach ($statement->fetchAll() as $post) {

            $statement = $db->prepare("select a.id, a.content, concat(b.firstname, ' ', b.lastname) as user, a.date from comment a inner join user b on a.user=b.id where a.post=:id ");
            $statement->bindValue('id', $post->id);
            $statement->execute();
            $comments = $statement->fetchAll();

            $statement = $db->prepare('select a.post, b.name from tagpost a inner join tag b on a.tag=b.id where a.post=:id');
            $statement->bindValue('id', $post->id);
            $statement->execute();
            $tags = $statement->fetchAll();

            $statement = $db->prepare('select count(postid) from report where postid=:id');
            $statement->bindValue(':id', $post->id);
            $statement->execute();
            $reports = $statement->fetchColumn();


            $list[] = new Post($post->id, $post->content, $post->user,$post->date, $post->hidden, $post->likes,$comments,$tags,$reports, $post->userid);



        }

        return $list;
    }

    public static function find($id)
    {
        $id = intval($id);
        $db = Db::connect();
        $statement = $db->prepare("select 
        a.id, a.content, concat(b.firstname, ' ', b.lastname) as user, a.date, a.hidden, a.user as userid, count(c.id) as likes
        from 
        post a inner join user b on a.user=b.id 
        left join likes c on a.id=c.post 
         where a.id=:id");
        $statement->bindValue('id', $id);
        $statement->execute();
        $post = $statement->fetch();

        $statement = $db->prepare("select a.id, a.content, concat(b.firstname, ' ', b.lastname) as user, a.date from comment a inner join user b on a.user=b.id where a.post=:id ");
        $statement->bindValue('id', $id);
        $statement->execute();
        $comments = $statement->fetchAll();

        $statement = $db->prepare('select a.post, b.name from tagpost a inner join tag b on a.tag=b.id where a.post=:id');
        $statement->bindValue('id', $post->id);
        $statement->execute();
        $tags = $statement->fetchAll();

        $statement = $db->prepare('select count(postid) from report where postid=:id');
        $statement->bindValue(':id', $post->id);
        $statement->execute();
        $reports = $statement->fetchColumn();



        return new Post($post->id, $post->content, $post->user, $post->date, $post->hidden, $post->likes, $comments,$tags, $reports, $post->userid);
    }

    public static function postLikesList($id)
    {

        $id = intval($id);
        $db = Db::connect();
        $likes = [];
        $statement = $db->prepare('select a.id, concat(b.firstname, \' \', b.lastname) as user, a.post 
        from likes a 
        inner join user b 
        on a.user=b.id where a.post=:id');
        $statement->bindValue(':id', $id);
        $statement->execute();
        $likes = $statement->fetchAll();

        return $likes;

    }

}