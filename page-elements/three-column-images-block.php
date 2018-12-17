<?php
$columns = [
    'first' => [
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2018/10/b07ef764-gp0stsk0b-768x512.jpg',
        'alt' => 'Incêndio Floresta na Amazônia 2018. © Daniel Beltrá'
    ],
    'second' => [
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2018/10/17793478-gp0stsk0d-768x512.jpg',
        'alt' => 'Incêndio Floresta na Amazônia 2018. © Daniel Beltrá'
    ],
    'third' => [
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2018/09/e0c3898a-gp035as_web_size-768x515.jpg',
        'alt' => '© Will Rose / Greenpeace'
    ]
];
?>
<div class="split-three-column">
    <div class="container">
        <div class="three-column-box">
            <div class="three-column-info col-md-11 col-lg-9">
                <h2 class="page-section-header">Three Column Images Block</h2>
                <p>Description goes here. There's lots happening with blocks, but design is design.</p>
            </div>
            <div class="three-column-images row">
                <?php foreach ($columns as $index => $column) : ?>
                <div class="col">
                    <div class="<?php echo $index; ?>-column split-image">
                        <img src="<?php echo $column['image']; ?>" alt="<?php echo $column['alt']; ?>">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

