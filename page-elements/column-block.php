<?php
$columns = [
    [
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2018/06/values-150x150.png',
        'link' => 'http://www.greenpeace.org/international/values/',
        'title' => 'Our Values',
        'content' => 'Greenpeace uses non-violent creative action to pave the way towards a greener, more peaceful
                        world, and to confront the systems that threaten our environment.',
        'footer' => 'See what we stand for',
    ],
    [
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2018/06/history-150x150.png',
        'link' => 'http://www.greenpeace.org/international/history/',
        'title' => 'History &amp; Successes',
        'content' => 'In 1971, our founders set sail to an island in the Arctic. Their mission? To stop a nuclear
                        bomb. It was a journey that would spark a movement and make history.',
        'footer' => 'Discover our stories',
    ],
    [
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2018/06/organisation-150x150.png',
        'link' => 'https://www.greenpeace.org/international/annual-report/',
        'title' => 'Annual report',
        'content' => 'Greenpeace International is proud to be part of a global network of independent Greenpeace
                        organisations and we are happy to be able to report back to you on our work in 2017.',
        'footer' => 'Check annual report',
    ],
    [
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2018/06/offices-150x150.png',
        'link' => 'http://www.greenpeace.org/international/worldwide/',
        'title' => 'Contact Us',
        'content' => 'With 26 independent national/regional organisations, we work directly with communities on the
                        frontlines as they protect the environments they call home.',
        'footer' => 'Find a Greenpeace office near you',
    ]
];
?>
<section class="four-column">
    <div class="container">
        <h3>Column Block</h3>
        <div class="row">
            <?php foreach ($columns as $column) : ?>
            <div class="col-md-6 col-lg four-column-wrap">
                <div class="four-column-symbol-container">
                    <div class="four-column-symbol">
                        <a href="<?php echo $column['link']; ?>">
                            <img src="<?php echo $column['image']; ?>" alt="<?php echo $column['title']; ?>">
                        </a>
                    </div>
                </div>
                <div class="four-column-information">
                    <h5>
                        <a href="<?php echo $column['link']; ?>"><?php echo $column['title']; ?></a>
                    </h5>
                    <p><?php echo $column['content']; ?></p>
                    <a href="<?php echo $column['link']; ?>"><?php echo $column['footer']; ?></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
