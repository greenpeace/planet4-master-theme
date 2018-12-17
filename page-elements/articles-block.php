<?php
$articles = [
    [
        'link' => 'https://k8s.p4.greenpeace.org/international/press-release/6828/arctic-30-jailed-in-russia-to-take-case-to-european-court/',
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2013/12/f33c56ab-gp0sto60w_medium_res-493x340.jpg',
        'image-alt' => 'Amnesty Granted for the &#039;Arctic 30&#039; in Saint Petersburg © Dmitry Sharomov / Greenpeace',
        'type' => 'Press Release',
        'type-link' => 'https://k8s.p4.greenpeace.org/international/page_type/press-release/',
        'tags' => [
            'ArcticSunrise' => 'https://k8s.p4.greenpeace.org/international/tag/arctic-sunrise/',
            'EnergyRevolution' => 'https://k8s.p4.greenpeace.org/international/tag/energy-revolution/'
        ],
        'headline' => '‘Arctic 30’ jailed in Russia to take case to European Court',
        'headline-link' => 'https://k8s.p4.greenpeace.org/international/press-release/6828/arctic-30-jailed-in-russia-to-take-case-to-european-court/',
        'author' => 'Greenpeace International',
        'author-link' => 'https://k8s.p4.greenpeace.org/international/author/greenpeace-international/',
        'date' => '03/17/2014',
        'content' => 'Amsterdam, 17 March 2014 - The group of Greenpeace activists and freelance journalists
                                who collectively became known as the ‘Arctic 30’ today applied to the European Court of
                                Human Rights&hellip;'
    ],
    [
        'link' => 'https://k8s.p4.greenpeace.org/international/story/6954/its-about-the-people-not-about-the-products-the-faces-of-pfc-pollution/',
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2017/11/131914_232250-510x340.jpg',
        'image-alt' => '',
        'type' => 'Story',
        'type-link' => 'https://k8s.p4.greenpeace.org/international/page_type/story/',
        'tags' => [
            'Consumption' => 'https://k8s.p4.greenpeace.org/international/tag/consumption/',
            'Health' => 'https://k8s.p4.greenpeace.org/international/tag/health/'
        ],
        'headline' => '“It&#039;s about the people, not about the products” - the faces of PFC pollution',
        'headline-link' => 'https://k8s.p4.greenpeace.org/international/story/6954/its-about-the-people-not-about-the-products-the-faces-of-pfc-pollution/',
        'author' => 'Elske Krikhaar and Jeffrey Dugas',
        'author-link' => '',
        'date' => '11/15/2016',
        'content' => 'The first thing that went through my mind as I entered Jan and Ineke van Genderen’s
                            living room was how close the DuPont/Chemours facility was. I could almost see it&hellip;'
    ],
    [
        'link' => 'https://k8s.p4.greenpeace.org/international/press-release/6878/sustainable-fish-from-major-consumer-brands-linked-to-arctic-destruction/',
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2016/03/03f9ac5e-gp0stpna3-510x340.jpg',
        'image-alt' => 'Activity at North Atlantic Seafood Forum (NASF) in Bergen © Johanna Hanno / Greenpeace',
        'type' => 'Press Release',
        'type-link' => 'https://k8s.p4.greenpeace.org/international/page_type/press-release/',
        'tags' => [
            'Food' => 'https://k8s.p4.greenpeace.org/international/tag/food/',
            'Oceans' => 'https://k8s.p4.greenpeace.org/international/tag/oceans/'
        ],
        'headline' => '“Sustainable” fish from major consumer brands linked to Arctic destruction',
        'headline-link' => 'https://k8s.p4.greenpeace.org/international/press-release/6878/sustainable-fish-from-major-consumer-brands-linked-to-arctic-destruction/',
        'author' => 'Greenpeace International',
        'author-link' => 'https://k8s.p4.greenpeace.org/international/author/greenpeace-international/',
        'date' => '03/02/2016',
        'content' => 'Amsterdam, 2 March 2016 - Fishing fleets that supply major consumer brands are using
                            giant trawlers in an area known as the ‘Arctic Galapagos’, according to a new Greenpeace
                            investigation.'
    ]
];
?>
<section class="article-listing page-section">
    <div class="container">
        <div class="row">
            <header class="col-md-12 article-listing-intro">
                <h3 class="page-section-header">Articles Block</h3>
            </header>
            <p class="page-section-description col-md-12">Lorem ipsum dolor sit amet, consectetuer adipiscing elit.
                Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis
                parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium
                quis, sem. Nulla consequat massa quis enim.</p>

            <div class="article-list-section clearfix col-md-12">
                <?php foreach ($articles as $article) : ?>
                    <article class="article-list-item">
                        <div class="article-list-item-image article-list-item-image-max-width">
                            <a href="<?php echo $article['link']; ?>">
                                <img class="d-flex topicwise-article-image"
                                     src="<?php echo $article['image']; ?>"
                                     alt="<?php echo $article['image-alt']; ?>">
                            </a>
                        </div>

                        <div class="article-list-item-body">
                            <div class="article-list-item-tags top-page-tags">
                                <a class="tag-item tag-item--main page-type" href="<?php echo $article['type-link']; ?>">
                                    <?php echo $article['type']; ?>
                                </a>

                                <div class="tag-wrap tags">
                                    <?php foreach ($article['tags'] as $tag => $link) : ?>
                                        <a class="tag-item tag" href="<?php echo $link; ?>">#<?php echo $tag; ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <header>
                                <h4 class="article-list-item-headline">
                                    <a href="<?php echo $article['headline-link']; ?>"><?php echo $article['headline']; ?></a>
                                </h4>
                                <p class="article-list-item-meta">
                                    <span class="article-list-item-author">
                                        by
                                        <?php if ($article['author-link']) : ?>
                                            <a href="<?php echo $article['author-link']; ?>"><?php echo $article['author']; ?></a>
                                        <?php else : ?>
                                            <?php echo $article['author']; ?>
                                        <?php endif; ?>
                                    </span>
                                    <time class="article-list-item-date" datetime=""><?php echo $article['date']; ?></time>
                                </p>
                            </header>

                            <p class="article-list-item-content">
                                <?php echo $article['content']; ?>
                            </p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</section>
