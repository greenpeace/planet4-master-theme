<?php
$covers = [
    [
        'link' => 'https://k8s.p4.greenpeace.org/international/tag/climate/',
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2017/11/GP0STOLO1-768x512.jpg',
        'image-alt' => 'Statue of Liberty action at the Arctic Sea ice edge. © Christian Åslund / Greenpeace',
        'tag' => 'Climate'
    ],
    [
        'link' => 'https://k8s.p4.greenpeace.org/international/tag/coal/',
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2018/01/0fdfd366-gp04fyk_pressmedia-768x512.jpg',
        'image-alt' => 'Herder and Sheep in Central Java. © Kemal Jufri / Greenpeace',
        'tag' => 'Coal'
    ],
    [
        'link' => 'https://k8s.p4.greenpeace.org/international/tag/food/',
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2017/11/GP0STP8OJ-768x511.jpg',
        'image-alt' => 'Ecological Farmer in Kenya © Cheryl-Samantha Owen / Greenpeace',
        'tag' => 'Food'
    ]
]
?>
<div class="campaign-thumbnail-block show-3-covers">
    <div class="container">
        <h2 class="page-section-header">Campaign Covers (covers block)</h2>

        <p class="page-section-description">this is a description</p>

        <div class="row thumbnail-largeview-container mb-2 limit-visibility">
            <?php foreach ($covers as $cover) : ?>
            <div class="col-md-4 campaign-card-column">
                <a href="<?php echo $cover['link']; ?>">
                    <div class="thumbnail-large">
                        <img src="<?php echo $cover['image']; ?>"
                             alt="<?php echo $cover['image-alt']; ?>">
                        <span class="yellow-cta">#<?php echo $cover['tag']; ?></span>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
