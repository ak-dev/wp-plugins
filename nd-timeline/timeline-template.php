
<?php get_header(); ?>

<main id="main" role="main">
    <?php if (get_post_meta($post->ID, 'newsday_top_content', true)) { ?>
        <?php echo stripslashes(do_shortcode(get_post_meta($post->ID, 'newsday_top_content', true))); ?>
    <?php } ?>

    <article id="ndTimeline" class="container">

        <?php if (get_post_meta($post->ID, 'newsday_lead_content', true)) : ?>
            <div id="features">
                <div class="caro"><?php echo stripslashes(do_shortcode(get_post_meta($post->ID, 'newsday_lead_content', true))); ?></div>
                <a class="prev gc fa-angle-left" href="#"></a>
                <a class="next gc fa-angle-right" href="#"></a>
            </div>
        <?php endif; ?>


        <header class="entryHead">
            <?php
            if (get_post_meta($id, 'hide_h1', true)) :
                echo '';
            else : ?>
                <h1 itemprop="name"><?php the_title(); ?></h1>
                <?php include_once( get_template_directory() . '/inc/simpleshare.php' ); ?>
            <?php endif; ?>
        </header>

        <?php echo do_shortcode(apply_filters("the_content", $post->post_content)); ?>

        <?php 
        $postdata = $nd_timeline->get_timeline_meta(get_query_var('name'));
        ?>

        <?php foreach ($postdata['timeline'] as $key=>$val) : ?>
        <div>
            <div><?php echo $val['tldate']; ?></div>
            <div><?php echo $val['tltime']; ?></div>
            <div><?php echo $val['day']; ?></div>
            <div><?php echo $val['month']; ?></div>
            <div><?php echo $val['year']; ?></div>
            <div><?php echo $val['tltitle']; ?></div>
            <div><?php echo $val['tlcaption']; ?></div>
            <div><?php echo $val['tldescription']; ?></div>
            <div><?php echo $val['tlcategory']; ?></div>
            <div><?php echo $val['tlmediachoice']; ?></div>
            <div><?php echo $val['tlpicture']; ?></div>
            <div><?php echo $val['tlmedia']; ?></div>
            <div><?php echo $val['tlfeature_event']; ?></div>
        </div>
        <?php endforeach; // end -- foreach ($timeline as $key=>$val) ?> 


        <?php
        $btns = get_post_meta($post->ID, "buttons", true);
        if ($btns != ""): $btns = preg_split("/\r\n/", $btns);
        ?>
            <div class="btns">
            <?php foreach ($btns as $btn): preg_match("/^([^:]+)\s*[:]\s*(.+)$/", $btn, $matches);
                    $label = trim($matches[1]);
                    $value = "." . trim($matches[2]);
                    ?>
                    <a href="#" data-filter="<?php echo $value; ?>"><?php echo $label; ?></a>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>


        <?php if (get_post_meta($post->ID, 'credit_line', true)) : ?>
            <p class="credit"><?php echo stripslashes(do_shortcode(get_post_meta($post->ID, 'credit_line', true))); ?></p>
        <?php endif; ?>


        <?php if (get_post_meta($post->ID, 'newsday_related_html', true)) : ?>
            <?php echo stripslashes(do_shortcode(get_post_meta($post->ID, 'newsday_related_html', true))); ?>
        <?php endif; ?>
        
        <?php include get_template_directory() . '/inc/comments.php'; ?>

    </article><!-- #post-## -->

</main><!-- #main -->

<?php get_footer(); ?>