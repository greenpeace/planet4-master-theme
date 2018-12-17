<?php
$menu = [
    [
        'text' => 'Pudding powder icing candy fruitcake.',
        'hash' => 'ef011cfdc7ad448c3cb5541bc07a8c00',
        'type' => 'h1',
        'link' => true,
        'id' => 'pudding-powder-icing-candy-fruitcake',
        'children' => [
            [
                'text' => 'Bear claw bonbon pie dragée.',
                'hash' => '2bb83355a19c721ae20459087dcd6724',
                'type' => 'h2',
                'link' => true,
                'id' => 'bear-claw-bonbon-pie-dragee',
                'children' => []
            ]
        ]
    ],
    [
        'text' => 'Caramels biscuit jelly-o tart',
        'hash' => 'dad41bf189c9030fc78b5469a7583d6a',
        'type' => 'h1',
        'link' => true,
        'id' => 'caramels-biscuit-jelly-o-tart',
        'children' => [
            [
                'text' => 'cotton candy tootsie roll.',
                'hash' => 'e02b1d9560f38bf022c8ac496496f42d',
                'type' => 'h2',
                'link' => true,
                'id' => 'cotton-candy-tootsie-roll',
                'children' => []
            ]
        ]
    ],
    [
        'text' => 'Gummi bears gummi bears jelly-o bear',
        'hash' => '2b322f36695f661b8047bda264e941f1',
        'type' => 'h1',
        'link' => true,
        'id' => 'gummi-bears-gummi-bears-jelly-o-bear',
        'children' => []
    ]
];

$versions = [
    'Submenu block' => '',
    'Submenu Block Option 2' => 'submenu-short'
];
?>

<?php foreach ($versions as $title => $modifierClass) : ?>
    <div class="submenu-block <?php echo $modifierClass; ?>">

        <h2><?php echo $title; ?></h2>

        <div class="submenu-menu">
            <?php foreach ($menu as $menuItem) : ?>
                <ul class="submenu-item">
                    <li>
                        <a href="#<?php echo $menuItem['id']; ?>"
                           class="submenu-link"><?php echo $menuItem['text']; ?></a>
                        <?php if (!empty($menuItem['children'])) : ?>
                            <ul>
                                <?php foreach ($menuItem['children'] as $child) : ?>
                                    <li>
                                        <a href="#<?php echo $child['id']; ?>"
                                           class="submenu-link"><?php echo $child['text']; ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                </ul>
            <?php endforeach; ?>
        </div>

    </div>
<?php endforeach; ?>

<script type='text/javascript'>
    /* <![CDATA[ */
    var submenu = <?php echo json_encode($menu); ?>;
    /* ]]> */
</script>

<?php
/*
 * JS uses the above definition to search the page for matching elements (by type and text content), and prepend them
 * with anchors, so that the menu links scroll to them when clicked
 */
?>
<h1 class="paragraph">Pudding powder icing candy fruitcake.</h1>
<h2 class="paragraph">Bear claw bonbon pie dragée.</h2>
<p class="paragraph">Jelly sweet roll donut bear claw cake soufflé liquorice cake caramels. Dessert chocolate bar
    biscuit dragée jelly-o. Caramels topping candy canes oat cake tart marzipan chupa chups gummies sesame snaps.
    Lollipop jelly dragée icing cookie biscuit tart marzipan powder. Fruitcake bonbon cupcake. Sugar plum sesame snaps
    muffin jelly-o oat cake. Jujubes sweet roll tootsie roll jelly macaroon pie. Pie gummi bears muffin.</p>
<h1 class="paragraph">Caramels biscuit jelly-o tart</h1>
<h2 class="paragraph">cotton candy tootsie roll.</h2>
<p class="paragraph">Muffin cookie halvah oat cake. Jelly-o bear claw chocolate dessert dessert. Cupcake tart toffee
    danish croissant gingerbread chocolate cake topping apple pie. Tootsie roll croissant marzipan gummi bears sweet
    roll lollipop. Lemon drops cupcake chocolate cake sweet roll cookie candy toffee bonbon apple pie. Icing carrot cake
    dragée. Bonbon lollipop gingerbread apple pie bonbon.</p>
<h1 class="paragraph">Gummi bears gummi bears jelly-o bear</h1>
<p class="paragraph">claw gingerbread halvah. Brownie tootsie roll dragée chocolate croissant oat cake. Marshmallow
    pastry sweet topping wafer. Sweet roll dessert toffee jelly bear claw bear claw danish tart bonbon. Soufflé dessert
    sesame snaps jelly gingerbread gummies cookie lollipop tiramisu. Biscuit sweet jelly-o gingerbread oat cake pudding.
    Jelly-o tart carrot cake candy gummies chocolate ice cream bear claw candy.</p>
