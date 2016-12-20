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
        $this->assertTrue(in_array("hashtag0", Hashtag\list_hashtags()));
    }

    public function testListHashtags()
    {
        $l = Hashtag\list_hashtags();
        $this->assertTrue(in_array("hashtag0", $l));
        $this->assertTrue(in_array("hashtag1", $l));
        $this->assertTrue(in_array("hashtag2", $l));
        $this->assertTrue(in_array("hashtag3", $l));
    }

    public function testListPopularHashtags()
    {
        $l = Hashtag\list_popular_hashtags(5);
        $this->assertEquals($l[0], "hashtag3");
        $this->assertEquals($l[0], "hashtag2");
        $this->assertEquals($l[0], "hashtag1");
        $this->assertEquals($l[0], "hashtag0");
    }

    public function testGetPosts()
    {
        $p = Hashtag\get_posts("hashtag0");
        $this->assertEquals(count($p), 1);
        $this->assertEquals($p[0]->id, self::$pids[0]);
    }

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
