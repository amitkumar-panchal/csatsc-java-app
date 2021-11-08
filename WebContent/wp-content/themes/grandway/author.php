<?php
    get_header();

    global $PhoenixData;

    $PHT_gen_crumbs = isset($PhoenixData['breadcrumbs']) ? $PhoenixData['breadcrumbs'] : null;
    $PHT_sidebar = isset($PhoenixData['blog_sidebar_position']) ? $PhoenixData['blog_sidebar_position'] : 'right';
    $PHT_layout = isset($PhoenixData['blog_layout']) ? $PhoenixData['blog_layout'] : 'classic';
?>

      <div class="page-in">
              <div class="container">
                <div class="row">

                  <div class="col-lg-6 pull-left">
                    <div class="page-in-name">
<?php
                        _e('Author', 'grandway');
                        echo ': <span>';
                        echo get_the_author();
                        echo '</span>';
?>
                    </div>
                  </div>
<?php
                if ($PHT_gen_crumbs) :
                    PhoenixTeam_Utils::breadcrumbs();
                else :
                    echo "<!-- Breadcrumbs turned off -->\n";
                endif;
?>
                </div>
              </div>
            </div>

            <div <?php post_class(array('container', 'general-font-area', 'marg50')); ?>>
                <div class="row">

<?php
                    if ($PHT_sidebar == 'no') {
                        echo '<div class="col-lg-12">'."\n";
                    } elseif ($PHT_sidebar == 'right') {
                        echo '<div class="col-lg-9">'."\n";
                    } elseif ($PHT_sidebar == 'left') {
                        ?>
                        <!-- sidebar -->
                        <div class="col-lg-3">
                            <?php dynamic_sidebar('blog-sidebar');
                        ?>
                        </div><!-- sidebar end-->

                        <div class="col-lg-9">
<?php

                    }

                    if (have_posts()) {
                        the_post();

                        if (get_the_author_meta('description')) : ?>
                            <div class="author-bio">
                              <div class="img-author"><?php echo get_avatar(get_the_author_meta('ID'), 80);
                                      ?></div>
                              <div class="name-author"><?php _e('About author:', 'grandway');
                                      ?> <?php the_author_posts_link();
                                      ?></div>
                              <div class="text-author">
                                <?php echo wpautop(get_the_author_meta('description'));
                                      ?>
                              </div>
                            </div>

                            <div class="cl-blog-line"></div>
                      <?php endif;

                      rewind_posts();

                        while (have_posts()) {
                            the_post();

                            $PHT_post_format = get_post_format();
                            if (!$PHT_post_format) {
                                echo '<div class="row '.esc_html($PHT_layout).'-blog post-standard">';
                                get_template_part('format', 'standard');
                            } else {
                                echo '<div class="row '.esc_html($PHT_layout).'-blog post-'.$PHT_post_format.'">';
                                get_template_part('format', get_post_format());
                            }
                            echo '</div>';
                        }

                        echo '<div class="row"><div class="col-lg-12">';
                        PhoenixTeam_Utils::pagination('pride_pg');
                        echo '</div></div>';
                    } else {
                        ?>
                        <div class="container general-font-area marg50">
                            <h1 style="display: block; text-align: center;">
                              <?php _e('Sorry, nothing to display.', 'grandway'); ?>
                            </h1>
                        </div>
<?php

                    }
?>
                </div>

                    <?php if ($PHT_sidebar == 'right') : ?>
                        <!-- sidebar -->
                        <div class="col-lg-3">
                            <?php dynamic_sidebar('blog-sidebar'); ?>
                        </div><!-- sidebar end-->
                    <?php endif; ?>

                </div>

            </div><!-- container marg50 -->

<?php get_footer(); ?>
