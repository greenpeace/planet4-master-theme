<?php
$pages = [
    [
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2018/10/0ba734e8-gp0strb69.jpg',
        'image-alt' => 'Protest Against Violence in the Field in Brazil. © Adriano Machado',
        'content' => 'Greenpeace activists protest in front of the Brazilian Congress, demanding an official
                            response and the end of impunity on murders and violence in the field. 251 crosses were
                            taken to the Congress, standing for the 251 murders in the Amazon from 2007 to 2016.
                            Valdelir João de Souza, the &quot;Polish&quot;, is the owner of Cedroarana and G.A. sawmills
                            and the responsible for the forest management plan next to where the Colniza Massacre
                            happened. He&#039;s currently a fugitive from justice, but his sawmills keep shipping timber
                            internally and for other countries. In the same day of the massacre, timber was sent to
                            Europe and to the United States.
                            The Blood-stained Timber report, launched by Greenpeace, shows how fraud in licensing
                            (authorizing logging from protected areas) and production chain monitoring systems
                            (identifying the companies that buy and sell timber from the forest to end users) further
                            increase violence in the field.
                            © Adriano Machado'
    ],
    [
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2018/10/b07ef764-gp0stsk0b.jpg',
        'image-alt' => 'Incêndio Floresta na Amazônia 2018. © Daniel Beltrá',
        'content' => 'Ao final da temporada de fogo na Amazônia, o Greenpeace esteve em campo para registrar o
                        estrago deixado pelas queimadas, na região entre os estados do Amazonas, Acre e Rondônia
                        Mesmo com sua reconhecida importância para conservação da sociobiodiversidade e do clima no
                        mundo todo, a Amazônia ainda possui focos ativos de incêndio e áreas de cinzas. O fogo
                        oferece risco às pessoas e aos animais e contribui para engordar as emissões de gases do
                        efeito estufa. Em 2018, apesar da tendência geral de queda no número de focos de calor na
                        Amazônia Legal, estados críticos em desmatamento registraram mais fogo.
                        © Daniel Beltrá'
    ],
    [
        'image' => 'https://storage.googleapis.com/planet4-international-stateless-develop/2018/10/17793478-gp0stsk0d.jpg',
        'image-alt' => 'Incêndio Floresta na Amazônia 2018. © Daniel Beltrá',
        'content' => 'Ao final da temporada de fogo na Amazônia, o Greenpeace esteve em campo para registrar o
                        estrago deixado pelas queimadas, na região entre os estados do Amazonas, Acre e Rondônia
                        Mesmo com sua reconhecida importância para conservação da sociobiodiversidade e do clima no
                        mundo todo, a Amazônia ainda possui focos ativos de incêndio e áreas de cinzas. O fogo
                        oferece risco às pessoas e aos animais e contribui para engordar as emissões de gases do
                        efeito estufa. Em 2018, apesar da tendência geral de queda no número de focos de calor na
                        Amazônia Legal, estados críticos em desmatamento registraram mais fogo.
                        © Daniel Beltrá'
    ]
];
$sizes = [
    '' => '1200w',
    '-300x200' => '300w',
    '-768x512' => '768w',
    '-1024x683' => '1024w',
    '-510x340' => '510w'
];
foreach ($pages as $i => $page) {
    $srcSet = [];
    foreach ($sizes as $suffix => $size) {
        $src = str_replace('.jpg', $suffix . '.jpg', $page['image']);
        $srcSet[] = $src . ' ' . $size;
    }
    $pages[$i]['srcset'] = implode(', ', $srcSet);
}
$id = 'carousel_e5827774';
?>
<div class="carousel-wrap">
    <div id="<?php echo $id; ?>" class="carousel slide" data-ride="carousel">
        <h1>Carousel Block</h1>
        <ol class="carousel-indicators">
            <?php foreach ($pages as $i => $page) : ?>
            <li data-target="#<?php echo $id; ?>" data-slide-to="<?php echo $i; ?>" <?php if ($i === 0) echo 'class="active"'; ?>></li>
            <?php endforeach; ?>
        </ol>
        <div class="carousel-inner" role="listbox">
            <div class="carousel-item-container">
                <a class="carousel-control-prev" href="#<?php echo $id; ?>" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true">
                        <i></i>
                    </span>
                    <span class="sr-only">Previous</span>
                </a>
                <?php foreach ($pages as $i => $page) : ?>
                    <div class="carousel-item <?php if ($i === 0) echo 'active'; ?>">
                        <img src="<?php echo $page['image']; ?>"
                             srcset="<?php echo $page['srcset']; ?>"
                             sizes=""
                             alt="<?php echo $page['image-alt']; ?>">
                        <div class="carousel-caption">
                            <p>
                                <?php echo $page['content']; ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <a class="carousel-control-next" href="#<?php echo $id; ?>" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true">
                        <i></i>
                    </span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
        </div>
    </div>
</div>
