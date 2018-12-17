<?php
$steps = [
    [
        'step' => 1,
        'name' => 'one',
        'title' => 'Send your selfie',
        'content' => 'Upload a selfie with the tag #break_free_2018 in front of your favorite local spot that
                                is free of fossil fuels. Add a caption to explain why you think this spot should remain
                                free of fossil fuels',
        'label' => 'Follow on Instagram',
        'link' => 'https://www.instagram.com/break_free_2018/'
    ],
    [
        'step' => 2,
        'name' => 'two',
        'title' => 'Check out what others upload',
        'content' => 'See what people around the world are submitting as their favorite spot that is free from fossil fuels',
        'label' => 'Follow on Facebook',
        'link' => 'https://www.facebook.com/BreakFree2018/',
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2013/12/f33c56ab-gp0sto60w_medium_res-493x340.jpg'
    ]
];
?>
<div id="p4bks_tasks_container" class="what-you-can-do-wrapper">
    <div class="container">
        <h3>Take Action Task Block</h3>
        <div class="row">
            <div class="col-md-12">
                Here's how you can join the global movement
            </div>
        </div>

        <div class="clearfix"></div>

        <?php // desktop version ?>
        <div class="can-do-steps d-none d-lg-block">
            <div class="row">
                <?php foreach ($steps as $step) : ?>
                    <div class="col" data-id="<?php echo $step['step']; ?>">
                    <span class="step-number" id="step-<?php echo $step['step']; ?>">
                        <span class="step-number-inner"><?php echo $step['step']; ?></span>
                    </span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="step-info">
                <div class="row">
                    <?php foreach ($steps as $step) : ?>
                        <div class="col" data-id="<?php echo $step['step']; ?>">
                            <h5><?php echo $step['title']; ?></h5>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="row">
                    <?php foreach ($steps as $step) : ?>
                        <div class="col" data-id="<?php echo $step['step']; ?>">
                            <p><?php echo $step['content']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="steps-action">
                    <div class="row">
                        <?php foreach ($steps as $step) : ?>
                            <div class="col" data-id="<?php echo $step['step']; ?>">
                                <?php if (!empty($step['image'])) : ?>
                                    <img src="<?php echo $step['image']; ?>" alt="">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="row">
                        <?php foreach ($steps as $step) : ?>
                            <div class="col" data-id="<?php echo $step['step']; ?>">
                                <a class="btn btn-small btn-medium btn-secondary" href="<?php echo $step['link']; ?>">
                                    <?php echo $step['label']; ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php // mobile version ?>
        <div class="can-do-steps-mobile d-lg-none">
            <div id="accordion" role="tablist" aria-multiselectable="true">

                <?php foreach ($steps as $step) : ?>
                    <div class="card">
                        <a class="card-header <?php if ($step['step'] !== 1) echo 'collapsed'; ?>" role="tab"
                           id="heading-<?php echo $step['name']; ?>"
                           data-toggle="collapse" data-target="#collapse-<?php echo $step['name']; ?>"
                           href="#collapse-<?php echo $step['name']; ?>"
                           aria-expanded="true"
                           aria-controls="collapse-<?php echo $step['name']; ?>">
                            <span class="step-number"><?php echo $step['step']; ?></span>
                            <?php echo $step['title']; ?>
                        </a>

                        <div id="collapse-<?php echo $step['name']; ?>"
                             class="collapse <?php if ($step['step'] === 1) echo 'show'; ?>"
                             data-parent="#accordion" role="tabpanel"
                             aria-labelledby="heading-<?php echo $step['name']; ?>">
                            <div class="card-block info-with-image-wrap clearfix">
                                <div class="mobile-accordion-info">
                                    <p><?php echo $step['content']; ?></p>
                                </div>

                                <div class="accordion-image-wrap">
                                    <?php if (!empty($step['image'])) : ?>
                                        <img src="<?php echo $step['image']; ?>" alt="">
                                    <?php endif; ?>
                                </div>

                                <a class="btn btn-small btn-secondary" href="<?php echo $step['link']; ?>">
                                    <?php echo $step['label']; ?>
                                </a>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
