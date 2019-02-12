<?php

class AdminController
{
    public function login()
    {
    

        $view = new View();    
        $view->render('login', [
            "message" => ""
        ]);
    }

    public function registration()
    {
        $view = new View();
        $view->render('registration',["message"=>""]);
       
    }

    public function register()
    {

        $firstName = trim(Request::post('firstname'));
        $lastName = trim(Request::post('lastname'));
        $email = trim(Request::post('email'));
        $pass = trim(Request::post('pass'));
        $valid = true;

        if ($firstName === '' || $lastName === '' || $email === '' || $pass === '') {
            $valid = false;
            $message = 'Obavezan unos svih polja';
        }

        if ($valid) {
            $db = Db::connect();
            $statement = $db->prepare("insert into user (firstname,lastname,email,pass,role) values 
            (:firstname,:lastname,:email,:pass,:role)");
            $statement->bindValue('firstname', $firstName);
            $statement->bindValue('lastname', $lastName);
            $statement->bindValue('email', $email);
            $statement->bindValue('pass', password_hash($pass,PASSWORD_DEFAULT));
            $statement->bindValue('role', 'admin');
            $statement->execute();
            $lastId = $db->lastInsertId();

            $statement = $db->prepare('select count(id) from user');
            $statement->execute();
            $countID = $statement->fetchColumn();

            if ($countID > 1) {
                $statement = $db->prepare('update user set role=:role where id=:id');
                $statement->bindValue(':role', 'user');
                $statement->bindValue(':id', $lastId);
                $statement->execute();
            }

            Session::getInstance()->logout();
            $view = new View();
            $view->render('login',["message"=>""]);
        } else {
            $view = new View();
            $view->render('registration', ['message' => $message]);
        }

       
    }

    public function delete($post)
    {

        $db = Db::connect();
        $db->beginTransaction();
        $statement = $db->prepare("delete from comment where post=:post");
        $statement->bindValue('post', $post);
        $statement->execute();

        $statement = $db->prepare("delete from likes where post=:post");
        $statement->bindValue('post', $post);
        $statement->execute();

        $statement = $db->prepare("delete from post where id=:post");
        $statement->bindValue('post', $post);
    
        $statement->execute();

        $db->commit();
        
        $this->index();
       
    }

    public function comment($post)
    {

        $db = Db::connect();
        $statement = $db->prepare("insert into comment (post,user, content) values (:post,:user,:content)");
        $statement->bindValue('post', $post);
        $statement->bindValue('user', Session::getInstance()->getUser()->id);
        $statement->bindValue('content', Request::post("content"));
        $statement->execute();
        
        $view = new View();

        $view->render('view', [
            "post" => Post::find($post),
            "likes" => Post::postLikesList($post)
        ]);
       
    }


    public function like($post)
    {
        $id = Session::getInstance()->getUser()->id;
        $uniqueLikes = $post . '-' . $id;
        try {
            $db = Db::connect();
            $statement = $db->prepare("insert into likes (post,user,uniquelikes) values (:post,:user,:uniquelikes)");
            $statement->bindValue('post', $post);
            $statement->bindValue('user', Session::getInstance()->getUser()->id);
            $statement->bindValue(':uniquelikes', $uniqueLikes);
            $statement->execute();
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {

            }
        }

        
        $this->index();
       
    }


    public function authorize()
    {
        $email = trim(Request::post("email"));
        $pass = trim(Request::post("password"));
        $valid = true;

        if ($email === '' || $pass === '') {
            $valid = false;
            $message = 'Obavezan unos svih polja';
        }

        if ($valid) {
            $db = Db::connect();
            $statement = $db->prepare("select id, firstname, lastname, email, pass from user where email=:email");
            $statement->bindValue('email', Request::post("email"));
            $statement->execute();


            if($statement->rowCount()>0){
                $user = $statement->fetch();
                if(password_verify(Request::post("password"), $user->pass)){

                    unset($user->pass);

                    Session::getInstance()->login($user);

                    $this->index();
                }else{
                    $view = new View();
                    $view->render('login',["message"=>"Neispravna kombinacija korisničko ime i lozinka"]);
                }
            }else{
                $view = new View();
                $view->render('login',["message"=>"Neispravan email"]);
            }
        } else {
            $view = new View();
            $view->render('login', ['message' => $message]);
        }


    }

    public function logout()
    {
    
        Session::getInstance()->logout();
        $this->index();
    }

    public function index()
    {

        $posts = Post::all();
        $view = new View();
        $view->render('index', [
            "posts" => $posts
        ]);
    }

    public function profile()
    {
        $vieW = new View();
        $vieW->render('profile', [

        ]);
    }

    public function updateImage()
    {

            $targetDir = BP . 'uploads/';
            $name = basename($_FILES['image']['name']);
            $targetFile = $targetDir . $name;
            $fileType = pathinfo($targetFile, PATHINFO_EXTENSION);
            $allowedFileTypes=array("jpg", "jpeg");
            $message = '';
            $valid = true;

            if (!in_array($fileType, $allowedFileTypes)) {
                $valid = false;
                $message = 'Not allowed file type, only jpeg and jpg are allowed';

            }

            if ($_FILES['image']['size'] > 2097152) {
                $valid = false;
                $message = 'File size too big';

            }

            if ($valid) {
                move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);

                $connection = Db::connect();
                $sql = 'update user set image=:image where id=:id';
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(':image', $name);
                $stmt->bindValue(':id', Session::getInstance()->getUser()->id);
                $stmt->execute();

                Session::getInstance()->getUser()->image = $name;
            }

            $view = new View();
            $view->render('profile', [
                'message' => $message
            ]);


    }

    public function updateUser()
    {
        $data = $_POST;
        $firstName = trim($data['firstname']);
        $lastName = trim($data['lastname']);
        $email = trim($data['email']);


        if ($firstName === '' || $lastName === '' || $email === '') {
            $valid = false;
            $message = 'Obavezan unos';
        } else {
            $valid = true;
            $message = 'Uspiješna promjena';
        }

        if ($valid) {
            $connection = Db::connect();
            $sql = 'update user set firstname=:firstname, lastname=:lastname, email=:email where id=:id';
            $stmt = $connection->prepare($sql);
            $stmt->bindValue(':firstname', $firstName);
            $stmt->bindValue(':lastname', $lastName);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':id', $_POST['id']);
            $stmt->execute();
            Session::getInstance()->getUser()->firstname = $firstName;
            Session::getInstance()->getUser()->lastname = $lastName;
            Session::getInstance()->getUser()->email = $email;


        }

        $view = new View();
        $view->render('profile', ['message'=>$message]);

    }

    public function updatePassword()
    {
            $newPass = trim($_POST['newPassword']);
            $confPass = trim($_POST['confPassword']);

            if($newPass !== '' && $newPass === $confPass ) {
                $connection = Db::connect();
                $sql = 'update user set pass=:newpassword where id=:id';
                $stmt = $connection->prepare($sql);
                $stmt->bindValue(':newpassword', password_hash($newPass, PASSWORD_DEFAULT));
                $stmt->bindValue(':id', Session::getInstance()->getUser()->id);
                $stmt->execute();

                $view = new View();
                $view->render('profile', ['message'=>'Lozinka uspiješno primjenjena']);
            } else {
                $view = new View();
                $view->render('profile', ['message' => 'Pokušaj ponovo']);
            }
    }

    public function reportPost($post)
    {
        $id = Session::getInstance()->getUser()->id;
        $uniqueReports = $post . '-' . $id;

        try {
            $db = Db::connect();
            $statement = $db->prepare("insert into report (userid,postid,uniquereport) values (:userid,:postid,:uniquereport)");
            $statement->bindValue('postid', $post);
            $statement->bindValue('userid', Session::getInstance()->getUser()->id);
            $statement->bindValue(':uniquereport', $uniqueReports);
            $statement->execute();
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {

            }
        }


        $this->index();

    }

    public function reportComment($comment)
    {

        $db = Db::connect();
        $statement = $db->prepare("insert into reportcomment (userid,commentid) values 
        (:userid,:commentid)");
        $statement->bindValue('commentid', $comment);
        $statement->bindValue('userid', Session::getInstance()->getUser()->id);
        $statement->execute();

        header('Location: ' . App::config('url') . 'Index/view/' . $comment);

    }

    public function hide($postid)
    {
        $postid = intval($postid);
        $db = Db::connect();
        $statement = $db->prepare('update post set hidden=:hidden where id=:postid');
        $statement->bindValue(':hidden', 1);
        $statement->bindValue(':postid', $postid);
        $statement->execute();

        header('Location: ' . App::config('url'));

    }


}