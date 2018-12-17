<?php
$sides = [
    'left' => [
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2018/10/0ba734e8-gp0strb69.jpg',
        'image-alt' => 'Protest Against Violence in the Field in Brazil. © Adriano Machado',
        'title' => 'Split Block',
        'title-link' => '#',
        'title-tag' => false,
        'content' => 'This block allows a split. Gummies jelly-o cupcake dessert
                bear claw carrot cake. Cookie pie marshmallow wafer. Muffin donut gummies chupa chups.',
        'button' => [
            'link' => '#',
            'label' => 'Button'
        ]
    ],
    'right' => [
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2018/10/69cc5b6b-gp0stsk0e.jpg',
        'image-alt' => 'Forest Fires in Brazilian Amazon 2018. © Daniel Beltrá',
        'title' => '#Forests',
        'title-link' => 'https://k8s.p4.greenpeace.org/international/tag/forests/',
        'title-tag' => true,
        'content' => 'Describing a campaign area. Sugar plum danish pudding jujubes
                chocolate cake pie. Chocolate cake chocolate danish macaroon lollipop ice cream caramels
                marshmallow.',
        'tag' => [
            'link' => '#',
            'label' => 'Forests'
        ]
    ]
];
$sizes = [
    '-300x200' => '300w',
    '-768x512' => '768w',
    '-1024x683' => '1024w',
    '-510x340' => '510w',
    '' => '1200w'
];
foreach ($sides as $i => $side) {
    $srcSet = [];
    foreach ($sizes as $suffix => $size) {
        $src = str_replace('.jpg', $suffix . '.jpg', $side['image']);
        $srcSet[] = $src . ' ' . $size;
    }
    $sides[$i]['srcset'] = implode(', ', $srcSet);
}
?>
<div class="split-two-column block-wide">
    <?php foreach ($sides as $side => $data) : ?>
    <div class="split-two-column-item item--<?php echo $side; ?>">
        <div class="split-two-column-item-image" style="background-position:left top;">
            <img src="<?php echo $data['image']; ?>"
                 srcset="<?php echo $data['srcset']; ?>"
                 alt="<?php echo $data['image-alt']; ?>">
        </div>
        <div class="split-two-column-item-content">
            <?php if ($data['title-tag']) : ?>
                <a class="split-two-column-item-tag" href="<?php echo $data['title-link']; ?>"><?php echo $data['title']; ?></a>
            <?php else : ?>
                <h2 class="split-two-column-item-title">
                    <a href="<?php echo $data['title-link']; ?>"><?php echo $data['title']; ?></a>
                </h2>
            <?php endif; ?>
            <p class="split-two-column-item-subtitle"><?php echo $data['content']; ?></p>
            <?php if (!empty($data['button'])) : ?>
                <a class="split-two-column-item-link" href="<?php echo $data['button']['link']; ?>">
                    <?php echo $data['button']['label']; ?>
                </a>
            <?php endif; ?>
            <?php if (!empty($data['tag'])) : ?>
                <a class="btn btn-small btn-primary btn-block split-two-column-item-button" href="<?php echo $data['tag']['link']; ?>">
                    <?php echo $data['tag']['label']; ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

</div>


