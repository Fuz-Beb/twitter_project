<?php
use PHPUnit\Framework\TestCase;
use \Model\Post;
use \Model\User;
use \Model\Hashtag;

class HashtagTest extends TestCase
{
    protected static $pids;
    protected static $uid;

    public static function setUpBeforeClass()
    {
        self::$uid = User\create(
            "userpost1",
            "User 1",
            "password",
            "user1@mail.com",
            ""
        );
        self::$pids[] = Post\create(self::$uid, "This is a sample text");
        self::$pids[] = Post\create(self::$uid, "This is a sample text #hashtag1");               
        self::$pids[] = Post\create(self::$uid, "This is a sample text #hashtag1");               
        self::$pids[] = Post\create(self::$uid, "This is a sample text #hashtag2");               
        self::$pids[] = Post\create(self::$uid, "This is a sample text #hashtag2");               
        self::$pids[] = Post\create(self::$uid, "This is a sample text #hashtag2");               
        self::$pids[] = Post\create(self::$uid, "This is a sample text #hashtag3");               
        self::$pids[] = Post\create(self::$uid, "This is a sample text #hashtag3");               
        self::$pids[] = Post\create(self::$uid, "This is a sample text #hashtag3");               
        self::$pids[] = Post\create(self::$uid, "This is a sample text #hashtag3");               
        self::$pids[] = Post\create(self::$uid, "Two hashtags #hash #tag");
    }

    public function testAttach()
    {
        $this->assertTrue(Hashtag\attach(self::$pids[0], "hashtag0"));
        $l = Hashtag\list_hashtags();
        $this->assertContains("hashtag0", $l);
        $this->assertContains("hashtag1", $l);
        $this->assertContains("hashtag2", $l);
        $this->assertContains("hashtag3", $l);
        $this->assertContains("hash", $l);
        $this->assertContains("tag", $l);
        $this->assertContains("hashtag3", $l);
    }

    /**
     * @depends testAttach
     */  
    public function testListPopularHashtags()
    {
        $l = Hashtag\list_popular_hashtags(5);
        $this->assertEquals($l[0], "hashtag3");
        $this->assertEquals($l[1], "hashtag2");
        $this->assertEquals($l[2], "hashtag1");
        $this->assertEquals($l[3], "hashtag0");
    }

    /**
     * @depends testListPopularHashtags
     */  
    public function testGetPosts()
    {
        $p = Hashtag\get_posts("hashtag0");
        $this->assertEquals(count($p), 1);
        $this->assertEquals($p[0]->id, self::$pids[0]);
    }

    /**
     * @depends testGetPosts
     */  
    public function testGetRelatedHashtags()
    {
        $h = Hashtag\get_related_hashtags("hash", 5);
        $this->assertEquals(count($h), 1);
        $this->assertEquals($h[0], "tag");
    }

    public static function tearDownAfterClass()
    {
        \Db::flush();
    }
}
?>
