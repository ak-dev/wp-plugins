
</div>
</article>

<div class="tl" data-id="<?php echo $post->ID; ?>" data-post="<?php echo $post->post_name; ?>"> 
    <h2 class="title">
        <?php echo $post->post_title; ?>
        <span><?php if (isset($timeline['post']['subtitle'])) echo $timeline['post']['subtitle']; ?></span>
    </h2>

    <?php if (isset($post->post_content) && !empty($post->post_content)) : ?>
    <p class="desc"><?php echo $post->post_content; ?></p>
    <?php endif; ?> 

    <div class="navigation">
        <ul>
            <?php $adinterval = 0; $ad=0; ?>

            <?php for ($i=0; $i<count($timeline['rows']); $i++) : ?>
            <?php $tl_row = $timeline['rows'][$i]; ?>

            <li <?php if ($tl_row['tlseparator'] == 1) echo 'class="break"'; ?>>
                <a href="#tlitem-<?php echo $post->ID.'-'.$i; ?>" class="dateDot <?php if ($tl_row['showdate'] === 0) echo 'alt'; ?><?php if ($i === 0) echo 'active'; ?>">
                    <?php if (isset($tl_row['tldotlabel']) && !empty($tl_row['tldotlabel'])) : ?>
                    <label><?php echo $tl_row['tldotlabel']; ?></label>
                    <?php endif; ?>
                </a>
            </li>



            <?php if ($i !== 0 && $adinterval !== 0 && ($i == 2 || $adinterval % 5 === 0)) : ?>
            <?php $ad++; ?>

            <li class="ad">
                <a href="#tlitem-<?php echo $post->ID.'-advert-'.$ad; ?>" class="dateDot"></a>
            </li>

            <?php endif; ?>
            <?php  
             if ($i == 2) {
                $adinterval = $i-1;
             } else {
                $adinterval++;
             }
             ?>


            <?php endfor; // end -- for ($i=0; $i<count($timeline['rows']); $i++) ?> 
        <div class="backLine"></div>  
        </ul>
    </div>

    <div class="buttons">
        <a class="prev hide" href="#"><i class="fa fa-chevron-left"></i></a>
        <a class="next" href="#"><i class="fa fa-chevron-right"></i></a>
    </div>

    <div class="timeline">
        <div class="wrapper">

            <?php $adinterval = 0; $ad=0; ?>

            <?php for ($i=0; $i<count($timeline['rows']); $i++) : ?>
            
            <?php
                $tl_row = $timeline['rows'][$i];
                $feature = ($tl_row['tlfeature_event'] == 1) ? ' feature' : '';
                $cat = (!empty($tl_row['tlcategory'])) ? ' '.$tl_row['tlcategory'] : '';
            ?>

            <section id="#tlitem-<?php echo $post->ID.'-'.$i; ?>" class="<?php if ($i === 0) echo 'active'; ?><?php echo $feature.$cat; ?>" >
                <h2 class="date <?php echo $tl_row['showdateandtime']; ?>">

                    <?php if ($tl_row['tldaytime'] == 'day' && isset($tl_row['tldate']) && !empty($tl_row['tldate'])) : ?>
                    <strong class="day"><?php echo $tl_row['day']; ?></strong>
                    <span class="month"><?php echo $tl_row['month']; ?></span>
                    <span class="year"> <?php echo $tl_row['year']; ?></span>

                    <?php elseif ($tl_row['tldaytime'] == 'time' && isset($tl_row['tltime']) && !empty($tl_row['tltime'])) : ?>
                    <?php if ($tl_row['showdate'] === 1) : ?>
                    <strong class="day"><?php echo $tl_row['tlt_day']; ?></strong>
                    <span class="month"><?php echo $tl_row['tlt_month']; ?></span>
                    <span class="year"> <?php echo $tl_row['tlt_year']; ?></span>
                    <?php endif; ?>
                    <span class="time">
                        <?php echo $tl_row['tltime']; ?>
                        <?php if (isset($tl_row['tlendtime']) && !empty($tl_row['tlendtime'])) : ?>
                         - <?php echo $tl_row['tlendtime']; ?>
                        <?php endif; ?>
                    </span>

                    <?php endif; ?>
                </h2> 

                <div class="scrollArea">
                    <?php if ($tl_row['tlmediachoice'] == 'picture' && isset($tl_row['tlpicture']) && !empty($tl_row['tlpicture'])) : ?>
                    <img class="img full" src="<?php echo $tl_row['tlpicture']; ?>" alt="<?php if (isset($tl_row['tlimgalt']) && !empty($tl_row['tlimgalt'])) echo $tl_row['tlimgalt']; ?>" />
                    <?php if (isset($tl_row['tlcredit']) && !empty($tl_row['tlcredit'])) : ?><p class="credit"><?php echo $tl_row['tlcredit']; ?></p><?php endif; ?>
                    <?php elseif ($tl_row['tlmediachoice'] == 'media' && isset($tl_row['tlmedia']) && !empty($tl_row['tlmedia'])) : ?>
                    <?php echo $tl_row['tlmedia']; ?>
                    <?php endif; ?>
                    
                    <?php if (isset($tl_row['tlcaption']) && !empty($tl_row['tlcaption'])) : ?>
                    <h4><?php echo $tl_row['tlcaption']; ?></h4>
                    <?php endif; ?> 

                    <?php if (isset($tl_row['tltitle']) && !empty($tl_row['tltitle'])) : ?>
                    <h3><?php echo $tl_row['tltitle']; ?></h3>
                    <?php endif; ?>

                    <?php if (isset($tl_row['tldescription']) && !empty($tl_row['tldescription'])) : ?>
                    <p><?php echo $tl_row['tldescription']; ?></p>
                    <?php endif; ?>
                </div>
            </section>

            <?php if ($i !== 0 && $adinterval !== 0 && ($i == 2 || $adinterval % 5 === 0)) : ?>
            <?php $ad++; ?>
            <section id="#tlitem-<?php echo $post->ID.'-advert-'.$ad; ?>" class="advert">
                <div class="ad cubeAd">
                    <div class="adBanner"></div>
                </div>
            </section>
            <?php endif; ?>
            <?php  
             if ($i == 2) {
                $adinterval = $i-1;
             } else {
                $adinterval++;
             }
             ?>

            <?php endfor; // end -- for ($i=0; $i<count($timeline['rows']); $i++) ?> 

        </div>
    </div>
</div>


<article class="container">
    <div class="content">



