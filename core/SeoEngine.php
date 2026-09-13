<?php

/**
 * erocms SEO Engine
 * Saytdagi barcha 27 ta toifa (kategoriya) uchun yuqori samarali SEO ma'lumotlari:
 * - Sarlavhalar (O'zbek va Rus tillarida)
 * - Meta tavsiflar (Meta Description)
 * - Qidiruv kalit so'zlari (Meta Keywords - Google, Yandex va mahalliy qidiruvlar uchun)
 */

function seo_get_categories_data() {
    return [
        'uzbek' => [
            'name' => 'Узбекский секс (O‘zbekcha seks)',
            'description' => 'Самая большая коллекция узбекского секса в хорошем качестве. Eng sara o‘zbekcha seks, uzbek sex, uzb porno, toshkent, samarqand va vodiy qizlarining uyatli videolari. Bepul onlayn tomosha qiling va yuklab oling.',
            'meta' => 'Узбекский секс видео онлайн бесплатно в высоком качестве. O‘zbekcha seks, uzbek sex, uzb porno, toshkent va samarqand qizlari bilan erotik va uyatli videolar sekschi.online saytida.',
            'keywords' => 'узбек секс, узбекский секс, uzbekcha seks, uzbek sex, o\'zbekcha seks, uzb seks, узбек порно, uzbekcha porno, uzb porno, узбечка, uzbechka, тошкент секс, samarqand sex, fargona qizlari, uyatli video, seks video, skachat porno'
        ],
        'minet' => [
            'name' => 'Минет (Oral seks)',
            'description' => 'Горячий минет и глубокий оральный секс в хорошем качестве. Qizlarning ehtiros bilan og‘izga olishi, sochniy minet va erkak asbobini erkalash videolari sekschi.online saytida.',
            'meta' => 'Смотреть минет и оральный секс онлайн бесплатно. O‘zbekcha minet, rotga olish, og‘izga olish va chuqur minet videolari.',
            'keywords' => 'минет, отсос, оральный секс, minet, ogizga olish, o\'zbekcha minet, uzbek minet, rotga olish, глубокий минет, сосет член, член в рот, oral sex, seks minet, skachat minet'
        ],
        'rakom' => [
            'name' => 'Раком (Doggystyle)',
            'description' => 'Страстный секс в позе раком и сочные движения сзади. Qizlarni rakom qilib orqasidan tiqish, doggystyle sekis va shiddatli jinsiy aloqa videolari sekschi.online da.',
            'meta' => 'Секс в позе раком и догги-стайл онлайн бесплатно. Qizlarni rakom qilib urish, orqasidan tiqish va ehtirosli videolar.',
            'keywords' => 'раком, секс раком, поза раком, сзади, doggystyle, rakom, orqadan urish, orqasidan tiqish, rakom sekis, doggi stayl, секс сзади, раком видео, скачать секс раком'
        ],
        'anal' => [
            'name' => 'Анал (Anal seks)',
            'description' => 'Отборное анальное порно и глубокие проникновения в попку. Qizlarning tor orqa teshigiga tiqish, o‘zbekcha anal sekis va ehtirosli anal penetratsiya videolari.',
            'meta' => 'Анальный секс видео онлайн бесплатно в HD. Anal seks, ketiga tiqish, orqasiga olish va uzbek anal videolari sekschi.online da.',
            'keywords' => 'анал, анальный секс, анальное порно, anal, anal sex, orqaga tiqish, ketiga tiqish, uzbek anal, секс в жопу, в попу, узбекский анал, скачать анал'
        ],
        'domashnee' => [
            'name' => 'Домашнее (Uyda olingan)',
            'description' => 'Реальное домашнее порно, скрытая камера и любительский секс. O‘zbekcha domashniy videolar, uyda er-xotinlar tomonidan olingan va yashirin kamera lavhalari.',
            'meta' => 'Домашний и любительский секс онлайн бесплатно. Uyda olingan samopal videolar, domashniy seks va shaxsiy kadrlar sekschi.online da.',
            'keywords' => 'домашнее, домашнее порно, любительский секс, частное порно, domashniy, uyda olingan, yashirin kamera, samopal, uzbek domashnee, domashka, секс дома, скачать домашнее'
        ],
        'molodye' => [
            'name' => 'Молодые (Yoshlar seksi)',
            'description' => 'Секс молодых и юных девушек онлайн. 18+ yosh go‘zallar seksi, yosh talaba qizlarning ilk jinsiy tajribalari va ehtirosli sarguzashtlari sekschi.online saytida.',
            'meta' => 'Секс молодых девушек онлайн бесплатно в HD. Yosh qizlar seksi, 18+ yoshlik g‘o‘rlik, yoshlarning ehtirosli aloqasi.',
            'keywords' => 'молодые, молоденькие, секс молодых, юные девушки, yoshlar seksi, yosh qizlar, yoshlik g\'o\'rlik, 18+ yosh, molodye porno, molodenkie, скачать порно молодых'
        ],
        'siski' => [
            'name' => 'Большие сиськи (Katta ko‘kraklar)',
            'description' => 'Красотки с большими сиськами и пышными формами. Katta ko‘krakli va emchakli qizlar seksi, ko‘krak orasiga tiqish va emish videolari yuqori sifatda.',
            'meta' => 'Порно с большими сиськами онлайн бесплатно. Katta kokrakli qizlar, katta emchaklar, kokrak orasiga olish va emish videolari.',
            'keywords' => 'большие сиськи, грудастые, дойки, big tits, tits, katta kokrak, katta emchak, emchaklar, emish, огромные сиськи, порно с большими сиськами'
        ],
        'big' => [
            'name' => 'Большие члены (Katta asboblar)',
            'description' => 'Секс с большими членами и гигантскими стволами. Katta asbobli va uzun olatli baquvvat yigitlar bilan qizlarning ehtirosli jinsiy aloqasi sekschi.online da.',
            'meta' => 'Порно с большими членами онлайн бесплатно в HD. Katta olatli erkaklar, ulkan asbob bilan qizlarni qondirish videolari.',
            'keywords' => 'большие члены, большой член, огромный хуй, big cock, katta asbob, katta olat, katta quroq, толстый член, гигантский член, секс с большим членом'
        ],
        'studenty' => [
            'name' => 'Студенты (Talabalar)',
            'description' => 'Секс студентов в общежитии и на вписке. Talaba qizlar va yigitlarning yotoqxonalardagi sarguzashtlari, yoshlarning ehtirosli seksi sekschi.online da.',
            'meta' => 'Секс студентов и студенток онлайн бесплатно. Talabalar seksi, talaba qizlar bilan yotoqxona va kvartiradagi videolar.',
            'keywords' => 'студенты, секс студентов, студентки, общага, вписка, talabalar seksi, talaba qizlar, obshaga, yotoqxona seksi, порно студенток'
        ],
        'sperma' => [
            'name' => 'Сперма (Sperma otilishi)',
            'description' => 'Мощные залпы спермы на лицо, в рот и в киску. Erkakning kuchli sperma otishi, qizlarning yuziga va og‘ziga oqizish kadrlarini bepul tomosha qiling.',
            'meta' => 'Выстрелы спермы и концовка видео онлайн бесплатно. Sperma otilishi, yuzga va og‘izga sperma quyish videolari sekschi.online da.',
            'keywords' => 'сперма, выстрелы спермы, сперма на лицо, кончил внутрь, sperma, sperma otilishi, yuziga sperma, cumshot, залпы спермы, сперма во рту'
        ],
        'gruppovoe' => [
            'name' => 'Групповое (Guruhli seks)',
            'description' => 'Групповой секс, оргии и тройнички МЖМ и ЖМЖ онлайн. Bir nechta sheriklar bilan guruhli jinsiy aloqa, ikki erkak bitta qiz yoki aksincha videolar to‘plami.',
            'meta' => 'Групповой секс и секс втроем онлайн бесплатно. Guruhli seks, troynichok, bir nechta erkak va qizlar ishtirokidagi videolar.',
            'keywords' => 'групповое, групповой секс, групповуха, тройничок, мжм, жмж, threesome, orgy, guruhli seks, birgalikda, секс втроем'
        ],
        'russkoe' => [
            'name' => 'Русское (Ruscha porno)',
            'description' => 'Отборное русское порно с красивыми девушками. Rus qizlari bilan ehtirosli videolar, ruscha jinsiy aloqa va uyatli kadrlar sekschi.online da.',
            'meta' => 'Русское порно и русский секс онлайн бесплатно в HD. Ruscha seks, rus qizlari bilan videolar bepul tomosha qiling.',
            'keywords' => 'русское, русское порно, русский секс, отечественное порно, rus porno, ruscha seks, русские шлюхи, русские девушки'
        ],
        'mamki' => [
            'name' => 'Мамки (Milf va Ayollar)',
            'description' => 'Секс с горячими зрелыми мамками и милфами. Yoshi katta tajribali ayollar, kelinchaklar va xolalar bilan ehtirosli jinsiy aloqa videolari sekschi.online da.',
            'meta' => 'Порно с мамками и милфами онлайн бесплатно. Tajribali ayollar, kelinlar va xolalar seksi yuqori sifatda.',
            'keywords' => 'мамки, милф, milf, зрелые женщины, опытные мамочки, yoshi katta ayollar, xolalar seksi, kelinlar seksi, kelinchak, порно мамки'
        ],
        'asian' => [
            'name' => 'Азиатки (Osiyolik qizlar)',
            'description' => 'Милые азиатки, тайки и восточные красавицы в постели. Sharqona go‘zallar va osiyolik qizlar bilan nozik hamda ehtirosli jinsiy aloqa videolari.',
            'meta' => 'Порно с азиатками онлайн бесплатно в HD. Osiyo qizlari, sharq go‘zallari va тайки bilan erotik videolar sekschi.online da.',
            'keywords' => 'азиатки, порно азиатки, тайки, японки, кореянки, osiyo qizlari, asian porno, asian sex, восточные красавицы'
        ],
        'kunnilingus' => [
            'name' => 'Куннилингус (Am yalash)',
            'description' => 'Специально для любителей ласкать киску языком. Qizlarning amini yalash, til bilan rohatlantirish va nozik erkalash videolari sekschi.online da bepul.',
            'meta' => 'Куннилингус и ласки женской киски онлайн бесплатно. Am yalash, til bilan qizlarni rohatlantirish videolari.',
            'keywords' => 'куннилингус, кунни, лизать киску, ласки языком, am yalash, qizlar amini yalash, til bilan, yalash, лижет пизду, cunnilingus'
        ],
        'lishenie_celki' => [
            'name' => 'Лишение целки (Qizlikni olish)',
            'description' => 'Лишение девственности и первый нежный секс онлайн. Qizlik pardasini olish, birinchi bor jinsiy aloqaga kirishish va ilk lazzatlanish videolari.',
            'meta' => 'Лишение девственности онлайн бесплатно в HD. Qizlikni olish, birinchi marta seks qilish va bokiralik videolari sekschi.online da.',
            'keywords' => 'лишение целки, лишение невинности, первый секс, девственницы, целка, qizlikni olish, birinchi marta, bokiralik, qizlik pardasi, первый раз'
        ],
        'beremennye' => [
            'name' => 'Беременные (Homilador ayollar)',
            'description' => 'Секс с беременными женщинами на ранних и поздних сроках. Homilador ayollar bilan nozik va ehtirosli jinsiy aloqa videolari sekschi.online da.',
            'meta' => 'Смотреть порно с беременными онлайн бесплатно. Homilador ayollar seksi, homiladorlik davridagi erotik videolar.',
            'keywords' => 'беременные, секс беременных, порно с беременными, pregnant sex, homilador ayollar, qorni chiqqan, беременная в постели'
        ],
        'pyanye' => [
            'name' => 'Пьяные (Mast qizlar seksi)',
            'description' => 'Секс с пьяными девушками после вечеринок и клубов. Ichib olgan va mast holda jinsiy aloqa qilgan qizlarning uyatli videolari sekschi.online da.',
            'meta' => 'Порно с пьяными девушками онлайн бесплатно. Mast qizlar seksi, ichkilikdan keyingi ehtirosli aloqalar.',
            'keywords' => 'пьяные, секс с пьяными, пьяная шлюха, вписка, mast qizlar, mastlikda sekis, ichib olgan qizlar, пьяное порно'
        ],
        'volosatye' => [
            'name' => 'Волосатые (Tukli amlar)',
            'description' => 'Порно с волосатыми кисками и естественной растительностью. Tukli va tabiiy go‘zallikka ega qizlarning ehtirosli videolari sekschi.online saytida.',
            'meta' => 'Порно с волосатыми кисками онлайн бесплатно. Tukli amlar, tabiiy go‘zallik va sochi olingan-olinmagan qizlar seksi.',
            'keywords' => 'волосатые, волосатые киски, волосатая пизда, hairy pussy, tukli am, sochli amlar, естественная красота, натуральные девушки'
        ],
        'negry' => [
            'name' => 'Негры (Qoratanlilar seksi)',
            'description' => 'Чернокожие парни с огромными членами (BBC) и белые красотки. Qoratanli baquvvat erkaklar bilan qizlarning shiddatli jinsiy aloqasi sekschi.online da.',
            'meta' => 'Порно с неграми и огромными членами онлайн бесплатно. Qoratanlilar seksi, ulkan asbobli bbc yigitlar bilan videolar.',
            'keywords' => 'негры, секс с неграми, bbc, чернокожие, чернокожий член, qoratanlilar, qora tanli yigitlar, негры ебут белых'
        ],
        'blonde' => [
            'name' => 'Блондинки (Sariq sochli qizlar)',
            'description' => 'Сексуальные блондинки с горячим темпераментом онлайн. Sariq sochli go‘zal qizlar bilan ehtirosli va lazzatli jinsiy aloqa videolari sekschi.online da.',
            'meta' => 'Порно с блондинками онлайн бесплатно в HD. Sariq sochli qizlar seksi, chiroyli oqsoch go‘zallar bilan videolar.',
            'keywords' => 'блондинки, секс с блондинками, блондиночки, blonde, sariq sochli qizlar, blonde sex, красивая блондинка'
        ],
        'bryunetki' => [
            'name' => 'Брюнетки (Qora sochli qizlar)',
            'description' => 'Горячие брюнетки и темноволосые восточные бестии. Qoramag‘iz va qora sochli ehtirosli qizlarning to‘shakdagi qiziq videolari sekschi.online da.',
            'meta' => 'Порно с брюнетками онлайн бесплатно в HD. Qora sochli qizlar seksi, qosh-ko‘zlari qora go‘zallar bilan videolar.',
            'keywords' => 'брюнетки, секс с брюнетками, брюнеточки, brunette, qora sochli qizlar, brunette sex, темноволосые девушки'
        ],
        'lesbiyanki' => [
            'name' => 'Лесбиянки (Lesbiyankalar)',
            'description' => 'Чувственный секс двух девушек, ласки, поцелуи и страсть. Qizlarning o‘zaro bir-birini erkalashi, amlarini yalashi va o‘yinchoqlar bilan lazzatlanishi.',
            'meta' => 'Лесбийский секс онлайн бесплатно в высоком качестве. Qizlar qizlar bilan, lesbiyankalar seksi va nozik erkalashlar sekschi.online da.',
            'keywords' => 'лесбиянки, лесбийский секс, лесби, lesbiyankalar, qizlar qizlar bilan, lesbian sex, девушки ласкают друг друга'
        ],
        'bdsm' => [
            'name' => 'БДСМ (BDSM va Bo‘ysundirish)',
            'description' => 'БДСМ порно, подчинение, ролевые игры, плётки и доминирование. Qattiq rolevoy o‘yinlar va xonim-qul munosabatlari aks etgan videolar sekschi.online da.',
            'meta' => 'БДСМ видео и доминирование онлайн бесплатно. BDSM seks, bog‘lash, bo‘ysundirish va qattiq rolevoy o‘yinlar.',
            'keywords' => 'бдсм, bdsm, госпожа, порка, подчинение, boglash, qattiq seks, садо мазо, ролевые игры, domina'
        ],
        'zhestkoe' => [
            'name' => 'Жесткое (Qo‘pol va Shiddatli)',
            'description' => 'Жёсткий секс без прелюдий, интенсивные фрикции и глубокий трах. Qizlarni qattiq va qo‘pol ravishda ehtirosli jinsiy aloqa qilish videolari sekschi.online da.',
            'meta' => 'Жесткий секс онлайн бесплатно в хорошем качестве. Qattiq sekis, shiddatli va tezkor jinsiy aloqa videolari.',
            'keywords' => 'жесткое, жесткий секс, грубый секс, qattiq sekis, shiddatli seks, deepthroat, грубое порно, интенсивный трах'
        ],
        'jestokoe_porno' => [
            'name' => 'Жестокое порно (Qattiq porno)',
            'description' => 'Самое жестокое порно со всего интернета без пощады. Qattiq va shafqatsiz jinsiy aloqa, kutilmagan harakatlar videolari sekschi.online saytida.',
            'meta' => 'Жестокое порно онлайн бесплатно в HD качестве. Qattiq porno, shiddatli jinsiy aloqa va to‘siqlarsiz kadrlar.',
            'keywords' => 'жестокое порно, безжалостный секс, жестокий трах, qattiq porno, qopol seks, hardcore, жесткое порно онлайн'
        ],
        'anime-hentai' => [
            'name' => 'Аниме и Хентай (Anime & Hentai)',
            'description' => 'Красочные хентай мультики и аниме порно без цензуры. Kattalar uchun anime va xentay multfilmlari, senzurarsiz sifatli animatsion videolar.',
            'meta' => 'Хентай и аниме порно онлайн бесплатно без цензуры. Anime hentai multfilmlar, chizilgan qizlar bilan erotik videolar sekschi.online da.',
            'keywords' => 'аниме, хентай, hentai, anime hentai, порно мультики, без цензуры, хентай видео, смотреть хентай онлайн'
        ]
    ];
}

/**
 * Barcha 27 ta toifani bazada avtomatik SEO ma'lumotlari bilan to'ldirish
 */
function seo_update_all_categories($mysqli) {
    $seo_data = seo_get_categories_data();
    $updated = 0;

    foreach ($seo_data as $translit => $data) {
        $name = mysqli_real_escape_string($mysqli, $data['name']);
        $desc = mysqli_real_escape_string($mysqli, $data['description']);
        $meta = mysqli_real_escape_string($mysqli, $data['meta']);
        $keys = mysqli_real_escape_string($mysqli, $data['keywords']);
        $safe_translit = mysqli_real_escape_string($mysqli, $translit);

        // Kategoriya mavjudligini tekshiramiz
        $check = $mysqli->query("SELECT id FROM ero_categories WHERE translit = '$safe_translit' LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $cat = $check->fetch_assoc();
            $sql = "UPDATE ero_categories SET 
                name = '$name', 
                description = '$desc', 
                meta = '$meta', 
                keywords = '$keys' 
                WHERE id = '{$cat['id']}'";
            if ($mysqli->query($sql)) {
                $updated++;
            }
        } else {
            // Agar kategoriya bazada hali yo'q bo'lsa, avtomatik qo'shamiz
            $sql = "INSERT INTO ero_categories (name, description, meta, keywords, translit, view) 
                VALUES ('$name', '$desc', '$meta', '$keys', '$safe_translit', 0)";
            if ($mysqli->query($sql)) {
                $updated++;
            }
        }
    }

    // Sahifalar keshini tozalash
    $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);
    @array_map('unlink', glob($doc_root . '/content/cache/*.html'));

    return $updated;
}
