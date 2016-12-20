<?php
use PHPUnit\Framework\TestCase;
use Model\User;

class UserTest extends TestCase
{
    public function testCreate()
    {
        $uid = User\create(
            "user1",
            "User 1",
            "password",
            "user1@mail.com",
            ""
        );
        $this->assertNotNull($uid);
        $user = User\get($uid);
        $this->assertNotNull($user);
        $this->assertEquals($user->id, $uid);
        $this->assertEquals($user->username, "user1");
        $this->assertEquals($user->name, "User 1");
        $this->assertEquals($user->email, "user1@mail.com");
        $this->assertEquals($user->username, "user1");
        $this->assertEquals($user, User\check_auth($user->username, "password"));
        $this->assertEquals($user, User\check_auth_id($uid, "password"));
        return $uid;
    }

    /**
     * @depends testCreate
     */
    public function testModify($uid)
    {
        $user = User\get($uid);
        $this->assertTrue(
            User\modify(
                $uid,
                "user2",
                "User 2",
                "user2@mail.com"
            )
        );
        $user = User\get($uid);
        $this->assertEquals($user->username, "user2");
        $this->assertEquals($user->name, "User 2");
        $this->assertEquals($user->email, "user2@mail.com");
        $this->assertEquals($user->username, "user2");
        $this->assertTrue($user == User\check_auth($user->username, "password"));
        $this->assertTrue($user == User\check_auth_id($uid, "password"));
        return $uid;
    }

    /**
     * @depends testModify
     */
    public function testChangePassword($uid)
    {
        $this->assertTrue(
            User\change_password(
                $uid,
                "new password"
            )
        );
        $user = User\get($uid);
        $this->assertTrue($user == User\check_auth($user->username, "new password"));
        $this->assertTrue($user == User\check_auth_id($uid, "new password"));
        return $uid;
    }
    
    /**
     * @depends testChangePassword
     */
    public function testListAll($uid)
    {
        $existing_user = User\get($uid);
        $new_uid = User\create(
            "user1",
            "User 1",
            "password",
            "user1@mail.com",
            ""
        );
        $new_user = User\get($new_uid);
        $l = User\list_all();
        $this->assertEquals(2, count($l));
        $this->assertTrue(in_array($new_user, $l));
        $this->assertTrue(in_array($existing_user, $l));
        return [$existing_user, $new_user];
    }

    /**
     * @depends testListAll
     */
    public function testSearch($users)
    {
        foreach($users as $u) {
            $r = User\search($u->name);

            $this->assertEquals(1, count($r));
            $this->assertEquals($r[0], $u);
        }
        
        return $users;
    }

    /**
     * @depends testSearch
     */
    public function testGetByUsername($users)
    {
        foreach($users as $u) {
            $this->assertEquals(User\get_by_username($u->username), $u);
        }

        $this->assertNull(User\get_by_username("nothing called like it"));
        return $users;
    }

    /**
     * @depends testGetByUsername
     */
    public function testFollow($users)
    {
        $this->assertTrue(User\follow($users[0]->id, $users[1]->id));

        $l = User\get_followers($users[1]->id);
        $this->assertEquals(1, count($l));
        $this->assertEquals($users[0], $l[0]);

        $l = User\get_followings($users[0]->id);
        $this->assertEquals(1, count($l));
        $this->assertEquals($users[1], $l[0]);

        $this->assertTrue(User\unfollow($users[0]->id, $users[1]->id));
        $this->assertEmpty(User\get_followings($users[0]->id));
        $this->assertEmpty(User\get_followers($users[1]->id));
        return $users;
    }

    /**
     * @depends testFollow
     */
    public function testDestroy($users) 
    {
        $this->assertTrue(User\destroy($users[0]->id));
        $l = User\list_all();
        $this->assertEquals(1, count($l));
        $this->assertEquals($l[0], $users[1]);
    }

    public static function tearDownAfterClass()
    {
        \Db::flush();
    }
}
?>
