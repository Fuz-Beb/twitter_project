<?php
namespace View\Partials\Post;

function post($post, $html_classes="") {
?>
                    <div class="post inner-block <?php echo $html_classes; ?>">
                        <div class="post-avatar">
                            <a href="user.php?username=<?php echo htmlspecialchars($post->author->username); ?>">
                                <img class="email-avatar" src="<?php echo htmlspecialchars($post->author->avatar); ?>" height="64" width="64">
                            </a>
                        </div>

                        <div class="post-content">
                            <div class="post-author">
                                <a class="link-author" href="user.php?username=<?php echo htmlspecialchars($post->author->username); ?>">
                                    <?php echo htmlspecialchars($post->author->name); ?> (<?php echo htmlspecialchars($post->author->username); ?>)
                                </a>
                            </div>
                            <a class="link-post" href="post.php?id=<?php echo htmlspecialchars($post->id); ?>">
                                <div class="text"><?php echo htmlspecialchars($post->text); ?></div>
                            </a>
                        </div>

                    </div>
<?php
}
?>
