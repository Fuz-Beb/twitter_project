<?php
use PHPUnit\Framework\TestCase;
use \Model\Post;
use \Model\User;
class PostTest extends TestCase
{
    protected static $users;
    public static function setUpBeforeClass()
    {
        User\create(
            "userpost1",
            "User 1",
            "password",
            "user1@mail.com",
            ""
        );
        User\create(
            "userpost2",
            "User 2",
            "password",
            "user2@mail.com",
            ""
        );
        self::$users = User\list_all();
    }

    public function testCreate()
    {
        $uid = self::$users[0]->id;
        $pid = Post\create($uid, "This is a sample text");
        $this->assertTrue($pid !== null);
        $post = Post\get($pid);
        $this->assertEquals($post->author->id, $pid);
        $this->assertEquals($post->text, "This is a sample text");
        $this->assertEquals($post->id, $pid);
        return $pid;
    }

    /**
     * @depends testCreate
     */  
    public function testRespond($pid)
    {
        $uid = self::$users[1]->id;
        $new_pid = Post\create($uid, "This is a sample response", $pid);
        $post = Post\get_with_joins($new_pid);
        $this->assertTrue($post->responds_to == Post\get($pid));
        $this->assertTrue(Post\get_responses($pid)[0] == Post\get($new_pid));
    }

    /**
     * @depends testRespond
     */  
    public function testMentionUser()
    {
        $uid = self::$users[0]->id;
        $pid = Post\create($uid, "@".self::$users[1]->username);
        $m = Post\get_mentioned($pid);
        $this->assertEquals(count($m), 1);
        $this->assertTrue(self::$users[1] == $m[0]);
        return $pid;
    }

    /**
     * @depends testRespond
     */  
    public function testLike($pid)
    {
        $this->assertTrue(User\like(self::$users[1]->id, $pid));
        $post = Post\get_with_joins($pid);
        $this->assertEquals(count($post->likes), 1);
        $this->assertTrue($post->likes[0] == self::$users[1]);
        $this->assertTrue(User\unlike(self::$users[1]->id, $pid));
        $post = Post\get_with_joins($pid);
        $this->assertEmpty($post->likes);
    }

    /**
     * @depends testLike
     */
    public function testSearch()
    {
        $pid1 = Post\create($users[0]->id, "this is a searchid1 test");
        $pid2 = Post\create($users[1]->id, "this searchid2 is a test");
        $s = Post\search("searchid1");
        $this->assertEquals(count($s), 1);
        $this->assertEquals($s[0]->id, $pid1);
        $s = Post\search("searchid2");
        $this->assertEquals(count($s), 1);
        $this->assertEquals($s[0]->id, $pid2);
    }

    /**
     * @depends testSearch
     */      
    public function testDestroy()
    {
        foreach(Post\list_all() as $post)
        {
            Post\destroy($post->id);
        }
        $pid1 = Post\create($users[0]->id, "this is a searchid1 test");
        $pid2 = Post\create($users[1]->id, "this is a searchid2 test");
        $this->assertTrue(Post\destroy($pid1));
        $posts = Post\list_all();
        $this->assertEquals(count($posts), 1);
        $this->assertEquals($posts[0]->id, $pid2);
    }

    /**
     * @depends testDestroy
     */      
    public function testLists()
    {
        foreach(Post\list_all() as $post)
        {
            Post\destroy($post->id);
        }
        $pid1 = Post\create($users[0]->id, "this is a searchid1 test");
        $pid2 = Post\create($users[1]->id, "this is a searchid2 test");
        $posts = Post\list_all("DESC");
        $this->assertEquals($posts[0]->id, $pid2);
        $this->assertEquals($posts[1]->id, $pid1);
        $posts = Post\list_all("ASC");
        $this->assertEquals($posts[0]->id, $pid1);
        $this->assertEquals($posts[1]->id, $pid2);
    }

    public static function tearDownAfterClass()
    {
        \Db::flush();
    }
}
?>
