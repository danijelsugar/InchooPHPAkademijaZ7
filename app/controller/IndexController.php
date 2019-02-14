<?php

class IndexController
{
    public function index()
    {

        $view = new View();
        $posts = Post::all($this->totalPages);

        $view->render('index', [
            "posts" => $posts
        ]);
    }

    private $totalPages;

    public function pagination($page)
    {

        $this->totalPages = $page;

        $this->index();
    }

    public function view($id = 0)
    {
        $view = new View();

        $view->render('view', [
            "post" => Post::find($id),
            "likes" => Post::postLikesList($id)
        ]);
    }

    public function newPost()
    {
        $data = $this->_validate($_POST);


            if ($data === false) {
                header('Location: ' . App::config('url'));
            } else {
                $connection = Db::connect();
                $sql = 'INSERT INTO post (content,user) VALUES (:content,:user)';
                $stmt = $connection->prepare($sql);
                $stmt->bindValue('content', $data['content']);
                $stmt->bindValue('user', Session::getInstance()->getUser()->id);
                $stmt->execute();
                $lastPost = $connection->lastInsertId();

                if (!empty($data['tags'])) {
                    $tags = $data['tags'];
                    $tags = explode(',', $tags);

                    foreach ($tags as $tag) {
                        trim($tag);
                        $sql = 'insert into tag (name) values(:name)';
                        $stmt = $connection->prepare($sql);
                        $stmt->bindValue(':name', $tag);
                        $stmt->execute();
                        $lastTag = $connection->lastInsertId();

                        $sql = 'insert into tagpost (post,tag) values(:post,:tag)';
                        $stmt = $connection->prepare($sql);
                        $stmt->bindValue(':post', $lastPost);
                        $stmt->bindValue(':tag', $lastTag);
                        $stmt->execute();
                    }
                }



                header('Location: ' . App::config('url'));

            }




    }

    /**
     * @param $data
     * @return array|bool
     */
    private function _validate($data)
    {
        $required = ['content'];

        //validate required keys
        foreach ($required as $key) {
            if (!isset($data[$key])) {
                return false;
            }

            $data[$key] = trim((string)$data[$key]);
            if (empty($data[$key])) {
                return false;
            }
        }
        return $data;
    }
}