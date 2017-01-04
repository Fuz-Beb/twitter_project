<?php
use PHPUnit\Framework\TestCase;
use \Model\Post;
use \Model\User;
use \Model\Notification;

class NotificationTest extends TestCase
{
    protected static $pids;
    protected static $uids;
    public static function setUpBeforeClass()
    {
        self::$uids = [];
        self::$uids[] = User\create(
            "userpost1",
            "User 1",
            "password",
            "user1@mail.com",
            ""
        );
        
        self::$uids[] = User\create(
            "userpost2",
            "User 2",
            "password",
            "user2@mail.com",
            ""
        );
        self::$pids = [];
        
        self::$pids[] = Post\create(self::$uids[0], "this is a searchid1 test");
        self::$pids[] = Post\create(self::$uids[1], "this searchid2 is a test");
    }

    public function testLikedNotification()
    {
        Post\like(self::$uids[0], self::$pids[1]);
        $n = Notification\get_liked_notifications(self::$uids[1]);
        $this->assertEquals(count($n), 1);
        $this->assertEquals($n[0]->post->id, self::$pids[1]);
        $this->assertEquals($n[0]->liked_by->id, self::$uids[0]);
        $this->assertEquals($n[0]->type, "liked");
        $this->assertObjectHasAttribute('date', $n[0]);
        $this->assertObjectHasAttribute('reading_date', $n[0]);

        $this->assertEquals($n[0]->reading_date, null);
        $this->assertTrue(Notification\liked_notification_seen(self::$pids[1], self::$uids[0]));
        $n = Notification\get_liked_notifications(self::$uids[1]);
        $this->assertNotEquals($n[0]->reading_date, null);
        
        Post\unlike(self::$uids[0], self::$pids[1]);
        $n = Notification\get_liked_notifications(self::$uids[1]);
        $this->assertEmpty($n);
    }

    public function testMentionedNotification()
    {
        Post\mention_user(self::$pids[0], self::$uids[1]);
        $n = Notification\get_mentioned_notifications(self::$uids[1]);
        $this->assertEquals(count($n), 1);
        $this->assertEquals($n[0]->mentioned_by->id, self::$uids[0]);
        $this->assertEquals($n[0]->post->id, self::$pids[0]);
        $this->assertEquals($n[0]->type, "mentioned");
        $this->assertObjectHasAttribute('date', $n[0]);
        $this->assertObjectHasAttribute('reading_date', $n[0]);

        $this->assertEquals($n[0]->reading_date, null);
        $this->assertTrue(Notification\mentioned_notification_seen(self::$uids[1], self::$pids[0]));
        $n = Notification\get_mentioned_notifications(self::$uids[1]);
        $this->assertNotEquals($n[0]->reading_date, null);

        Post\destroy(self::$pids[0]);
        $n = Notification\get_liked_notifications(self::$uids[1]);
        $this->assertEmpty($n);
    }

    public function testFollowedNotification()
    {
        User\follow(self::$uids[0], self::$uids[1]);
        $n = Notification\get_followed_notifications(self::$uids[1]);
        $this->assertEquals(count($n), 1);
        $this->assertEquals($n[0]->user->id, self::$uids[0]);
        $this->assertEquals($n[0]->type, "followed");
        $this->assertObjectHasAttribute('date', $n[0]);

        $this->assertEquals($n[0]->reading_date, null);
        $this->assertTrue(Notification\followed_notification_seen(self::$uids[1], self::$uids[0]));
        $n = Notification\get_followed_notifications(self::$uids[1]);
        $this->assertNotEquals($n[0]->reading_date, null);
        
        User\unfollow(self::$uids[0], self::$uids[1]);
        $n = Notification\get_followed_notifications(self::$uids[1]);
        $this->assertEmpty($n);
    }

    public function testListAllNotifications()
    {
        User\follow(self::$uids[0], self::$uids[1]);
        Post\mention_user(self::$pids[1], self::$uids[1]);
        Post\like(self::$uids[0], self::$pids[1]);
        
        $n = Notification\list_all_notifications(self::$uids[1]);
        $this->assertEquals(count($n), 3);
        $this->assertEquals($n[0]->type, "liked");
        $this->assertEquals($n[1]->type, "mentioned");
        $this->assertEquals($n[2]->type, "followed");
    }

    public static function tearDownAfterClass()
    {
        \Db::flush();
    }

}
?>
